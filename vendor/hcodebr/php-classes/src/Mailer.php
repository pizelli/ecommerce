<?php

namespace Hcode;

use Rain\Tpl;

class Mailer
{

    const USERNAME = "";
    const PASSWORD = "";
    const NAMEFROM = "Hcod Store";

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
        $this->mail->Host = 'smtp.seudominio.com.br';
        $this->mail->Port = 587;
        $this->mail->SMTPSecure = 'tls';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = self::USERNAME;
        $this->mail->Password = self::PASSWORD;
        $this->mail->setFrom(self::USERNAME, self::NAMEFROM);
        $this->mail->addAddress($toAddress,$toName);
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