<?php

namespace Hcode;

use Rain\Tpl;

class Mailer
{

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {
        $config = array(
            'tpl_dir'   => $_SERVER['DOCUMENT_ROOT'].'/views/email/',
            'cache_dir' => $_SERVER['DOCUMENT_ROOT'].'/views-cache/',
            'debug'     => false
        );

        Tpl::configure($config);
        $tpl = new Tpl;

        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }
        $html = $tpl->draw($tplName, true);

        $this->mail = new \PHPMailer;
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = 1;
        $this->mail->Debugoutput = 'html';
        $this->mail->Host = MAIL_ADDRESS;
        $this->mail->Port = MAIL_PORT;
        $this->mail->SMTPSecure = MAIL_SECURE;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = MAIL_USER;
        $this->mail->Password = MAIL_PASS;
        $this->mail->setFrom(MAIL_USER, SITE_TITLE);
        $this->mail->addAddress($toAddress, $toName);
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($html);
        $this->mail->AltBody = 'This is a plain-text message body';
    }

    public function send()
    {
        return $this->mail->send();
    }

}

?>