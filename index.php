<?php
session_start();

require_once("vendor/autoload.php");
require_once("config.php");

use \Slim\Slim;

$app = new \Slim\Slim();

$app->config('debug', true);

require_once "routes" . DS . "site.php";
require_once "routes" . DS . "site-categories.php";
require_once "routes" . DS . "site-products.php";
require_once "routes" . DS . "site-client.php";
require_once "routes" . DS . "site-cart.php";
require_once "routes" . DS . "site-checkout.php";
require_once "routes" . DS . "site-order.php";
require_once "routes" . DS . "admin.php";
require_once "routes" . DS . "admin-users.php";
require_once "routes" . DS . "admin-categories.php";
require_once "routes" . DS . "admin-products.php";

$app->run();
?>