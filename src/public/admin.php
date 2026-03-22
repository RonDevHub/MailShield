<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$admin_user = getenv('ADMIN_USER') ?: 'admin';
$admin_pass = getenv('ADMIN_PASS') ?: 'admin';
$db_path = getenv('DB_PATH') ?: 'data/mailshield.sqlite';

if (isset($_POST['login'])) {
    if ($_POST['user'] === $admin_user && $_POST['pass'] === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = $lang['incorrect_login'];
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /");
    exit;
}

if (isset($_SESSION['admin_logged_in']) && isset($_POST['reset_db'])) {
    require_once '../core/Database.php';
    $db = (new Database($db_path))->getDB();
    $db->exec("DELETE FROM shields; DELETE FROM rate_limits;");
    $success = $lang['database_cleared'];
}
?>
<!DOCTYPE html>
<html lang="de" x-data="{ darkMode: localStorage.getItem('theme') === 'dark', showConfirm: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <title><?= $lang['admin_title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center p-6 transition-colors">

    <div class="max-w-md w-full">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold mb-6 text-center"><?= $lang['admin_login'] ?></h1>
                <form method="POST" class="space-y-4">
                    <input type="text" name="user" placeholder="Username" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="password" name="pass" placeholder="Passwort" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <button name="login" class="w-full bg-blue-600 py-4 rounded-xl font-bold hover:bg-blue-700 transition-all text-white">Anmelden</button>
                    <?php if (isset($error)) echo "<p class='text-red-500 text-center text-sm'>$error</p>"; ?>
                </form>
                <a href="/" class="block text-center mt-6 text-sm opacity-50"><?= $lang['back'] ?></a>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 text-center">
                <h1 class="text-2xl font-bold mb-8">Dashboard</h1>
                <?php if (isset($success)) echo "<p class='mb-6 text-green-500 font-bold'>$success</p>"; ?>
                <div class="space-y-4">
                    <button @click="showConfirm = true" class="w-full bg-red-600/10 text-red-500 border border-red-600/20 py-4 rounded-xl font-bold hover:bg-red-600 hover:text-white transition-all">Datenbank leeren 🔥</button>
                    <a href="?logout" class="block w-full bg-gray-200 dark:bg-gray-700 py-4 rounded-xl font-bold"><?= $lang['admin_logout'] ?></a>
                    <a href="/" class="block text-sm opacity-50 mt-4"><?= $lang['back_main'] ?></a>
                </div>
            </div>

            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div @click.away="showConfirm = false" class="bg-white dark:bg-gray-800 p-8 rounded-3xl max-w-sm w-full shadow-2xl border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4"><?= ['sure'] ?></h2>
                    <p class="opacity-60 mb-8 text-sm text-center"><?= $lang['data_deleted'] ?></p>
                    <div class="flex gap-4">
                        <button @click="showConfirm = false" class="flex-1 py-3 rounded-xl bg-gray-200 dark:bg-gray-700 font-bold"><?= $lang['cancel'] ?></button>
                        <form method="POST" class="flex-1">
                            <button name="reset_db" class="w-full py-3 rounded-xl bg-red-600 text-white font-bold"><?= $lang['yes_delete'] ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>