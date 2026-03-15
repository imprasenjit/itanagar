<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ReactApp extends Controller
{
    public function serve()
    {
        // FCPATH = itanagar/public/, so app/index.html = itanagar/public/app/index.html
        $indexPath = realpath(FCPATH . 'app/index.html');

        if ($indexPath === false || ! file_exists($indexPath)) {
            return service('response')
                ->setStatusCode(404)
                ->setContentType('text/html')
                ->setBody('<h2>React app not built.</h2><p>Run <code>npm run build</code> inside <code>react-app/</code>.</p>');
        }

        return service('response')
            ->setContentType('text/html')
            ->setBody(file_get_contents($indexPath));
    }
}
