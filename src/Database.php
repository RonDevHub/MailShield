<?php
namespace App;

use PDO;
use Exception;

class Database {
    private static $instance = null;

    public static function getInstance($config) {
        if (self::$instance === null) {
            $type = getenv('DB_TYPE') ?: ($config['db_type'] ?? 'sqlite');
            
            try {
                if ($type === 'sqlite') {
                    // Wir erzwingen den Pfad im /data Verzeichnis des Containers
                    $dbFile = 'database.sqlite';
                    $dataDir = '/var/www/html/data';
                    $path = $dataDir . '/' . $dbFile;

                    // 1. Prüfen ob Verzeichnis existiert, sonst erstellen
                    if (!is_dir($dataDir)) {
                        mkdir($dataDir, 0775, true);
                    }

                    // 2. WICHTIG: Prüfen ob das Verzeichnis beschreibbar ist
                    if (!is_writable($dataDir)) {
                        throw new Exception("Das Verzeichnis $dataDir ist nicht beschreibbar. Prüfe die Docker-Rechte!");
                    }

                    $dsn = "sqlite:$path";
                    self::$instance = new PDO($dsn);
                    
                } else {
                    $host = getenv('DB_HOST') ?: ($config['db_configs']['mysql']['host'] ?? 'localhost');
                    $name = getenv('DB_NAME') ?: ($config['db_configs']['mysql']['name'] ?? 'mailshield');
                    $user = getenv('DB_USER') ?: ($config['db_configs']['mysql']['user'] ?? 'root');
                    $pass = getenv('DB_PASS') ?: ($config['db_configs']['mysql']['pass'] ?? '');
                    
                    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
                    self::$instance = new PDO($dsn, $user, $pass);
                }

                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::initSchema($type);

            } catch (Exception $e) {
                // Wir geben eine klare Fehlermeldung aus
                die("❌ MailShield Datenbank-Fehler: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    private static function initSchema($type) {
        $ai = ($type === 'sqlite') ? 'AUTOINCREMENT' : 'AUTO_INCREMENT';
        $sql = "CREATE TABLE IF NOT EXISTS shielded_mails (
            id INTEGER PRIMARY KEY $ai,
            slug VARCHAR(10) UNIQUE,
            email_hash VARCHAR(64),
            email_encrypted TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        self::$instance->exec($sql);
    }
}