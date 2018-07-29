<?php

use \Hcode\Page;

$app->get('/', function() {
    $products = Product::listAll();
    $page = new Page();
    $page->setTpl("index", [
        'products' => Product::checkList($products)
    ]);
});


























$app->get('/dump', function(){
    var_dump($_SESSION);
    exit;
});

$app->get('/destroy', function(){
    session_destroy();
    header("Location: /dump");
    exit;
});

?>