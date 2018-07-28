<?php

define("DS", DIRECTORY_SEPARATOR);
define("PATH_ROOT", __DIR__);
define("PATH_IMGS", $_SERVER["DOCUMENT_ROOT"] . DS . "res" . DS . "site" . DS . "img" . DS . "products" . DS);

use \Hcode\Model\User;

function formatPrice(float $vlprice)
{
    return number_format($vlprice, 2, ",", ".");
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




