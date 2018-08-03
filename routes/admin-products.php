<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){
    User::verifyLogin();
    $search = (isset($_GET['search'])) ? $_GET['search'] : "";
    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
    $pagination = Product::getPage($search, $page);
    $pages = [];
    for($x=0;$x<$pagination['pages'];$x++){
        array_push($pages, [
            'href' => "/admin/products?".http_build_query([
                'page'=>($x + 1),
                'search'=>$search
            ]),
            'text'=>($x + 1)
        ]);
    }
    $page = new PageAdmin;
    $page->setTpl("products", [
        "products"=>$pagination['data'],
        'search' => $search,
        'pages' => $pages
    ]);
});

$app->get("/admin/products/create", function(){
    User::verifyLogin();
    $page = new PageAdmin;
    $page->setTpl("products-create");
});

$app->post("/admin/products/create", function(){
    User::verifyLogin();
    $prod = new Product;
    $prod->setData($_POST);
    $prod->setdesurl(Product::checkDesUrl($prod->getdesproduct()));
    $prod->save();
    header("Location: /admin/products");
    exit();
});

$app->get("/admin/products/:idproduct", function($idproduct){
    User::verifyLogin();
    $prod = new Product;
    $prod->get((int)$idproduct);
    $page = new PageAdmin;
    $page->setTpl("products-update", [
        'product' => $prod->getValues()
    ]);
});

$app->post("/admin/products/:idproduct", function($idproduct){
    User::verifyLogin();
    $prod = new Product;
    $prod->get((int)$idproduct);
    $prod->setData($_POST);
    $prod->save();
    $prod->setPhoto($_FILES['file']);
    header("Location: /admin/products");
    exit();
});

$app->get("/admin/products/:idproduct/delete", function($idproduct){
    User::verifyLogin();
    $prod = new Product;
    $prod->get((int)$idproduct);
    $prod->delete();
    header("Location: /admin/products");
    exit();
});

?>