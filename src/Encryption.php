<?php
namespace App;

class Encryption {
    private $key;

    public function __construct($key) {
        $this->key = substr(hash('sha256', $key, true), 0, 32);
    }

    public function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-gcm'));
        $tag = "";
        $ciphertext = openssl_encrypt($data, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt($data) {
        $data = base64_decode($data);
        $ivLen = openssl_cipher_iv_length('aes-256-gcm');
        $iv = substr($data, 0, $ivLen);
        $tag = substr($data, $ivLen, 16);
        $ciphertext = substr($data, $ivLen + 16);
        return openssl_decrypt($ciphertext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}