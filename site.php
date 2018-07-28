<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;

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

?>