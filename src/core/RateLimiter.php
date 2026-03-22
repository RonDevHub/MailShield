<?php
class RateLimiter {
    private $db;
    private $limit = 10;   // Max Requests
    private $period = 3600; // Zeitraum in Sekunden (1 Stunde)
    private $is_dev;

    public function __construct($db) {
        $this->db = $db;
        // Wir prüfen, ob die Umgebung auf 'dev' steht. 
        // Standardmäßig gehen wir von 'production' aus, falls nichts gesetzt ist.
        $this->is_dev = (getenv('APP_ENV') === 'dev');
    }

    public function check($ip_hash) {
        // Wenn wir im Dev-Modus sind, ist alles erlaubt. 
        // Der Rest des Codes wird einfach übersprungen.
        if ($this->is_dev) {
            return true;
        }

        $stmt = $this->db->prepare("SELECT request_count, last_request FROM rate_limits WHERE ip_hash = ?");
        $stmt->execute([$ip_hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $now = time();

        if ($row) {
            // Zeitfenster abgelaufen? Dann Zähler zurücksetzen
            if (($now - $row['last_request']) > $this->period) {
                $this->reset($ip_hash);
                return true;
            }
            // Limit erreicht?
            if ($row['request_count'] >= $this->limit) {
                return false;
            }
            $this->increment($ip_hash);
        } else {
            $this->init($ip_hash);
        }
        return true;
    }

    private function init($ip_hash) {
        $stmt = $this->db->prepare("INSERT INTO rate_limits (ip_hash, request_count, last_request) VALUES (?, 1, ?)");
        $stmt->execute([$ip_hash, time()]);
    }

    private function increment($ip_hash) {
        $stmt = $this->db->prepare("UPDATE rate_limits SET request_count = request_count + 1, last_request = ? WHERE ip_hash = ?");
        $stmt->execute([time(), $ip_hash]);
    }

    private function reset($ip_hash) {
        $stmt = $this->db->prepare("UPDATE rate_limits SET request_count = 1, last_request = ? WHERE ip_hash = ?");
        $stmt->execute([time(), $ip_hash]);
    }
}