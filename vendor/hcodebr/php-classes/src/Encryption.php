<?php

namespace Hcode;

class Encryption
{
    const SECRET = "123qwe";
    const CIPHER = "aes-256-cbc";

    public static function Encrypt($str, $base = true)
    {
        $iv = random_byte(openssl_cipher_iv_length(self::CIPHER));
        $ciphertext = openssl_encrypt($str, self::CIPHER, self::SECRET, 0, $iv);
        return ($base) ? base64_encode($dados) : $dados;
    }

    public static function Decrypt($str, $base = true)
    {
        $str = ($base) ? base64_decode($str) : $str;
        $code = mb_substr($str, openssl_cipher_iv_length(self::CIPHER), null, '8bit');
        $iv = mb_substr($str, 0, openssl_cipher_iv_length(self::CIPHER), '8bit');;
        return openssl_decrypt($code, self::CIPHER, self::SECRET, 0, $iv);
    }

}