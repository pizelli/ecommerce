<?php

use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Address;
use \Hcode\Model\Cart;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


$app->get("/checkout", function(){
    User::verifyLogin(false);
    $address = new Address;
    $cart = Cart::getFromSession();
    if(isset($_GET['zipcode'])){
        $address->loadFromCEP($_GET['zipcode']);
        $cart->setdeszipcode($_GET['zipcode']);
        $cart->save();
        $cart->getCalculateTotal();
    }

    if(!$address->getdesaddress()) $address->setdesaddress('');
    if(!$address->getdesnumber()) $address->setdesnumber('');
    if(!$address->getdescomplement()) $address->setdescomplement('');
    if(!$address->getdesdistrict()) $address->setdesdistrict('');
    if(!$address->getdescity()) $address->setdescity('');
    if(!$address->getdescountry()) $address->setdescountry('');
    if(!$address->getdesstate()) $address->setdesstate('');
    if(!$address->getdeszipcode()) $address->setdeszipcode('');

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
    if(!isset($_POST['desnumber']) || $_POST['desnumber']===''){
        Address::setMsgError("Informe o número.");
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

    $cart = Cart::getFromSession();
    $cart->getCalculateTotal();

    $order = new Order;
    $order->setData([
        'idcart' => $cart->getidcart(),
        'idaddress' => $address->getidaddress(),
        'iduser' => $user->getiduser(),
        'idstatus' => OrderStatus::EM_ABERTO,
        'vltotal' => ($cart->getvltotal())
    ]);
    $order->save();
    goURL("/order/".$order->getidorder());
});

$app->get('/profile/orders', function(){
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page;
    $page->setTpl('profile-orders', [
        'orders' => $user->getOrders()
    ]);
});

$app->get('/profile/orders/:idorder', function($idorder){
    User::verifyLogin(false);

    $order = new Order;
    $order->get((int)$idorder);

    $cart = new Cart;
    $cart->get((int)$order->getidcart());
    $cart->getCalculateTotal();

    $page = new Page;
    $page->setTpl('profile-orders-detail', [
        'order' => $order->getValues(),
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts()
    ]);
});