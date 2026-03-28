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

        $db = \Config\Database::connect();

        // Discover all migration files on disk
        $migrationPath = APPPATH . 'Database/Migrations/';
        $files         = glob($migrationPath . '*.php') ?: [];

        $migrationFiles = [];
        foreach ($files as $file) {
            $migrationFiles[] = basename($file, '.php');
        }

        // Fetch already-applied versions from the migrations table
        $ran = [];
        if ($db->tableExists('migrations')) {
            $rows = $db->table('migrations')->select('version')->get()->getResultArray();
            foreach ($rows as $row) {
                $ran[] = $row['version'];
            }
        }

        // Build per-migration status list
        $migrationList = [];
        foreach ($migrationFiles as $filename) {
            // CI4 filename format: 2026-03-28-000001_CreatePermissionsTables
            preg_match('/^(\d{4}-\d{2}-\d{2}-\d{6})/', $filename, $matches);
            $version = $matches[1] ?? $filename;

            $migrationList[] = [
                'filename' => $filename,
                'version'  => $version,
                'status'   => in_array($version, $ran) ? 'Applied' : 'Pending',
            ];
        }

        // Sort ascending by version
        usort($migrationList, fn($a, $b) => strcmp($a['version'], $b['version']));

        $totalCount   = count($migrationList);
        $ranCount     = count(array_filter($migrationList, fn($m) => $m['status'] === 'Applied'));
        $pendingCount = $totalCount - $ranCount;

        $data = [
            'migrationList' => $migrationList,
            'totalCount'    => $totalCount,
            'ranCount'      => $ranCount,
            'pendingCount'  => $pendingCount,
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
