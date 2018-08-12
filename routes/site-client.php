<?php

use \Hcode\Page;
use \Hcode\Input;
use \Hcode\Model\User;

$app->get("/login", function(){
    $page = new Page;
    $page->setTpl("login", [
        'error' => User::getError(),
        'errorRegister' => User::getErrorRegister(),
        'registerValues' => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
    ]);
});

$app->post("/login", function(){
    $login = filter_input_array(INPUT_POST);
    try{
        User::login($login['login'], $login['password']);
    }catch (Exception $e) {
        User::setError($e->getMessage());
    }
    goURL('/profile');
});

// Logout Cliente

$app->get("/logout", function(){
    User::logout();
    header("Location: /login");
    exit;
});

// Cadastro de Cliente

$app->post("/register", function(){
    $post = Input::postAll();
    $_SESSION['registerValues'] = $post;

    if($post['name'] == ''){
        User::setErrorRegister("Preencha o seu nome.");
        header("Location: /login");
        exit;
    }

    if($post['email'] == ''){
        User::setErrorRegister("Preencha o seu email.");
        header("Location: /login");
        exit;
    }

    if($post['password'] == ''){
        User::setErrorRegister("Preencha a sua senha.");
        header("Location: /login");
        exit;
    }

    if(User::checkLoginExist($post['email']) === true){
        User::setErrorRegister("Este e-mail já esta registrado!");
        header("Location: /login");
        exit;
    }

    $dados = array(
        'inadmin' => 0, 'deslogin' => $post['email'],
        'desperson' => $post['name'], 'desemail' => $post['email'],
        'despassword' => $post['password'], 'nrphone' => $post['phone']
    );

    $user = new User;
    $user->setData($dados);
    $user->save();
    User::login($post['email'], $post['password']);
    header("Location: /profile");
    exit;
});

// Forgot --- Cliente ---

$app->get('/forgot', function(){
    $page = new Page();
    $page->setTpl("forgot");
});

$app->post('/forgot', function(){
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $user = User::getForgot($email);
    header("Location: /forgot/sent");
    exit;
});

$app->get('/forgot/sent', function(){
    $page = new Page;
    $page->setTpl("forgot-sent");
});

$app->get('/forgot/reset', function(){
    $user = User::validForgotDecrypt(Input::get('code', false));
    $page = new Page;
    $page->setTpl('forgot-reset', array(
        "name" => $user["desperson"],
        "code" => Input::get("code", false)
    ));
});

$app->post('/forgot/reset', function(){
    $forgot = User::validForgotDecrypt(Input::post('code'));
    User::setForgotUsed($forgot['idrecovery']);
    $user = new User;
    $user->get((int)$forgot['iduser']);
    $user->setPassword(Input::post('password'));

    $page = new Page;
    $page->setTpl("forgot-reset-success");
});

$app->get("/profile", function(){
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page;
    $page->setTpl("profile", [
        'user' => $user->getValues(),
        'profileMsg' => User::getSuccess(),
        'profileError' => User::getError()
    ]);
});

$app->post("/profile", function(){
    User::verifyLogin(false);
    $post = Input::postAll();
    
    if($post['desperson'] == ''){
        User::setError("Preencha o seu nome.");
        header("Location: /profile");
        exit;
    }
    if($post['desemail'] === ''){
        User::setError("Preencha o seu e-mail.");
        header("Location: /profile");
        exit;
    }

    $user = User::getFromSession();

    if($post['desemail'] != $user->getdesemail()){
        if(User::checkLoginExist($post['desemail']) === true){
            User::setError("Este endereço de e-mail ja esta cadastrado.");
            goURL("/profile");
        }
    }

    $post['inadmin'] = $user->getinadmin();
    $post['despassword'] = $user->getdespassword();
    $post['deslogin'] = Input::post('desemail');
    $user->setData($post);
    $user->update();
    User::setSuccess("Dados alterados com sucesso!");
    goURL("/profile");
});

$app->get("/profile/change-password", function(){
    User::verifyLogin(false);
    $page = new Page;
    $page->setTpl("profile-change-password",[
        'changePassError' => User::getError(),
        'changePassSuccess' => User::getSuccess()
    ]);
});

$app->post("/profile/change-password", function(){
    User::verifyLogin(false);
    $post = Input::postAll();
    if($post['current_pass'] === ''){
        User::setError("Digite a senha atual.");
        goURL('/profile/change-password');
    }
    if($post['new_pass'] === ''){
        User::setError("Digite a nova senha.");
        goURL('/profile/change-password');
    }
    if($post['new_pass_confirm'] === ''){
        User::setError("Confirme a nova senha.");
        goURL('/profile/change-password');
    }
    if($post['new_pass'] !== $post['new_pass_confirm']){
        User::setError("As senhas não estão iguais.");
        goURL('/profile/change-password');
    }
    if($post['current_pass'] === $post['new_pass']){
        User::setError("A sua nova senha deve ser diferente da atual.");
        goURL('/profile/change-password');
    }
    $user = User::getFromSession();

    if(!password_verify($post['current_pass'], $user->getdespassword())){
        User::setError("A senha está inválida.");
        goURL('/profile/change-password');
    }
    $user->setdespassword(User::getPasswordHash($post['new_pass']));
    $user->update();
    User::setSuccess("Senha alterada com sucesso.");
    goURL("/profile/change-password");
});