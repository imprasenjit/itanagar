<?php

/**
 * Root bootstrap for the /itanagar/ directory request.
 *
 * Apache's DirectoryIndex finds this file when accessing the directory root.
 * We manually set PATH_INFO (matching how the .htaccess rewrite sets it for
 * sub-paths) so that CI4's PATH_INFO uri protocol routes correctly.
 */

// Strip the /itanagar base from REQUEST_URI to get just the route path.
$requestUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$appBase    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // → /itanagar

$path = str_starts_with($requestUri, $appBase)
    ? (substr($requestUri, strlen($appBase)) ?: '/')
    : '/';

// Strip /index.php prefix when accessed as index.php/route
if (str_starts_with($path, '/index.php')) {
    $path = substr($path, strlen('/index.php')) ?: '/';
}

$_SERVER['PATH_INFO'] = $path;

require __DIR__ . '/public/index.php';
