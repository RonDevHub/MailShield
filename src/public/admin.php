<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../core/Database.php';
$db_instance = new Database();
$db = $db_instance->getDB();
$driver = $db_instance->getDriver();

$admin_user = getenv('ADMIN_USER') ?: 'admin';
$admin_pass = getenv('ADMIN_PASS') ?: 'admin';
$db_path = getenv('DB_PATH') ?: 'data/mailshield.sqlite';

// --- LOGIN LOGIK ---
if (isset($_POST['login'])) {
    if ($_POST['user'] === $admin_user && $_POST['pass'] === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = $lang['incorrect_login'] ?? 'Login fehlgeschlagen';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /");
    exit;
}

// --- ADMIN AKTIONEN ---
if (isset($_SESSION['admin_logged_in'])) {

    // 1. UNIVERSAL JSON EXPORT (Für Migration)
    if (isset($_POST['export_json'])) {
        $data = [
            'shields' => $db->query("SELECT * FROM shields")->fetchAll(PDO::FETCH_ASSOC),
            'metadata' => $db->query("SELECT * FROM metadata")->fetchAll(PDO::FETCH_ASSOC),
            'rate_limits' => $db->query("SELECT * FROM rate_limits")->fetchAll(PDO::FETCH_ASSOC)
        ];
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="mailshield_migration_' . date('Y-m-d') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    // 2. UNIVERSAL JSON IMPORT
    if (isset($_FILES['import_json'])) {
        $file = $_FILES['import_json'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $data = json_decode(file_get_contents($file['tmp_name']), true);
            if ($data) {
                try {
                    $db->beginTransaction();
                    $db->exec("DELETE FROM shields; DELETE FROM metadata; DELETE FROM rate_limits;");
                    
                    $stmtS = $db->prepare("INSERT INTO shields (hash, encrypted_email, slug, created_at) VALUES (?, ?, ?, ?)");
                    foreach ($data['shields'] as $row) {
                        $stmtS->execute([$row['hash'], $row['encrypted_email'], $row['slug'], $row['created_at']]);
                    }
                    
                    $stmtM = $db->prepare("INSERT INTO metadata (key, value) VALUES (?, ?)");
                    foreach ($data['metadata'] as $row) {
                        $stmtM->execute([$row['key'], $row['value']]);
                    }

                    $stmtR = $db->prepare("INSERT INTO rate_limits (ip_hash, request_count, last_request) VALUES (?, ?, ?)");
                    foreach ($data['rate_limits'] as $row) {
                        $stmtR->execute([$row['ip_hash'], $row['request_count'], $row['last_request']]);
                    }
                    $db->commit();
                    $success = "Daten erfolgreich migriert!";
                } catch (Exception $e) {
                    $db->rollBack();
                    $error = "Migrationsfehler: " . $e->getMessage();
                }
            }
        }
    }

    // 3. SQLITE FILE EXPORT (Nur wenn SQLite aktiv)
    if (isset($_POST['export_db']) && $driver === 'sqlite') {
        if (file_exists($db_path)) {
            header('Content-Type: application/x-sqlite3');
            header('Content-Disposition: attachment; filename="mailshield_backup.sqlite"');
            readfile($db_path);
            exit;
        }
    }

    // 4. DATENBANK LEEREN
    if (isset($_POST['reset_db'])) {
        $db->exec("DELETE FROM shields; DELETE FROM rate_limits;");
        $success = $lang['database_cleared'] ?? 'Datenbank wurde geleert.';
    }
}
?>
<!DOCTYPE html>
<html lang="de" x-data="{ darkMode: localStorage.getItem('theme') === 'dark', showConfirm: false }" :class="{ 'dark': darkMode }">
<head> 
    <meta charset="UTF-8">
    <title>Admin Panel | MailShield</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center p-6 transition-colors">
    <div class="max-w-md w-full">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold mb-6 text-center">Admin Login</h1>
                <form method="POST" class="space-y-4">
                    <input type="text" name="user" placeholder="Username" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="password" name="pass" placeholder="Passwort" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <button name="login" class="w-full bg-blue-600 py-4 rounded-xl font-bold hover:bg-blue-700 transition-all text-white">Anmelden</button>
                    <?php if (isset($error)) echo "<p class='text-red-500 text-center text-sm'>$error</p>"; ?>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 text-center">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Dashboard</h1>
                    <span class="text-[10px] uppercase tracking-widest opacity-30">Active Driver: <?= strtoupper($driver) ?></span>
                </div>
                
                <?php if (isset($success)) echo "<p class='mb-6 text-green-500 font-bold'>$success</p>"; ?>
                <?php if (isset($error)) echo "<p class='mb-6 text-red-500 font-bold'>$error</p>"; ?>

                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-3">
                        <p class="text-xs font-bold opacity-50 uppercase tracking-tighter">Migration & Backup (JSON)</p>
                        <form method="POST">
                            <button name="export_json" class="w-full bg-blue-600/10 text-blue-500 py-3 rounded-xl font-bold hover:bg-blue-600 hover:text-white transition-all text-sm flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 512 512"><path fill="currentColor" d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>
                                Exportieren
                            </button>
                        </form>
                        <form method="POST" enctype="multipart/form-data" x-ref="jsonForm">
                            <input type="file" name="import_json" class="hidden" x-ref="jsonInput" @change="$refs.jsonForm.submit()">
                            <button type="button" @click="$refs.jsonInput.click()" class="w-full bg-teal-600/10 text-teal-500 py-3 rounded-xl font-bold hover:bg-teal-600 hover:text-white transition-all text-sm flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 512 512"><path fill="currentColor" d="M288 109.3V352c0 17.7-14.3 32-32 32s-32-14.3-32-32V109.3l-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352H192l27.5 27.5c25 25 65.5 25 90.5 0L337.5 352H448c35.3 0 64 28.7 64 64v32c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V416c0-35.3 28.7-64 64-64zM432 456a24 24 0 1 0 0-48 24 24 0 1 0 0 48z"/></svg>
                                Importieren
                            </button>
                        </form>
                    </div>

                    <?php if ($driver === 'sqlite'): ?>
                    <form method="POST">
                        <button name="export_db" class="w-full border border-gray-200 dark:border-gray-700 py-3 rounded-xl text-xs opacity-50 hover:opacity-100 transition-all">SQLite-Datei Backup</button>
                    </form>
                    <?php endif; ?>

                    <hr class="border-gray-200 dark:border-gray-700 my-4">

                    <button @click="showConfirm = true" class="w-full bg-red-600/10 text-red-500 py-4 rounded-xl font-bold hover:bg-red-600 hover:text-white transition-all"><?= $lang['admin_empty_db'] ?> 🔥</button>
                    <a href="?logout" class="block w-full bg-gray-200 dark:bg-gray-700 py-4 rounded-xl font-bold italic opacity-50 hover:opacity-100 transition-all">Logout</a>
                </div>
            </div>

            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div @click.away="showConfirm = false" class="bg-white dark:bg-gray-800 p-8 rounded-3xl max-w-sm w-full shadow-2xl">
                    <h2 class="text-xl font-bold mb-4"><?= $lang['sure'] ?></h2>
                    <p class="opacity-60 mb-8 text-sm text-center"><?= $lang['data_deleted'] ?></p>
                    <div class="flex gap-4">
                        <button @click="showConfirm = false" class="flex-1 py-3 rounded-xl bg-gray-200 dark:bg-gray-700"><?= $lang['cancel'] ?></button>
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