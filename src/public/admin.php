<?php
session_start();
$admin_user = getenv('ADMIN_USER');
$admin_pass = getenv('ADMIN_PASS');

if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $admin_user || $_SERVER['PHP_AUTH_PW'] !== $admin_pass) {
    header('WWW-Authenticate: Basic realm="MailShield Admin"');
    header('HTTP/1.0 401 Unauthorized');
    die('Zutritt verweigert, Freundchen.');
}

require_once '../core/Database.php';
$db_path = getenv('DB_PATH') ?: 'data/mailshield.sqlite';
$db_instance = new Database($db_path);
$db = $db_instance->getDB();

if (isset($_POST['reset_db'])) {
    $db->exec("DELETE FROM shields");
    $db->exec("DELETE FROM rate_limits");
    $message = "Datenbank wurde rasiert. 🪒";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Admin - MailShield</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 p-10 rounded-3xl shadow-2xl text-center max-w-md w-full">
        <h1 class="text-2xl font-bold mb-6">MailShield Admin 🛡️</h1>
        <?php if (isset($message)) echo "<p class='mb-4 text-green-400'>$message</p>"; ?>
        
        <form method="POST" onsubmit="return confirm('Sicher? Alles weg? Für immer?')">
            <button name="reset_db" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-2xl transition-all">
                Datenbank leeren 🔥
            </button>
        </form>
        <a href="/" class="block mt-8 text-sm opacity-50 hover:opacity-100">Zurück zum Dienst</a>
    </div>
</body>
</html>