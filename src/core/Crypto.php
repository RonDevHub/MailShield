<?php
class Crypto {
    private static $method = 'aes-256-cbc';

    public static function encrypt($data, $key) {
        $key = str_replace('base64:', '', $key);
        $key = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$method));
        $encrypted = openssl_encrypt($data, self::$method, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($data, $key) {
        $key = str_replace('base64:', '', $key);
        $key = base64_decode($key);
        $data = base64_decode($data);
        $iv_len = openssl_cipher_iv_length(self::$method);
        $iv = substr($data, 0, $iv_len);
        $encrypted = substr($data, $iv_len);
        return openssl_decrypt($encrypted, self::$method, $key, 0, $iv);
    }

    public static function hash($data) {
        return hash('sha256', $data . getenv('APP_KEY'));
    }
}