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
            $detail = $e->getMessage()
                . ' | File: ' . $e->getFile() . ':' . $e->getLine();
            session()->setFlashdata('error', 'Migration failed: ' . $detail);
        }
        return redirect()->to(base_url('web/migrations'));
    }

    /**
     * Run a single migration file by version string and return rich JSON error info.
     */
    public function runSingleMigration(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->isAdmin()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $version = $this->request->getPost('version');
        if (empty($version)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No version provided']);
        }

        $db = \Config\Database::connect();

        try {
            // Build the class name from the migration file
            $migrationPath = APPPATH . 'Database/Migrations/';
            $files         = glob($migrationPath . $version . '_*.php') ?: [];

            if (empty($files)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Migration file not found for version: ' . $version,
                ]);
            }

            $filename  = basename($files[0], '.php');           // e.g. 2026-03-29-000006_AddTicketReservation
            $className = preg_replace('/^\d{4}-\d{2}-\d{2}-\d{6}_/', '', $filename); // AddTicketReservation

            require_once $files[0];
            $fqcn = 'App\\Database\\Migrations\\' . $className;

            if (! class_exists($fqcn)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Class ' . $fqcn . ' not found in file',
                ]);
            }

            $forge     = \Config\Database::forge();
            $migration = new $fqcn($forge, $db);
            $migration->up();

            // Record it as applied in the migrations table
            if ($db->tableExists('migrations')) {
                $existing = $db->table('migrations')->where('version', $version)->countAllResults();
                if (! $existing) {
                    $db->table('migrations')->insert([
                        'version' => $version,
                        'class'   => $fqcn,
                        'group'   => 'default',
                        'namespace' => 'App',
                        'time'    => time(),
                        'batch'   => ($db->table('migrations')->selectMax('batch')->get()->getRow()->batch ?? 0) + 1,
                    ]);
                }
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => $className . ' applied successfully.',
            ]);

        } catch (\Throwable $e) {
            $dbError = $db->error();
            return $this->response->setJSON([
                'status'        => 'error',
                'message'       => $e->getMessage(),
                'file'          => $e->getFile() . ':' . $e->getLine(),
                'db_error_code' => $dbError['code']  ?? null,
                'db_error_msg'  => $dbError['message'] ?? null,
                'trace'         => array_slice(
                    array_map(fn($f) => ($f['file'] ?? '') . ':' . ($f['line'] ?? '') . ' ' . ($f['function'] ?? ''), $e->getTrace()),
                    0, 8
                ),
            ]);
        }
    }
}
