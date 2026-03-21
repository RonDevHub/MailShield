<?php
namespace App;

use PDO;
use Exception;

class Database {
    private static $instance = null;

    public static function getInstance($config) {
        if (self::$instance === null) {
            // Typ bestimmen: ENV -> Config -> Default 'sqlite'
            $type = getenv('DB_TYPE') ?: ($config['db_type'] ?? 'sqlite');
            
            try {
                if ($type === 'sqlite') {
                    // Pfad bestimmen: ENV -> Config -> Absoluter Standard-Pfad
                    $path = getenv('DB_PATH') ?: ($config['db_configs']['sqlite'] ?? __DIR__ . '/../data/database.sqlite');
                    
                    $dir = dirname($path);
                    if (!file_exists($dir)) { 
                        mkdir($dir, 0755, true); 
                    }
                    
                    $dsn = "sqlite:$path";
                    self::$instance = new PDO($dsn);
                } else {
                    // MySQL Logik
                    $host = getenv('DB_HOST') ?: ($config['db_configs']['mysql']['host'] ?? 'localhost');
                    $name = getenv('DB_NAME') ?: ($config['db_configs']['mysql']['name'] ?? 'mailshield');
                    $user = getenv('DB_USER') ?: ($config['db_configs']['mysql']['user'] ?? 'root');
                    $pass = getenv('DB_PASS') ?: ($config['db_configs']['mysql']['pass'] ?? '');
                    
                    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
                    self::$instance = new PDO($dsn, $user, $pass);
                }

                // FEHLER BEHOBEN: ATTR_ERRMODE auf ERRMODE_EXCEPTION setzen
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::initSchema($type);

            } catch (Exception $e) {
                // Wenn dein 12GB Laptop hier aufgibt, wollen wir wissen warum
                die("Datenbank-Fehler: " . $e->getMessage());
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