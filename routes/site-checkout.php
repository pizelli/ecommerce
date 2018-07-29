<?php

use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Address;
use \Hcode\Model\Cart;


$app->get("/checkout", function(){
    User::verifyLogin(false);
    $address = new Address;
    $cart = Cart::getFromSession();
    if(isset($_GET['zipcode'])){
        $address->loadFromCEP($_GET['zipcode']);
        $cart->setdeszipcode($_GET['zipcode']);
        $cart->save();
        $cart->getCalculateTotal();
    }else{
        $address->setdesaddress('');
        $address->setdescomplement('');
        $address->setdesdistrict('');
        $address->setdescity('');
        $address->setdescountry('');
        $address->setdesstate('');
        $address->setdeszipcode('');
    }
    $page = new Page;
    $page->setTpl("checkout", [
        'cart' => $cart->getValues(),
        'address' => $address->getValues(),
        'products' => $cart->getProducts(),
        'error' => Address::getMsgError()
    ]);
});

$app->post("/checkout", function(){
    User::verifyLogin(false);
    if(!isset($_POST['zipcode']) || $_POST['zipcode']===''){
        Address::setMsgError("Informe o CEP.");
        goURL("/checkout");
    }
    if(!isset($_POST['desaddress']) || $_POST['desaddress']===''){
        Address::setMsgError("Informe o Endereço.");
        goURL("/checkout");
    }
    if(!isset($_POST['desdistrict']) || $_POST['desdistrict']===''){
        Address::setMsgError("Informe o Bairro.");
        goURL("/checkout");
    }
    if(!isset($_POST['descity']) || $_POST['descity']===''){
        Address::setMsgError("Informe a Cidade.");
        goURL("/checkout");
    }
    if(!isset($_POST['desstate']) || $_POST['desstate']===''){
        Address::setMsgError("Informe o Estado.");
        goURL("/checkout");
    }
    $user = User::getFromSession();
    $address = new Address;
    $_POST['deszipcode'] = $_POST['zipcode'];
    $_POST['idperson'] = $user->getidperson();
    $address->setData($_POST);
    $address->save();
    goURL("/order");
});
