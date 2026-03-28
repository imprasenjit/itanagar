<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WebModel;

/**
 * Web — admin games list page and its DataTables AJAX source.
 * All other admin features have been split into dedicated controllers:
 *   GameController     — game CRUD, ranges, tiers, draw dates
 *   FinanceController  — orders, tickets, transactions, wallet, winners, refunds, withdrawals, transfers
 *   ContentController  — FAQ, CMS pages, contact submissions
 *   SettingsController — common settings, reports, dashboard stats, migrations, RBAC
 */
class Web extends BaseController
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

    // ── Game Listing ──────────────────────────────────────────────────────────

    public function index()
    {
        $this->global['pageTitle'] = 'event : Web Listing';
        return $this->loadViews('pages/weblist', $this->global, [], null);
    }

    // ── Game Listing DataTable AJAX source ────────────────────────────────────

    public function weblist_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['data' => []]);
        }

        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 10);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->webModel->weblist_count();
        $filtered = $this->webModel->weblist_count($search);
        $rows     = $this->webModel->weblist_data($search, $length, $start);

        $data = [];
        foreach ($rows as $row) {
            $badge = $row->status === 'Active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">' . esc($row->status) . '</span>';

            $actions =
                '<a class="btn btn-sm btn-primary" href="' . base_url('web/edit/' . $row->id) . '" title="Edit"><i class="bi bi-pencil-fill"></i></a> '
                . '<a class="btn btn-sm btn-info" href="' . base_url('web/rangeEdit/' . $row->id) . '" title="Details"><i class="bi bi-gear-fill"></i> Details</a> '
                . '<a class="btn btn-sm btn-secondary" href="' . base_url('web/descriptionEdit/' . $row->id) . '" title="Description"><i class="bi bi-text-left"></i> Desc</a> '
                . '<a class="btn btn-sm btn-danger deleteWeb" href="#" data-userid="' . $row->id . '" title="Delete"><i class="bi bi-trash3-fill"></i></a>';

            $data[] = [
                'name'       => esc($row->name),
                'status'     => $badge,
                'createdDtm' => date('d-m-Y', strtotime($row->createdDtm)),
                'actions'    => $actions,
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

