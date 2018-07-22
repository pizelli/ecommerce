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
        Category::updateFile();
    }

    public function get($id)
    {
        $sql = new Sql;
        $res = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :id", array(
            ":id" => $id
        ));
        $this->setData($res[0]);
    }

    public static function updateFile()
    {
        $cats = Category::listAll();
        $html = [];
        foreach ($cats as $row) {
            array_push($html, '<li><a href="/categories/'. $row['idcategory'] .'">'. $row['descategory'] .'</a></li>');
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'views'. DIRECTORY_SEPARATOR . 'categories-menu.html', implode(PHP_EOL, $html));
    }

    public function delete()
    {
        $sql = new Sql;
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :id", array(
            ":id" => $this->getidcategory()
        ));
        Category::updateFile();
    }

    public function getProducts($related = true)
    {
        $sql = new Sql;
        if($related === true)
        {
            return $sql->select("
                SELECT * FROM tb_products WHERE idproduct IN(
                    SELECT a.idproduct 
                    FROM tb_products a 
                    INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory
                );
            ", [':idcategory' => $this->getidcategory()]);
        }
        else
        {
            return $sql->select("
            SELECT * FROM tb_products WHERE idproduct NOT IN(
                SELECT a.idproduct 
                FROM tb_products a 
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                WHERE b.idcategory = :idcategory
            );
        ", [':idcategory' => $this->getidcategory()]);
        }
    }

    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
    }

    public function delProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct;", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
    }

}
