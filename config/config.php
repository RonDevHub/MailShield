<?php
/**
 * MailShield - Konfiguration
 * Diese Werte werden genutzt, wenn keine Docker ENV-Variablen gesetzt sind.
 */

return [
    'app_name' => 'MailShield',
    'app_key'  => 'base64:......', // Bitte ändern!
    'base_url' => 'http://localhost', // Deine Domain
    
    // Datenbank: 'sqlite' oder 'mysql'
    'db_type' => 'sqlite', 
    'db_configs' => [
        'sqlite' => __DIR__ . '/../data/database.sqlite',
        'mysql'  => [
            'host' => 'localhost',
            'name' => 'mailshield',
            'user' => 'root',
            'pass' => ''
        ]
    ],

    // Captcha: 'internal' oder 'cloudflare'
    'captcha_type' => 'internal',
    'cf_site_key'  => '',
    'cf_secret_key' => '',

    'online_since' => '2026-03-21', // Startdatum für den Zähler
    'languages'    => ['de', 'en'],
    'default_lang' => 'de'
];