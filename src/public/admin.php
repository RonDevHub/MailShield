<?php
if (session_status() === PHP_SESSION_NONE) session_start();

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

// --- ADMIN AKTIONEN (Nur wenn eingeloggt) ---
if (isset($_SESSION['admin_logged_in'])) {

    // 1. DATENBANK EXPORT (Download der .sqlite Datei)
    if (isset($_POST['export_db'])) {
        if (file_exists($db_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/x-sqlite3');
            header('Content-Disposition: attachment; filename="mailshield_backup_' . date('Y-m-d') . '.sqlite"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($db_path));
            readfile($db_path);
            exit;
        }
    }

    // 2. DATENBANK IMPORT
    if (isset($_FILES['import_file'])) {
        $file = $_FILES['import_file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Prüfung, ob es eine SQLite Datei sein könnte
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (in_array($ext, ['sqlite', 'db', 'sqlite3'])) {
                if (move_uploaded_file($file['tmp_name'], $db_path)) {
                    $success = "Datenbank erfolgreich importiert!";
                } else {
                    $error = "Fehler beim Ersetzen der Datenbank.";
                }
            } else {
                $error = "Ungültiges Dateiformat. Bitte eine .sqlite Datei hochladen.";
            }
        }
    }

    // 3. DATENBANK LEEREN
    if (isset($_POST['reset_db'])) {
        require_once '../core/Database.php';
        $db = (new Database($db_path))->getDB();
        $db->exec("DELETE FROM shields; DELETE FROM rate_limits;");
        $success = $lang['database_cleared'] ?? 'Datenbank wurde geleert.';
    }
}
?>
<!DOCTYPE html>
<html lang="de" x-data="{ darkMode: localStorage.getItem('theme') === 'dark', showConfirm: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <title><?= $lang['admin_title'] ?? 'Admin Panel' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center p-6 transition-colors">

    <div class="max-w-md w-full">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold mb-6 text-center"><?= $lang['admin_login'] ?? 'Admin Login' ?></h1>
                <form method="POST" class="space-y-4">
                    <input type="text" name="user" placeholder="Username" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="password" name="pass" placeholder="Passwort" required class="w-full p-4 bg-gray-100 dark:bg-gray-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <button name="login" class="w-full bg-blue-600 py-4 rounded-xl font-bold hover:bg-blue-700 transition-all text-white">Anmelden</button>
                    <?php if (isset($error)) echo "<p class='text-red-500 text-center text-sm'>$error</p>"; ?>
                </form>
                <a href="/" class="block text-center mt-6 text-sm opacity-50"><?= $lang['back'] ?? 'Zurück' ?></a>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 text-center">
                <h1 class="text-2xl font-bold mb-8">Dashboard</h1>
                
                <?php if (isset($success)) echo "<p class='mb-6 text-green-500 font-bold'>$success</p>"; ?>
                <?php if (isset($error)) echo "<p class='mb-6 text-red-500 font-bold'>$error</p>"; ?>

                <div class="space-y-4">
                    <form method="POST">
                        <button name="export_db" class="w-full bg-blue-600/10 text-blue-500 border border-blue-600/20 py-4 rounded-xl font-bold hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 448 512"><path fill="currentColor" d="M433.9 129.9l-83.9-83.9c-9.4-9.4-24.6-9.4-33.9 0L284.1 78c-4.7 4.7-7.3 11-7.3 17s2.6 12.3 7.3 17l19 19c4.7 4.7 11 7.3 17 7.3s12.3-2.6 17-7.3l20.1-20.1 55.4 55.4L382 217c-4.7 4.7-7.3 11-7.3 17s2.6 12.3 7.3 17l19 19c4.7 4.7 11 7.3 17 7.3s12.3-2.6 17-7.3l21.3-21.3c9.4-9.4 9.4-24.6 0-33.9zM224 256c-17.7 0-32 14.3-32 32s14.3 32 32 32s32-14.3 32-32s-14.3-32-32-32zm141.5-122.5L256 24.5V128h103.5c2.4 0 4.8-.9 6.6-2.5c4-3.5 4.5-9.5 1-13.5c-.4-.4-.7-.8-1.1-1.1zM64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V160c0-17-6.7-33.3-18.7-45.3L333.3 18.7C321.3 6.7 305 0 288 0H64zM96 64h128v80c0 17.7 14.3 32 32 32h80V448H64V64H96z"/></svg>
                            Backup erstellen (.sqlite)
                        </button>
                    </form>

                    <form method="POST" enctype="multipart/form-data" x-ref="importForm">
                        <input type="file" name="import_file" class="hidden" x-ref="fileInput" @change="$refs.importForm.submit()">
                        <button type="button" @click="$refs.fileInput.click()" class="w-full bg-teal-600/10 text-teal-500 border border-teal-600/20 py-4 rounded-xl font-bold hover:bg-teal-600 hover:text-white transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 448 512"><path fill="currentColor" d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c17.7 0 32 14.3 32 32v64c0 8.8 7.2 16 16 16H336c8.8 0 16-7.2 16-16V384c0-17.7 14.3-32 32-32s32 14.3 32 32v64c0 35.3-28.7 64-64 64H112c-35.3 0-64-28.7-64-64V384c0-17.7 14.3-32 32-32z"/></svg>
                            Backup einspielen
                        </button>
                    </form>

                    <hr class="border-gray-200 dark:border-gray-700 my-4">

                    <button @click="showConfirm = true" class="w-full bg-red-600/10 text-red-500 border border-red-600/20 py-4 rounded-xl font-bold hover:bg-red-600 hover:text-white transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 448 512"><path fill="currentColor" d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg>
                        Datenbank leeren 🔥
                    </button>
                    
                    <a href="?logout" class="block w-full bg-gray-200 dark:bg-gray-700 py-4 rounded-xl font-bold"><?= $lang['admin_logout'] ?? 'Abmelden' ?></a>
                    <a href="/" class="block text-sm opacity-50 mt-4"><?= $lang['back_main'] ?? 'Zurück zur Website' ?></a>
                </div>
            </div>

            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div @click.away="showConfirm = false" class="bg-white dark:bg-gray-800 p-8 rounded-3xl max-w-sm w-full shadow-2xl border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4"><?= $lang['sure'] ?? 'Bist du sicher?' ?></h2>
                    <p class="opacity-60 mb-8 text-sm text-center"><?= $lang['data_deleted'] ?? 'Alle generierten Links werden unwiderruflich gelöscht!' ?></p>
                    <div class="flex gap-4">
                        <button @click="showConfirm = false" class="flex-1 py-3 rounded-xl bg-gray-200 dark:bg-gray-700 font-bold"><?= $lang['cancel'] ?? 'Abbrechen' ?></button>
                        <form method="POST" class="flex-1">
                            <button name="reset_db" class="w-full py-3 rounded-xl bg-red-600 text-white font-bold"><?= $lang['yes_delete'] ?? 'Ja, löschen' ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>