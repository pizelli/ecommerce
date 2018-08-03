<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

$app->get('/admin/categories', function(){
    User::verifyLogin();
    $search = (isset($_GET['search'])) ? $_GET['search'] : "";
    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
    $pagination = Category::getPage($search, $page);
    $pages = [];
    for($x=0;$x<$pagination['pages'];$x++){
        array_push($pages, [
            'href' => "/admin/categories?".http_build_query([
                'page'=>($x + 1),
                'search'=>$search
            ]),
            'text'=>($x + 1)
        ]);
    }
    $page = new PageAdmin();
    $page->setTpl("categories", [
        "categories"=>$pagination['data'],
        'search' => $search,
        'pages' => $pages
    ]);
});

$app->get('/admin/categories/create', function(){
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("categories-create");
});

$app->post('/admin/categories/create', function(){
    User::verifyLogin();
    $cat = new Category;
    $cat->setData($_POST);
    $cat->save();
    header("Location: /admin/categories");
    exit;
});

$app->get('/admin/categories/:idcategory/delete', function($idcategory){
    User::verifyLogin();
    $cat = new Category;
    $cat->get((int)$idcategory);
    $cat->delete();
    header("Location: /admin/categories");
    exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){
    User::verifyLogin();
    $cat = new Category;
    $cat->get((int)$idcategory);
    $p = new PageAdmin;
    $p->setTpl("categories-update", array(
        "category"=>$cat->getValues()
    ));
});

$app->post('/admin/categories/:idcategory', function($idcategory){
    User::verifyLogin();
    $cat = new Category;
    $cat->get((int)$idcategory);
    $cat->setData($_POST);
    $cat->save();
    header("Location: /admin/categories");
    exit;
});

$app->get('/admin/categories/:idcategory/products', function($idcategory){
    User::verifyLogin();
    $cat = new Category;
    $cat->get((int)$idcategory);
    $page = new PageAdmin;
    $page->setTpl("categories-products", [
        'category' => $cat->getValues(),
        'productsRelated' => $cat->getProducts(),
        'productsNotRelated' => $cat->getProducts(false)
    ]);
});

$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct){
    User::verifyLogin();
    $cat = new Category;
    $cat->get((int)$idcategory);
    $prod = new Product;
    $prod->get((int)$idproduct);
    $cat->addProduct($prod);
    header("Location: /admin/categories/{$idcategory}/products");
    exit;
});

$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct){
    User::verifyLogin();
    $cat = new Category;
    $cat->get((int)$idcategory);
    $prod = new Product;
    $prod->get((int)$idproduct);
    $cat->delProduct($prod);
    header("Location: /admin/categories/{$idcategory}/products");
    exit;
});

?>