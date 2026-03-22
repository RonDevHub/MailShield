<?php
class Database {
    private $db;

    public function __construct($path) {
        $this->db = new PDO("sqlite:$path");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->init();
    }

    private function init() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS shields (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            hash TEXT UNIQUE,
            encrypted_email TEXT,
            slug TEXT UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
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

        // DB Geburtsdatum setzen
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO metadata (key, value) VALUES ('db_created_at', ?)");
        $stmt->execute([date('Y-m-d H:i:s')]);
    }

    public function getDB() { return $this->db; }
}