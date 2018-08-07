<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("index");
});

$app->get('/admin/login', function() {
    $page = new PageAdmin([
        'header' => false,
        'footer' => false
    ]);
    $page->setTpl("login", [
        'error' => User::getError()
    ]);
});

$app->post('/admin/login', function() {
    try{
        User::login($_POST['login'], $_POST['password']);
    }
    catch(\Exception $er){
        User::setError($er->getMessage());
    }
    goURL("/admin");
});

$app->get('/admin/logout', function(){
    User::logout();
    header("Location: /admin/login");
    exit;
});

// FORGOT

$app->get('/admin/forgot', function(){
    $page = new PageAdmin([
        'header' => false,
        'footer' => false
    ]);
    $page->setTpl("forgot");
});

$app->post('/admin/forgot',function(){
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $user = User::getForgot($email);
    header("Location: /admin/forgot/sent");
    exit;
});

$app->get('/admin/forgot/sent', function(){
    $page = new PageAdmin([
        'header'=>false,
        'footer'=>false
    ]);
    $page->setTpl("forgot-sent");
});

$app->get('/admin/forgot/reset', function(){
    $user = User::validForgotDescrypt($_GET['code']);
    $page = new PageAdmin([
        'header'=>false, 
        'footer'=>false
    ]);
    $page->setTpl('forgot-reset', array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));
});

$app->post('/admin/forgot/reset', function(){
    $forgot = User::validForgotDescrypt($_POST['code']);
    User::setForgotUsed($forgot['idrecovery']);
    $user = new User;
    $user->get((int)$forgot['iduser']);
    $user->setPassword($_POST['password']);

    $page = new PageAdmin([
        'header'=>false,
        'footer'=>false
    ]);
    $page->setTpl("forgot-reset-success");
});

?>