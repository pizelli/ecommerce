<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

    const SESSION = "User";

    function __construct() {
        
    }

    public static function login($login, $password) {
        $sql = new Sql();
        $res = $sql->select("select * from tb_users where deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

        if (count($res) === 0) {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
        
        $data = $res[0];
        
        if(password_verify($password, $data["despassword"])){
            $user = new User();
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
    }

    public static function verifyLogin($inadmin = true)
    {
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]['iduser'] > 0
            ||
            (bool)$_SESSION[User::SESSION]['inadmin'] !== $inadmin
        ){
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout()
    {
        unset($_SESSION[User::SESSION]);
    }

}
