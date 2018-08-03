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

    public function getProductsPage($page = 1, $itensPerPage = 8)
    {
        $start = ($page - 1) * $itensPerPage;
        $sql = new Sql;
        $res = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_products a 
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
            INNER JOIN tb_categories c ON c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory 
            LIMIT {$start}, {$itensPerPage};
        ", [
            ':idcategory'=>$this->getidcategory()
        ]);
        $resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal");
        return [
            'data'=>Product::checkList($res),
            'total'=>(int)$resultTotal[0]['nrtotal'],
            'pages'=>ceil($resultTotal[0]['nrtotal'] / $itensPerPage)
        ];
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

    public static function getPage($search, $page = 1, $itemsPerPage = 10)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql;
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_categories
            WHERE descategory LIKE :search 
            ORDER BY descategory 
            LIMIT $start, $itemsPerPage;
        ",[':search' => "%{$search}%"]);
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        return [
            'data' => $results,
            'total' => (int)$resultTotal[0]['nrtotal'],
            'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }

}
