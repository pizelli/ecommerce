<?php

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;

class Product extends Model{

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

    public static function checkList($list)
    {
        foreach ($list as &$row)
        {
            $p = new Product;
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;
    }

    public function save()
    {
        $sql = new Sql;
        $res = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"    =>$this->getidproduct(),
            ":desproduct"   =>$this->getdesproduct(),
            ":vlprice"      =>$this->getvlprice(),
            ":vlwidth"      =>$this->getvlwidth(),
            ":vlheight"     =>$this->getvlheight(),
            ":vllength"     =>$this->getvllength(),
            ":vlweight"     =>$this->getvlweight(),
            ":desurl"       =>$this->getdesurl()
        ));
        $this->setData($res[0]);
    }

    public function get($id)
    {
        $sql = new Sql;
        $res = $sql->select("SELECT * FROM tb_products WHERE idproduct = :id", array(
            ":id" => $id
        ));
        $this->setData($res[0]);
    }

    public function delete()
    {
        $sql = new Sql;
        $sql->query("DELETE FROM tb_products WHERE idproduct = :id", array(
            ":id" => $this->getidproduct()
        ));
    }

    public function checkFoto()
    {
        if(file_exists(PATH_IMGS.$this->getidproduct().".jpg"))
        {
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        }
        else
        {
            $url = "/res/site/img/" . "product.jpg";
        }
        $this->setdesphoto($url);
    }

    public function getValues()
    {
        $this->checkFoto();
        $values = parent::getValues();
        return $values;
    }

    public function setPhoto($file)
    {
        $ext = explode('.', $file['name']);
        $ext = end($ext);
        switch ($ext)
        {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
            break;
            case 'gif':
                $image = imagecreatefromgif($file['tmp_name']);
            break;
            case 'png':
                $image = imagecreatefrompng($file['tmp_name']);
            break;
        }
        $dist = PATH_IMGS . $this->getidproduct() . ".jpg";
        imagejpeg($image, $dist);
        imagedestroy($image);
        $this->checkFoto();
    }

    public function getFromURL($desurl)
    {
        $sql = new Sql;
        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1;", [
            ':desurl' => $desurl
        ]);
        $this->setData($rows[0]);
    }

    public function getCategories()
    {
        $sql = new Sql;
        return $sql->select("SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct;", [
            ":idproduct"=>$this->getidproduct()
        ]);
    }

    public static function getPage($search, $page = 1, $itemsPerPage = 10)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql;
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_products
            WHERE desproduct LIKE :search 
            ORDER BY desproduct 
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
