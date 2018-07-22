<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app->get('/admin/categories', function(){
    User::verifyLogin();
    $categories = Category::listAll();
    $page = new PageAdmin();
    $page->setTpl("categories", [
        "categories" => $categories
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

$app->get('/categories/:idcategory', function($idcategory){
    $cat = new Category;
    $cat->get((int)$idcategory);
    $page = new Page;
    $page->setTpl("category", array(
        'category' => $cat->getValues(),
        'products'=>[]
    ));
});

?>