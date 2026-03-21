<?php
namespace App;

use PDO;

class Database {
    private static $instance = null;

    public static function getInstance($config) {
        if (self::$instance === null) {
            $type = getenv('DB_TYPE') ?: $config['db_type'];
            
            if ($type === 'sqlite') {
                $path = getenv('DB_PATH') ?: $config['db_configs']['sqlite'];
                // Verzeichnis erstellen falls nicht existent
                if (!file_exists(dirname($path))) { mkdir(dirname($path), 0755, true); }
                $dsn = "sqlite:$path";
                self::$instance = new PDO($dsn);
            } else {
                $m = $config['db_configs']['mysql'];
                $host = getenv('DB_HOST') ?: $m['host'];
                $name = getenv('DB_NAME') ?: $m['name'];
                $user = getenv('DB_USER') ?: $m['user'];
                $pass = getenv('DB_PASS') ?: $m['pass'];
                $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
                self::$instance = new PDO($dsn, $user, $pass);
            }
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
            self::initSchema($type);
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