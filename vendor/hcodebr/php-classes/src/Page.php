<?php

namespace Hcode;

use Rain\Tpl;

class Page
{
    private $tpl;
    private $options = [];
    private $defaults = [
        'data'=>[]
    ];

    public function __construct($opts = array(), $tpl_dir = '/views/')
    {
        $this->options = array_merge($this->defaults, $opts);
        $config = array(
            'tpl_dir'   => $_SERVER['DOCUMENT_ROOT'] . '/lojacurso' . $tpl_dir,
            'cache_dir' => $_SERVER['DOCUMENT_ROOT'] . '/lojacurso/views-cache/',
            'debug'     => true
        );
        Tpl::configure($config);

        $this->tpl = new Tpl;

        $this->setData($this->options['data']);

        $this->tpl->draw("header");
    }

    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct()
    {
        $this->tpl->draw("footer");
    }

    private function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }
}


?>