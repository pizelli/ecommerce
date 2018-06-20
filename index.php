<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	// echo "OK";
	$sql = new Hcode\DB\Sql();

	$result = $sql->select("SELECT * FROM tb_users");

	var_dump($result);
});

$app->run();

 ?>