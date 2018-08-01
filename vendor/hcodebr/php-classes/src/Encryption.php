<?php

namespace Hcode;

class Encryption
{
    const SECRET = "123qwe";
    const CIPHER = "aes-128-gcm";

    public static function Encrypt($str, $base = true)
    {
        $ivlen = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($str, self::CIPHER, self::SECRET, $options=0, $iv, $tag);
        $dados = array(
            'iv'=>$iv,
            'tag'=>$tag,
            'text'=>$ciphertext
        );
        return ($base) ? base64_encode(serialize($dados)) : serialize($dados);
    }

    public static function Decrypt($str, $base = true)
    {
        $d = ($base) ? unserialize(base64_decode($str)) : unserialize($str);
        return openssl_decrypt($d['text'], self::CIPHER, self::SECRET, $options=0, $d['iv'], $d['tag']);
    }

}