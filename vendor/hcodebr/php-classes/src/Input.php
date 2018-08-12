<?php

namespace Hcode;

/**
 * Classe destinada para limpar GET e POST
 * 
 * @author Giovani Pizelli
 */

class Input
{
    
    private static function clear(string $field):string
    {
        return strip_tags($field);
    }
    
    public static function get(string $field, bool $strip = true): string
    {
        $field = filter_input(INPUT_GET, $field);
        return ($strip) ? self::clear($field) : $field;
    }

    public static function getAll(bool $strip = true): array
    {
        $fields = filter_input_array(INPUT_GET);
        return ($strip) ? array_map("self::clear", $fields) : $fields;
    }
    
    public static function post(string $field, bool $strip = true): string
    {
        $field = filter_input(INPUT_POST, $field);
        return ($strip) ? self::clear($field) : $field;
    }

    public static function postAll(bool $strip = true): array
    {
        $fields = filter_input_array(INPUT_POST);
        return ($strip) ? array_map("self::clear", $fields) : $fields;
    }
    
}
