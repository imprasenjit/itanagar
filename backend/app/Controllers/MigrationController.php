<?php

namespace App\Controllers;

/**
 * MigrationController — admin migration tracker and runner.
 * All routes begin with "web/".
 */
class MigrationController extends BaseController
{
    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
    }

    public function migrations(): string
    {
        if (! $this->isAdmin()) {
            return $this->loadThis();
        }

        $migrate  = \Config\Services::migrations();
        $db       = \Config\Database::connect();
        $allFiles = $migrate->findMigrations();

        $history    = [];
        $ranByClass = [];
        if ($db->tableExists('migrations')) {
            $rows = $db->table('migrations')->orderBy('batch', 'ASC')->orderBy('time', 'ASC')->get()->getResult();
            foreach ($rows as $row) {
                $history[]  = $row;
                $shortKey   = preg_replace('/^.*\\\\/', '', ltrim($row->class, '\\'));
                $ranByClass[$shortKey] = $row;
            }
        }

        $migrations = [];
        foreach ($allFiles as $file) {
            $shortClass  = ltrim(preg_replace('/^.*\\\\/', '', $file->class ?? ''), '\\');
            $ran         = isset($ranByClass[$shortClass]);
            $historyRow  = $ranByClass[$shortClass] ?? null;
            $description = trim(preg_replace('/([A-Z])/', ' $1', $shortClass));

            $migrations[] = [
                'file'        => $shortClass,
                'description' => $description,
                'ran'         => $ran,
                'batch'       => $historyRow->batch ?? null,
                'run_at'      => $historyRow ? date('Y-m-d H:i:s', $historyRow->time) : null,
            ];
        }

        $ranCount     = count(array_filter($migrations, fn($m) => $m['ran']));
        $pendingCount = count($migrations) - $ranCount;

        $data = [
            'migrations'   => $migrations,
            'history'      => $history,
            'totalCount'   => count($migrations),
            'ranCount'     => $ranCount,
            'pendingCount' => $pendingCount,
        ];
        $this->global['pageTitle'] = 'Itanagarchoice : Migrations';
        return $this->loadViews('pages/migrations', $this->global, $data, null);
    }

    public function runMigrations(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->isAdmin()) {
            return redirect()->to(base_url('web/migrations'));
        }
        try {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            session()->setFlashdata('success', 'All pending migrations have been run successfully.');
        } catch (\Throwable $e) {
            session()->setFlashdata('error', 'Migration failed: ' . $e->getMessage());
        }
        return redirect()->to(base_url('web/migrations'));
    }
}
