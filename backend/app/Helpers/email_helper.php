<?php

if (!function_exists('sendmail')) {
    function sendmail(string $to, string $subject, string $body, ?string $attachment = null): bool|string
    {
        return _online($to, $subject, $body, $attachment);
    }
}

if (!function_exists('_online')) {
    function _online(
        string  $to,
        string  $subject,
        string  $body,
        ?string $attachment = null,
        string  $frmName    = 'Itanagar Choice',
        string  $frmMail    = 'ticketadmin@itanagarchoice.com'
    ): bool|string {
        $email = \Config\Services::email();
        $email->initialize([
            'protocol'  => 'smtp',
            'SMTPHost'  => 'mail.itanagarchoice.com',
            'SMTPUser'  => 'ticketadmin@itanagarchoice.com',
            'SMTPPass'  => 'K]YJ}pW}ZCp4',
            'SMTPPort'  => 587,
            'mailType'  => 'html',
            'charset'   => 'iso-8859-1',
            'wordWrap'  => true,
            'CRLF'      => "\r\n",
            'newline'   => "\r\n",
        ]);

        $email->setFrom($frmMail, $frmName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($body);

        if (!is_null($attachment)) {
            $email->attach($attachment);
        }

        if ($email->send()) {
            return true;
        }
        return $email->printDebugger();
    }
}

if (!function_exists('resetPasswordEmail')) {
    function resetPasswordEmail(array $detail): bool|string
    {
        $body = view('email/resetPassword', ['data' => $detail]);
        return sendmail($detail['email'], 'Reset Password', $body);
    }
}

if (!function_exists('setFlashData')) {
    function setFlashData(string $status, string $message): void
    {
        session()->setFlashdata($status, $message);
    }
}
