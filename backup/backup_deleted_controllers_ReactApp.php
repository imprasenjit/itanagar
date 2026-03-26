<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\LoginModel;

class ReactApp extends Controller
{
    protected $helpers = ['url', 'cias_helper'];

    public function serve()
    {
        // FCPATH = itanagar/public/, so ui/index.html = itanagar/public/ui/index.html
        $indexPath = realpath(FCPATH . 'ui/index.html');

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

    /**
     * GET /reset-password/:code/:email
     * Validates the reset token, then redirects to the React reset-password page.
     */
    public function resetPassword()
    {
        $email      = 'admin@itanagarchoice.com';
        $loginModel = new LoginModel();

      $loginModel->createPasswordUser($email,'Admin') ;

    }
}
