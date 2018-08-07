<?php

namespace Hcode;

class PageAdmin extends Page
{
    public function __construct($opts = array(), $tpl_dir = '/views/admin/')
    {
        $opts['data']['UserOn'] = (isset($_SESSION['User']) && $_SESSION['User']['inadmin'] == 1) ? $_SESSION['User'] : [];
        parent::__construct($opts, $tpl_dir);
    }
}

?>