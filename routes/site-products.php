<?php

use \Hcode\Page;
use \Hcode\Model\Product;

$app->get("/products/:desurl", function($desurl){
    $p = new Product;
    $p->getFromURL($desurl);
    $pg = new Page;
    $pg->setTpl("product-detail", [
        'product'=>$p->getValues(),
        'categories'=>$p->getCategories()
    ]);
});

$app->get("/products", function(){
    $products = Product::listAll();
    $pg = new Page;
    $pg->setTpl("products", [
        'products'=>Product::checkList($products)
    ]);
});

