<?php
class Database {
    private $db;
    private $driver;

    public function __construct() {
        $this->driver = getenv('DB_DRIVER') ?: 'sqlite';
        
        try {
            if ($this->driver === 'pgsql') {
                $host = getenv('DB_HOST') ?: 'localhost';
                $port = getenv('DB_PORT') ?: '5432';
                $name = getenv('DB_NAME') ?: 'mailshield';
                $user = getenv('DB_USER') ?: 'user';
                $pass = getenv('DB_PASS') ?: 'secret';
                $dsn = "pgsql:host=$host;port=$port;dbname=$name";
                $this->db = new PDO($dsn, $user, $pass);
            } else {
                $path = getenv('DB_PATH') ?: 'data/mailshield.sqlite';
                $this->db = new PDO("sqlite:$path");
            }
            
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->init();
        } catch (PDOException $e) {
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }

    private function init() {
        // Dialekt-Unterschiede für Primary Keys
        $pk = ($this->driver === 'pgsql') ? "SERIAL PRIMARY KEY" : "INTEGER PRIMARY KEY AUTOINCREMENT";
        
        $this->db->exec("CREATE TABLE IF NOT EXISTS shields (
            id $pk,
            hash TEXT UNIQUE,
            encrypted_email TEXT,
            slug TEXT UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS metadata (
            key TEXT PRIMARY KEY,
            value TEXT
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS rate_limits (
            ip_hash TEXT PRIMARY KEY,
            request_count INTEGER,
            last_request INTEGER
        )");

        // Initialisierung des Erstellungsdatums
        if ($this->driver === 'pgsql') {
            $stmt = $this->db->prepare("INSERT INTO metadata (key, value) VALUES ('db_created_at', ?) ON CONFLICT (key) DO NOTHING");
        } else {
            $stmt = $this->db->prepare("INSERT OR IGNORE INTO metadata (key, value) VALUES ('db_created_at', ?)");
        }
        $stmt->execute([date('Y-m-d H:i:s')]);
    }

    public function getDB() { return $this->db; }
    public function getDriver() { return $this->driver; }
}