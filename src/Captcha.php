<?php
namespace App;

class Captcha {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Generiert eine einfache Rechenaufgabe für das interne Captcha
     */
    public static function generateMathTask() {
        $a = rand(1, 10);
        $b = rand(1, 10);
        $_SESSION['captcha_result'] = $a + $b;
        return "$a + $b = ?";
    }

    /**
     * Validiert das Captcha basierend auf der Konfiguration
     */
    public function validate($postData) {
        // 1. Honeypot Check (Immer aktiv)
        if (!empty($postData['hp_field'])) {
            return false; // Ein Bot hat das unsichtbare Feld ausgefüllt
        }

        $type = getenv('CAPTCHA_TYPE') ?: $this->config['captcha_type'];

        if ($type === 'cloudflare') {
            return $this->validateCloudflare($postData['cf-turnstile-response'] ?? '');
        }

        // Standard: Internes Mathe-Captcha
        $userResult = intval($postData['captcha_input'] ?? 0);
        $sessionResult = $_SESSION['captcha_result'] ?? null;

        if ($sessionResult !== null && $userResult === $sessionResult) {
            unset($_SESSION['captcha_result']); // Einmalig nutzbar
            return true;
        }

        return false;
    }

    /**
     * Validierung gegen die Cloudflare API (Turnstile)
     */
    private function validateCloudflare($token) {
        if (empty($token)) return false;

        $secret = getenv('CF_SECRET_KEY') ?: $this->config['cf_secret_key'];
        
        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'secret' => $secret,
            'response' => $token
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return ($result['success'] ?? false);
    }
}