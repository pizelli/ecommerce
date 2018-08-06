<?php

// Informações do site.
define("SITE_URL", "http://localhost/");
define("SITE_TITLE", "Hortfruit Digital E-commerce");
define("SITE_CPF_CNPJ", "000.000.000-11");
define("SITE_ENDERECO", "Rua Não sei onde, 666 - 11222-333");
define("SITE_CIDADE_UF", "Inferto de Dante - RJ");
define("SITE_CEDENTE", "Giovanni Souza");
define("SITE_FONE", "(21) 2222-3333");
define("SITE_EMAIL", "sac@hortfruitdigital.com.br");

// Dados para o boleto.
define("BANCO_AGENCIA", "1111");
define("BANCO_CONTA", "22222");
define("BANCO_DIGITO", "3");
define("BANCO_CARTEIRA", "175");

// Dados para o envio de e-mail.
define("MAIL_ADDRESS", "smtp.dominio.com.br"); // Endereço smtp.
define("MAIL_USER", "nome@dominio.com.br"); // Login.
define("MAIL_PASS", "123456"); // Senha.
define("MAIL_PORT", 587); // Porta.
define("MAIL_SECURE", "tls"); // Tipo de segurança. TLS/SSL

// Helpers
define("DS", DIRECTORY_SEPARATOR);
define("PATH_ROOT", __DIR__);
define("PATH_IMGS", $_SERVER["DOCUMENT_ROOT"] . DS . "res" . DS . "site" . DS . "img" . DS . "products" . DS);

use \Hcode\Model\User;
use \Hcode\Model\Cart;

function formatPrice($vlprice)
{
    if(!$vlprice > 0) $vlprice = 0;
    return number_format($vlprice, 2, ",", ".");
}

function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

function checkLogin($inadmin = true)
{
    return User::checkLogin($inadmin);
}

function getUserName()
{
    $user = User::getFromSession();
    return $user->getdesperson();
}

function getCartNrQtd()
{
    $cart = Cart::getFromSession();
    $totals = $cart->getProductsTotals();
    return $totals['nrqtd'];
}

function getCartVlSubTotal()
{
    $cart = Cart::getFromSession();
    $totals = $cart->getProductsTotals();
    return formatPrice($totals['vlprice']);
}

function onlyNumbers($str){
    preg_match_all("/\d+/", $str, $arr);
    $res = implode("", $arr[0]);
    return $res;
}

function goURL($url, $stop = true){
    header("Location: {$url}");
    if($stop) exit;
}




