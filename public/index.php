<?php
session_start();

// 1. Kern-Klassen laden
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Encryption.php';
require_once __DIR__ . '/../src/Utils.php';
require_once __DIR__ . '/../src/Captcha.php';

// 2. Hybrid-Konfiguration (ENV hat Vorrang vor config.php)
$configFile = __DIR__ . '/../config/config.php';
$localConfig = file_exists($configFile) ? require $configFile : [];

// Wir führen die lokale Config mit den ENV-Defaults zusammen
$config = array_merge([
    'app_name'     => getenv('APP_NAME') ?: 'MailShield',
    'app_key'      => getenv('APP_KEY') ?: 'base64:3fS8kL9zP2mR5vX1nQ0wY7tJ4hB6aC9dE2fG5hJ8kL=',
    'base_url'     => getenv('BASE_URL') ?: 'http://localhost:9996',
    'db_type'      => getenv('DB_TYPE') ?: 'sqlite',
    'captcha_type' => getenv('CAPTCHA_TYPE') ?: 'internal',
    'online_since' => '2026-03-21',
    'languages'    => ['de', 'en'],
    'default_lang' => 'de'
], $localConfig);

// 3. Komponenten initialisieren
$db = \App\Database::getInstance($config);
$enc = new \App\Encryption($config['app_key']);
$captcha = new \App\Captcha($config);

// 4. Sprachen & Übersetzungen
$langCode = $_SESSION['lang'] ?? \App\Utils::getBrowserLang($config['languages']);
$langPath = __DIR__ . "/../config/languages/{$langCode}.json";

if (file_exists($langPath)) {
    $lang = json_decode(file_get_contents($langPath), true);
} else {
    // Notfall-Fallback, falls die Sprachdateien im Mount fehlen
    $lang = [
        'subtitle' => 'E-Mail Protection',
        'input_placeholder' => 'E-Mail address...',
        'btn_protect' => 'Protect Now',
        'step1' => 'Enter Mail', 'step2' => 'Get Link', 'step3' => 'Safe!'
    ];
}

// 5. Routing Logik
$path = $_GET['path'] ?? '';
$action = $_GET['action'] ?? '';

// AKTION: E-Mail schützen
if ($action === 'protect' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($captcha->validate($_POST)) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $hash = hash('sha256', $email);
        
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
// VIEW: E-Mail anzeigen (Slug-Check)
elseif (preg_match('/^v\/([a-zA-Z0-9]+)$/', $path, $matches)) {
    $slug = $matches[1];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $captcha->validate($_POST)) {
        $stmt = $db->prepare("SELECT email_encrypted FROM shielded_mails WHERE slug = ?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch();
        $displayEmail = $res ? $enc->decrypt($res['email_encrypted']) : 'Nicht gefunden';
        $view = 'display'; 
    } else {
        $view = 'captcha_verify';
    }
} 
// DEFAULT: Startseite
else {
    $view = 'home';
}

// 6. Statistiken für den Footer
try {
    $statsStmt = $db->query("SELECT COUNT(*) as total FROM shielded_mails");
    $totalShielded = $statsStmt->fetch()['total'];
} catch (Exception $e) {
    $totalShielded = 0;
}

// 7. Template laden
include __DIR__ . '/../templates/layout.php';