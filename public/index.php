<?php
session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Encryption.php';
require_once __DIR__ . '/../src/Utils.php';

$config = require __DIR__ . '/../config/config.php';
$db = \App\Database::getInstance($config);
$enc = new \App\Encryption(getenv('APP_KEY') ?: $config['app_key']);

// Einfaches Routing
$path = $_GET['path'] ?? '';
$path = rtrim($path, '/');

// Sprachregelung
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = \App\Utils::getBrowserLang($config['languages']);
}
if (isset($_GET['setlang'])) {
    $_SESSION['lang'] = in_array($_GET['setlang'], $config['languages']) ? $_GET['setlang'] : $_SESSION['lang'];
}

// Template basierend auf $path
include __DIR__ . '/../templates/layout.php';