<?php

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

if (!function_exists("sendmail")) {

    function sendmail($to, $subject, $body, $attachment = NULL)
    {

        if (ENVIRONMENT === "development") {
            $status = online($to, $subject, $body, $attachment);
        } else {
            $status = online($to, $subject, $body, $attachment);
        }

        return $status;
    }

    // End of sendmail
} // End of if

if (!function_exists("online")) {

    function online($to, $subject, $body, $attachment = NULL, $frmName = "Itanagar Choice", $frmMail = "ticketadmin@itanagarchoice.com")
    {
        $ci = &get_instance();
        $ci->load->library("email");
        $ci->email->initialize(array(
            "protocol" => "smtp",
            "smtp_host" => "mail.itanagarchoice.com",
            "smtp_user" => "ticketadmin@itanagarchoice.com",
            "smtp_pass" => "K]YJ}pW}ZCp4",
            "smtp_port" => 587,
            "mailtype" => "html",
            "charset" => "iso-8859-1",
            "wordwrap" => TRUE,
            "crlf" => "\r\n",
            "newline" => "\r\n"
        ));
        $ci->email->from($frmMail, $frmName);

        $ci->email->to($to);
        $ci->email->subject($subject);
        $ci->email->message($body);
        if (!is_null($attachment)) {
            $ci->email->attach($attachment);
        }
        if ($ci->email->send()) {
            return TRUE;
        } else {
            return $ci->email->print_debugger();
        }
    }

    //End of online
} //End of if
if(!function_exists('resetPasswordEmail'))
{
    function resetPasswordEmail($detail)
    {
        $ci = &get_instance();
        $data["data"] = $detail;
        $email_body = $ci->load->view("email/resetPassword", $data, TRUE);
        // echo $email_body;
        $status=sendmail($detail["email"], "Reset password" . $razorpayOrderId, $email_body);
        // $CI->email->message($CI->load->view('email/resetPassword', $data, TRUE));
        // $CI->email->to();
        // $status = $CI->email->send();
        
        return $status;
    }
}
if (!function_exists("offline")) {

    function offline($to, $subject, $body, $attachment = NULL, $frmName = "TEST Itanagar Choice", $frmMail = "")
    {
        $ci = &get_instance();
        $ci->load->library("email");
        $ci->email->initialize(array(
            "protocol" => "smtp",
            "smtp_host" => "",
            "smtp_user" => "",
            "smtp_pass" => "",
            "smtp_port" => 465,
            "mailtype" => "html",
            "charset" => "iso-8859-1",
            "wordwrap" => TRUE,
            "crlf" => "\r\n",
            "newline" => "\r\n"
        ));
        $ci->email->from($frmMail, $frmName);
        $ci->email->to($to);
        $ci->email->subject($subject);
        $emailBody = "";
        $emailBody .= $ci->load->view("email/header", '', TRUE);
        $emailBody .= $body;
        $emailBody .= $ci->load->view("email/footer", '', TRUE);

        $ci->email->message($emailBody);
        if (!is_null($attachment)) {
            $ci->email->attach($attachment);
        }
        if ($ci->email->send()) {
            return TRUE;
        } else {
            return $ci->email->print_debugger();
        }
    }

    //End of offline
}//End of if