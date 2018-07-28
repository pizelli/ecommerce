<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

$app->get('/', function() {
    $products = Product::listAll();
    $page = new Page();
    $page->setTpl("index", [
        'products' => Product::checkList($products)
    ]);
});


$app->get('/categories/:idcategory', function($idcategory){
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    $cat = new Category;
    $cat->get((int)$idcategory);
    $pagination = $cat->getProductsPage($page);
    $pages = [];
    for ($i = 1; $i <= $pagination['pages']; $i++)
    {
        array_push($pages, [
            'link'=>"/categories/{$idcategory}?page={$i}",
            'page'=>$i
        ]);
    }
    $page = new Page;
    $page->setTpl("category", array(
        'category' => $cat->getValues(),
        'products' => $pagination['data'],
        'pages' => $pages
    ));
});

$app->get("/products/:desurl", function($desurl){
    $p = new Product;
    $p->getFromURL($desurl);
    $pg = new Page;
    $pg->setTpl("product-detail", [
        'product'=>$p->getValues(),
        'categories'=>$p->getCategories()
    ]);
});

$app->get("/cart", function(){
    $cart = Cart::getFromSession();
    $page = new Page();
    $page->setTpl("cart", [
        'cart'=>$cart->getValues(),
        'products'=>$cart->getProducts(),
        'error' => Cart::getMsgError()
    ]);
});

$app->get("/cart/:idproduct/add", function($idproduct){
    $product = new Product;
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $qtd = (isset($_GET['quantity'])) ? (int)$_GET['quantity'] : 1;
    for($i=0;$i<$qtd;$i++)
    {
        $cart->addProduct($product);
    }
    header("Location: /cart");
    exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct){
    $product = new Product;
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product);
    header("Location: /cart");
    exit;
});

$app->get("/cart/:idproduct/remove", function($idproduct){
    $product = new Product;
    $product->get((int)$idproduct);
    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);
    header("Location: /cart");
    exit;
});

$app->post("/cart/freight", function(){
    $cart = Cart::getFromSession();
    $cart->setFreight($_POST['zipcode']);
    header("Location: /cart");
    exit;
});

$app->get("/checkout", function(){
    User::verifyLogin(false);
    $cart = Cart::getFromSession();
    $address = new Address;
    $page = new Page;
    $page->setTpl("checkout", [
        'cart' => $cart->getValues(),
        'address' => $address->getValues()
    ]);
});

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
    header("Location: /checkout");
    exit;
});

$app->get("/logout", function(){
    User::logout();
    header("Location: /login");
    exit;
});

$app->post("/register", function(){
    $post = filter_input_array(INPUT_POST);
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
    header("Location: /checkout");
    exit;
});



$app->get('/dump', function(){
    var_dump($_SESSION);
    exit;
});

?>