<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SlowRequestLogger
 *
 * After filter — logs any request exceeding SLOW_REQUEST_MS milliseconds
 * to writable/logs/slow_requests.log. Safe to run in production.
 *
 * Threshold is controlled by the SLOW_REQUEST_MS env variable (default 500ms).
 */
class SlowRequestLogger implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // CI4 sets this constant at bootstrap
        if (! defined('CI_START')) {
            return;
        }

        $elapsed   = (microtime(true) - CI_START) * 1000; // ms
        $threshold = (int) (env('SLOW_REQUEST_MS') ?: 500);

        if ($elapsed < $threshold) {
            return;
        }

        $db         = \Config\Database::connect();
        $queryCount = $db->getTotalQueries();
        $uri        = (string) $request->getUri();
        $method     = $request->getMethod();
        $ip         = $request->getIPAddress();
        $memory     = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $line = sprintf(
            "[%s] %s %s | %.1fms | %d queries | %.2fMB peak | IP %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($method),
            $uri,
            $elapsed,
            $queryCount,
            $memory,
            $ip
        );

        $logFile = WRITEPATH . 'logs/slow_requests.log';

        // Rotate if file exceeds 5 MB
        if (is_file($logFile) && filesize($logFile) > 5 * 1024 * 1024) {
            rename($logFile, $logFile . '.' . date('Ymd-His') . '.bak');
        }

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
