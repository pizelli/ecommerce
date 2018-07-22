<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

$app->get('/', function() {
    $products = Product::listAll();
    $page = new Page();
    $page->setTpl("index", [
        'products' => Product::checkList($products)
    ]);
});


$app->get('/categories/:idcategory', function($idcategory){
    $cat = new Category;
    $cat->get((int)$idcategory);
    $page = new Page;
    $page->setTpl("category", array(
        'category' => $cat->getValues(),
        'products'=> Product::checkList($cat->getProducts())
    ));
});

?>