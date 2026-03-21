<?php
namespace App;

class Utils {
    public static function generateSlug($length = 6) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($chars), 0, $length);
    }

    public static function getBrowserLang($available) {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'de', 0, 2);
        return in_array($lang, $available) ? $lang : $available[0];
    }
}