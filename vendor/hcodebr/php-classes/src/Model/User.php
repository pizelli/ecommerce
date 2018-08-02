<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Encryption;

class User extends Model{

    const SESSION = "User";
    const SECRECT = "Hc0d3PHP7_S3Cr3t";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSucess";

    public static function getFromSession()
    {
        $user = new User;
        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
        {
            $user->setData($_SESSION[User::SESSION]);
        }
        return $user;
    }

    public static function checkLogin($inadmin = true)
    {
        if(!isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]['iduser'] > 0)
        {
            // Não esta logado.
            return false;            
        }
        else
        {
            if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true)
            {
                return true;
            }
            elseif ($inadmin === false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public static function login($login, $password) {
        $sql = new Sql();
        $res = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

        if (count($res) === 0) {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
        
        $data = $res[0];
        
        if(password_verify($password, $data["despassword"])){
            $user = new User();
            $data['desperson'] = utf8_encode($data['desperson']);
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
    }

    public static function verifyLogin($inadmin = true)
    {
        if(!User::checkLogin($inadmin)){
            if($inadmin){
                header("Location: /admin/login");
            }else{
                header("Location: /login");
            }
            exit;
        }
    }

    public static function logout()
    {
        unset($_SESSION[User::SESSION]);
    }

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save()
    {
        $sql = new Sql;
        $res = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson" => utf8_decode($this->getdesperson()),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => User::getPasswordHash($this->getdespassword()),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));
        $this->setData($res[0]);
    }

    public function get($iduser)
    {
        $sql = new Sql;
        $res = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser" => $iduser
        ));
        $res[0]['desperson'] = utf8_encode($res[0]['desperson']);
        $this->setData($res[0]);
    }

    public function update()
    {
        $sql = new Sql;
        $res = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser" => $this->getiduser(),
            ":desperson" => utf8_decode($this->getdesperson()),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => $this->getdespassword(),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));
        $this->setData($res[0]);
        $this->updateSession();
    }

    private function updateSession()
    {
        $_SESSION[User::SESSION] = $this->getValues();
    }

    public function delete()
    {
        $sql = new Sql;
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser()
        ));
    }

    public static function getForgot($email)
    {
        $sql = new Sql;
        $res = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;", array(
            ":email" => $email
        ));

        if(count($res) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        }
        else
        {
            $data = $res[0];
            $res2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ':iduser'   => $data['iduser'],
                ':desip'    => $_SERVER['REMOTE_ADDR']
            ));
            if(count($res2) === 0)
            {
                throw new \Exception("Não foi possível recuperar a senha.");
            }
            else
            {
                $dataRecovery = $res2[0];
                
                $code = Encryption::Encrypt($dataRecovery['idrecovery']);
                $pathLevel = ($data['inadmin'] === true) ? 'admin/' : '';
                $link = "http://store.curso.com.br/{$pathLevel}forgot/reset?code={$code}";
                $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Loja Hortfruit", "forgot",
                    array(
                        "name"=>$data['desperson'],
                        "link"=>$link
                    )
                );
                $mailer->send();
                return $data;
            }
        }
    }

    public static function validForgotDecrypt($code)
    {
        // $idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRECT, base64_decode($code), MCRYPT_MODE_ECB);
        $idrecovery = Encryption::Decrypt($code);
        $sql = new Sql;
        $res = $sql->select("
            SELECT * FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser) 
            INNER JOIN tb_persons c USING(idperson)
            WHERE
                a.idrecovery = :idrecovery
                AND
                a.dtrecovery is NULL
                AND
                DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
        ", array(
            ':idrecovery' => $idrecovery
        ));
        if(count($res) === 0)
        {
            throw new \Exception("Não foi possivel recuperar a senha.");
        }
        else {
            return $res[0];
        }
    }

    public static function setForgotUsed($idrecovery){
        $sql = new Sql;
        $res = $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));
    }

    public function setPassword($password)
    {
        $sql = new Sql;
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            ":password" => $password,
            ":iduser" => $this->getiduser()
        ));
    }

    public static function setError($msg)
    {
        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError()
    {
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
        User::clearError();
        return $msg;
    }

    public static function clearError()
    {
        $_SESSION[User::ERROR] = null;
    }

    public static function setSuccess($msg)
    {
        $_SESSION[User::SUCCESS] = $msg;
    }

    public static function getSuccess()
    {
        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
        User::clearSuccess();
        return $msg;
    }

    public static function clearSuccess()
    {
        $_SESSION[User::SUCCESS] = null;
    }
    
    public static function setErrorRegister($msg)
    {
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }
    
    public static function getErrorRegister()
    {
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
        User::clearErrorRegister();
        return $msg;
    }
    
    public static function clearErrorRegister()
    {
        $_SESSION[User::ERROR_REGISTER] = null;
    }

    public static function checkLoginExist($email)
    {
        $sql = new Sql;
        $res = $sql->select("
            SELECT * FROM tb_users a 
            INNER JOIN tb_persons b USING(idperson) 
            WHERE a.deslogin = :email OR b.desemail = :email", [
            ':email' => $email
        ]);
        return (count($res) > 0);
    }

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost' => 12
        ]);
    }

    public function getOrders()
    {
        $sql = new Sql;
        $res = $sql->select("
            SELECT * FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus) 
            INNER JOIN tb_carts c USING(idcart) 
            INNER JOIN tb_users d ON d.iduser = a.iduser 
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson 
            WHERE a.iduser = :iduser;
        ", [':iduser' => $this->getiduser()]);
        return $res;
    }

}
