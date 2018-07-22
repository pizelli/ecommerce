<?php

define("DS", DIRECTORY_SEPARATOR);
define("PATH_ROOT", __DIR__);
define("PATH_IMGS", $_SERVER["DOCUMENT_ROOT"] . DS . "res" . DS . "site" . DS . "img" . DS . "products" . DS);

function formatPrice(float $vlprice)
{
    return number_format($vlprice, 2, ",", ".");
}


