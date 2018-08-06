<?php

namespace Hcode;

class ResizeImage
{
    public static function resize($file, $output, $newWidth = 500, $newHeight = 500)
    {
        $ext = explode('.', $file['name']);
        $ext = end($ext);
        switch ($ext)
        {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($file['tmp_name']);
            break;
            case 'gif':
                $source = imagecreatefromgif($file['tmp_name']);
            break;
            case 'png':
                $source = imagecreatefrompng($file['tmp_name']);
            break;
        }
        $filename = $file['tmp_name'];

        // Get new sizes
        list($width, $height) = getimagesize($filename);
        if($width > $height){
            $percent = $newWidth / $width;
        }else{
            $percent = $newHeight / $height;
        }
        $newWidth = round($width * $percent);
        $newHeight = round($height * $percent);
        
        // Load
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        // $source = imagecreatefromjpeg($filename);
        
        // Resize
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Output
        imagejpeg($thumb, $output);
        imagedestroy($thumb);
    }
}