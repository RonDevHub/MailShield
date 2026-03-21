<?php
session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Encryption.php';
require_once __DIR__ . '/../src/Utils.php';
require_once __DIR__ . '/../src/Captcha.php';

$config = require __DIR__ . '/../config/config.php';
$db = \App\Database::getInstance($config);
$enc = new \App\Encryption(getenv('APP_KEY') ?: $config['app_key']);
$captcha = new \App\Captcha($config);

// Übersetzungen laden
$langCode = $_SESSION['lang'] ?? 'de';
$lang = json_decode(file_get_contents(__DIR__ . "/../config/languages/{$langCode}.json"), true);

// Routing Logik
$path = $_GET['path'] ?? '';
$action = $_GET['action'] ?? '';

// 1. AKTION: E-Mail schützen
if ($action === 'protect' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($captcha->validate($_POST)) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $hash = hash('sha256', $email);
        
        // Prüfen ob Hash existiert
        $stmt = $db->prepare("SELECT slug FROM shielded_mails WHERE email_hash = ?");
        $stmt->execute([$hash]);
        $existing = $stmt->fetch();

        if ($existing) {
            $slug = $existing['slug'];
        } else {
            $slug = \App\Utils::generateSlug();
            $encrypted = $enc->encrypt($email);
            $stmt = $db->prepare("INSERT INTO shielded_mails (slug, email_hash, email_encrypted) VALUES (?, ?, ?)");
            $stmt->execute([$slug, $hash, $encrypted]);
        }
        
        $view = 'result';
    } else {
        $error = "Captcha falsch!";
        $view = 'home';
    }
} 
// 2. VIEW: E-Mail anzeigen (Slug-Abfrage)
elseif (preg_match('/^v\/([a-zA-Z0-9]+)$/', $path, $matches)) {
    $slug = $matches[1];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $captcha->validate($_POST)) {
        $stmt = $db->prepare("SELECT email_encrypted FROM shielded_mails WHERE slug = ?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch();
        $displayEmail = $res ? $enc->decrypt($res['email_encrypted']) : 'Nicht gefunden';
        $view = 'display'; 
    } else {
        $view = 'captcha_verify'; // User muss erst Captcha lösen
    }
} 
// 3. DEFAULT: Startseite
else {
    $view = 'home';
}

// Gesamt-Statistik für den Counter
$statsStmt = $db->query("SELECT COUNT(*) as total FROM shielded_mails");
$totalShielded = $statsStmt->fetch()['total'];

include __DIR__ . '/../templates/layout.php';