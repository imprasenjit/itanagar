<?php

namespace App\Controllers;

use App\Models\WebModel;
use App\Models\UserModel;

/**
 * SettingsController — admin-only settings: common config, reports, dashboard AJAX,
 * migration tracker, and RBAC permission management.
 * All routes begin with "web/".
 */
class SettingsController extends BaseController
{
    protected WebModel  $webModel;
    protected UserModel $userModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->webModel  = new WebModel();
        $this->userModel = new UserModel();
    }

    // ── Common Settings ───────────────────────────────────────────────────────

    public function common()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $data['WebInfo'] = $this->webModel->getcommon();
        $this->global['pageTitle'] = 'event : Edit Common Setting';
        return $this->loadViews('pages/web/common', $this->global, $data, null);
    }

    public function editCommon()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $fields   = ['wallet_min', 'wallet_max', 'refund_min', 'refund_max', 'transfer_min', 'transfer_max', 'withdrawl_min', 'withdrawl_max'];
        $userInfo = [];
        foreach ($fields as $field) {
            $userInfo[$field] = $this->request->getPost($field);
        }

        $result = $this->webModel->editWeb_all('common', $userInfo, 1);
        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Settings updated successfully' : 'Range updation failed');
        return redirect()->to('web/common');
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reports()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $data['games'] = $this->webModel->get_allweb();
        $this->global['pageTitle'] = 'event : Reports';
        return $this->loadViews('pages/web/reports', $this->global, $data, null);
    }

    public function report_download()
    {
        if ($this->isAdmin() === false) {
            return redirect()->to('dashboard');
        }

        $type  = $this->request->getGet('type') ?? 'daily';
        $date  = $this->request->getGet('date') ?? date('Y-m-d');
        $webId = $this->request->getGet('web_id') ? (int) $this->request->getGet('web_id') : null;
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));

        switch ($type) {
            case 'event':
                $rows     = $webId ? $this->webModel->report_event($webId) : [];
                $filename = 'report_event_' . $webId . '_' . date('Ymd') . '.csv';
                break;
            case 'monthly':
                $rows     = $this->webModel->report_monthly($year, $month);
                $filename = 'report_monthly_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.csv';
                break;
            default:
                $rows     = $this->webModel->report_daily($date);
                $filename = 'report_daily_' . $date . '.csv';
                break;
        }

        $totalRevenue = array_sum(array_column((array) $rows, 'total_price'));

        $output  = "Order ID,Date,Event,User Name,User Email,Transaction ID,Amount,Status\n";
        foreach ($rows as $row) {
            $output .= implode(',', [
                '#' . $row->id,
                date('d-m-Y H:i', strtotime($row->createdAt)),
                '"' . str_replace('"', '""', $row->web_name ?? '') . '"',
                '"' . str_replace('"', '""', $row->user_name ?? '') . '"',
                $row->user_email ?? '',
                $row->transaction_id ?? '',
                $row->total_price,
                $row->paid_status,
            ]) . "\n";
        }
        $output .= "\nTotal Revenue,," . $totalRevenue . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody("\xEF\xBB\xBF" . $output);
    }

    // ── Dashboard Stats (AJAX) ────────────────────────────────────────────────

    public function dashboard_stats()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $upcoming     = $this->webModel->upcoming_events(5);
        $upcomingData = [];
        foreach ($upcoming as $ev) {
            $upcomingData[] = [
                'name'        => esc($ev->name),
                'result_date' => $ev->result_date ? date('d M Y', strtotime($ev->result_date)) : '—',
                'jackpot'     => number_format((float)($ev->jackpot ?? 0), 0),
            ];
        }

        return $this->response->setJSON([
            'status'           => 'ok',
            'totalweb'         => $this->userModel->count_record('tbl_webs'),
            'totaluser'        => $this->userModel->count_record('tbl_users'),
            'totalTicketsSold' => $this->webModel->total_tickets_sold(),
            'totalRevenue'     => number_format($this->webModel->total_revenue(), 2),
            'todayRevenue'     => number_format($this->webModel->today_revenue(), 2),
            'todayOrders'      => $this->webModel->today_orders(),
            'upcomingEvents'   => $upcomingData,
        ]);
    }

    public function dashboard_txn_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['data' => []]);
        }

        $draw   = (int) ($this->request->getGet('draw') ?? 1);
        $start  = (int) ($this->request->getGet('start') ?? 0);
        $length = (int) ($this->request->getGet('length') ?? 10);
        $search = $this->request->getGet('search')['value'] ?? '';

        $total    = $this->webModel->txn_count(null, null, null, 'PAID', $search);
        $filtered = $total;
        $rows     = $this->webModel->txn_list(null, null, null, 'PAID', $search, $length, $start);

        $data = [];
        foreach ($rows as $txn) {
            $data[] = [
                'id'     => '<a href="' . base_url('web/tickets') . '">#' . $txn->id . '</a>',
                'user'   => '<div class="fw-semibold">' . esc($txn->user_name ?? '—') . '</div><small class="text-muted">' . esc($txn->user_email ?? '') . '</small>',
                'event'  => esc($txn->web_name ?? '—'),
                'amount' => '₹' . number_format((float)$txn->total_price, 2),
                'date'   => date('d M Y', strtotime($txn->createdAt)),
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

}
