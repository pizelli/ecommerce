<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class Category extends Model{


    function __construct() {
        
    }

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save()
    {
        $sql = new Sql;
        $res = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory" => $this->getidcategory(),
            ":descategory" => $this->getdescategory()
        ));
        $this->setData($res[0]);
    }

    public function get($id)
    {
        $sql = new Sql;
        $res = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :id", array(
            ":id" => $id
        ));
        $this->setData($res[0]);
    }

    public function update()
    {
        $sql = new Sql;
        $res = $sql->query("UPDATE tb_categories SET descategory = :descategory WHERE idcategory = :id", array(
            ":descategory"=>$this->descategory(),
            ":id" => $this->idcategory()
        ));
        $this->setData($res[0]);
    }

    public function delete()
    {
        $sql = new Sql;
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :id", array(
            ":id" => $this->getidcategory()
        ));
    }

}
