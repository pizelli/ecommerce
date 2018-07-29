<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model\User;

class Address extends Model{

    const SESSION_ERROR = "AddressError";

    public static function getCEP($nrcep)
    {
        $nrcep = onlyNumbers($nrcep);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/{$nrcep}/json/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $data;
    }

    public function loadFromCEP($nrcep)
    {
        $data = Address::getCEP($nrcep);
        if(isset($data['logradouro']) && $data['logradouro']){
            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdescountry("Brasil");
            $this->setdesstate($data['uf']);
            $this->setdeszipcode(onlyNumbers($data['cep']));
        }
    }

    public function save()
    {
        $sql = new Sql;

        $res = $sql->select("
            CALL sp_addresses_save(
                :idaddress, :idperson, :desaddress, :descomplement, :descity, 
                :desstate, :descountry, :deszipcode, :desdistrict)",[
                    ':idaddress' => $this->getidaddress(), 
                    ':idperson' => $this->getidperson(), 
                    ':desaddress' => utf8_decode($this->getdesaddress()),
                    ':descomplement' => utf8_decode($this->getdescomplement()),
                    ':descity' => utf8_decode($this->getdescity()), 
                    ':desstate' => $this->getdesstate(), 
                    ':descountry' => $this->getdescountry(), 
                    ':deszipcode' => $this->getdeszipcode(), 
                    ':desdistrict' => utf8_decode($this->getdesdistrict())
        ]);
        if(count($res)>0){
            $this->setData($res[0]);
        }
    }

    public static function setMsgError($msg)
	{
		$_SESSION[Address::SESSION_ERROR] = $msg;
	}

	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";
		Address::clearMsgError();
		return $msg;
	}

	public static function clearMsgError()
	{
		$_SESSION[Address::SESSION_ERROR] = null;
	}

}
