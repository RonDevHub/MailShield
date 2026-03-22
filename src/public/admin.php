<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$admin_user = getenv('ADMIN_USER') ?: 'admin';
$admin_pass = getenv('ADMIN_PASS') ?: 'admin';
$db_path = getenv('DB_PATH') ?: 'data/mailshield.sqlite';

// Login Check
if (isset($_POST['login'])) {
    if ($_POST['user'] === $admin_user && $_POST['pass'] === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Falsche Zugangsdaten!";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /");
    exit;
}

// Datenbank-Aktion
if (isset($_SESSION['admin_logged_in']) && isset($_POST['reset_db'])) {
    require_once '../core/Database.php';
    $db = (new Database($db_path))->getDB();
    $db->exec("DELETE FROM shields");
    $db->exec("DELETE FROM rate_limits");
    $success = "Datenbank wurde geleert! 🔥";
}
?>
<!DOCTYPE html>
<html lang="de" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    showConfirm: false 
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <title>Admin Area - MailShield</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold mb-6 text-center">Admin Login 🛡️</h1>
                <form method="POST" class="space-y-4">
                    <input type="text" name="user" placeholder="Username" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="password" name="pass" placeholder="Passwort" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <button name="login" class="w-full bg-blue-600 py-4 rounded-xl font-bold hover:bg-blue-700 transition-all">Anmelden</button>
                    <?php if (isset($error)) echo "<p class='text-red-500 text-center text-sm'>$error</p>"; ?>
                </form>
                <a href="/" class="block text-center mt-6 text-sm opacity-50">Zurück</a>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 text-center">
                <h1 class="text-2xl font-bold mb-2">Dashboard</h1>
                <p class="text-sm opacity-60 mb-8">Eingeloggt als <?= htmlspecialchars($admin_user) ?></p>
                
                <?php if (isset($success)) echo "<p class='mb-6 text-green-500 font-bold'>$success</p>"; ?>

                <div class="space-y-4">
                    <button @click="showConfirm = true" class="w-full bg-red-600/10 text-red-500 border border-red-600/20 py-4 rounded-xl font-bold hover:bg-red-600 hover:text-white transition-all">
                        Datenbank leeren 🔥
                    </button>
                    <a href="?logout" class="block w-full bg-gray-200 dark:bg-gray-700 py-4 rounded-xl font-bold">Abmelden</a>
                    <a href="/" class="block text-sm opacity-50 mt-4">Zurück zur Hauptseite</a>
                </div>
            </div>

            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div @click.away="showConfirm = false" class="bg-white dark:bg-gray-800 p-8 rounded-3xl max-w-sm w-full shadow-2xl border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4">Bist du sicher?</h2>
                    <p class="opacity-60 mb-8 text-sm text-center">Alle geschützten E-Mails und Statistiken werden unwiderruflich gelöscht.</p>
                    <div class="flex gap-4">
                        <button @click="showConfirm = false" class="flex-1 py-3 rounded-xl bg-gray-200 dark:bg-gray-700 font-bold">Abbrechen</button>
                        <form method="POST" class="flex-1">
                            <button name="reset_db" class="w-full py-3 rounded-xl bg-red-600 text-white font-bold shadow-lg shadow-red-500/30">Ja, löschen!</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>