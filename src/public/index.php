<?php
session_start();
error_reporting(0);
require_once '../core/Crypto.php';
require_once '../core/Database.php';
require_once '../core/RateLimiter.php';

$db_instance = new Database(getenv('DB_PATH') ?: 'data/mailshield.sqlite');
$db = $db_instance->getDB();
$limiter = new RateLimiter($db);
$app_key = getenv('APP_KEY');
$admin_enabled = filter_var(getenv('ADMIN_SITE') ?: 'true', FILTER_VALIDATE_BOOLEAN);

$user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
$ip_hash = hash('sha256', $user_ip);

$lang_code = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'de', 0, 2);
$lang = (file_exists("../lang/$lang_code.php")) ? require "../lang/$lang_code.php" : require "../lang/en.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$slug = ltrim($uri, '/');

// Admin-Routing
if ($slug === 'admin') {
    if (!$admin_enabled) {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        exit;
    }
    require_once 'admin.php';
    exit;
}

$message = '';
$generated_links = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    if (!$limiter->check($ip_hash)) {
        $message = $lang['rate_limit'];
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) || strlen($_POST['email']) > 255) {
        $message = $lang['invalid_email'];
    } elseif (!empty($_POST['honeypot'])) {
        $message = "Nice try, Bot!";
    } else {
        $email = $_POST['email'];
        $email_hash = Crypto::hash($email);
        
        $stmt = $db->prepare("SELECT slug FROM shields WHERE hash = ?");
        $stmt->execute([$email_hash]);
        $existing = $stmt->fetch();

        $final_slug = $existing ? $existing['slug'] : bin2hex(random_bytes(4));
        if (!$existing) {
            $encrypted = Crypto::encrypt($email, $app_key);
            $stmt = $db->prepare("INSERT INTO shields (hash, encrypted_email, slug) VALUES (?, ?, ?)");
            $stmt->execute([$email_hash, $encrypted, $final_slug]);
        }
        
        $full_url = (getenv('APP_URL') ?: 'http://localhost') . '/' . $final_slug;
        $generated_links = [
            'url'      => $full_url,
            'html'     => '<a href="' . $full_url . '" target="_blank">' . $lang['view_title'] . '</a>',
            'markdown' => '[' . $lang['view_title'] . '](' . $full_url . ')'
        ];
    }
}

$total_emails = $db->query("SELECT COUNT(*) FROM shields")->fetchColumn();
$db_since = $db->query("SELECT value FROM metadata WHERE key = 'db_created_at'")->fetchColumn();

$decrypted_email = null;
if (!empty($slug) && $slug !== 'index.php' && $slug !== 'admin') {
    $stmt = $db->prepare("SELECT encrypted_email FROM shields WHERE slug = ?");
    $stmt->execute([$slug]);
    $entry = $stmt->fetch();
    if ($entry && isset($_POST['cf-turnstile-response'])) {
        $decrypted_email = Crypto::decrypt($entry['encrypted_email'], $app_key);
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>" 
      x-data="app" 
      x-init="init()"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['title'] ?></title>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen flex flex-col items-center justify-center p-4">

    <?php if ($admin_enabled): ?>
    <a href="/admin" class="fixed top-4 right-4 text-xs opacity-20 hover:opacity-100 transition-opacity">🛡️ Admin</a>
    <?php endif; ?>

    <div class="max-w-xl w-full">
        <header class="text-center mb-8">
            <h1 class="text-5xl font-extrabold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-teal-400"><?= $lang['title'] ?></h1>
            <p class="text-gray-500 dark:text-gray-400"><?= $lang['subtitle'] ?></p>
            
            <div class="mt-8 flex justify-center gap-12">
                <div class="text-center">
                    <span class="block text-3xl font-bold text-blue-500"><?= (int)$total_emails ?></span>
                    <span class="text-xs uppercase tracking-widest opacity-60"><?= $lang['stats_protected'] ?></span>
                </div>
                <div class="text-center border-l border-gray-200 dark:border-gray-700 pl-12">
                    <span class="block text-3xl font-bold text-teal-500"><?= date('d.m.y', strtotime($db_since)) ?></span>
                    <span class="text-xs uppercase tracking-widest opacity-60"><?= $lang['stats_since'] ?></span>
                </div>
            </div>
        </header>

        <main class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700">
            <?php if ($decrypted_email): ?>
                <div class="text-center py-6">
                    <h2 class="text-xl mb-4"><?= $lang['view_title'] ?></h2>
                    <div class="p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl font-mono text-lg break-all">
                        <?= htmlspecialchars($decrypted_email) ?>
                    </div>
                    <a href="/" class="mt-6 inline-block text-blue-500 underline text-sm">Zurück</a>
                </div>
            <?php elseif ($slug && $slug !== 'index.php' && $slug !== 'admin'): ?>
                <form method="POST" class="text-center">
                    <h2 class="text-xl mb-4"><?= $lang['view_desc'] ?></h2>
                    <div class="cf-turnstile flex justify-center mb-6" data-sitekey="<?= getenv('CF_SITE_KEY') ?>"></div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all">Anzeigen 👁️</button>
                </form>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <input type="text" name="honeypot" class="hidden">
                    <div>
                        <input type="email" name="email" required placeholder="<?= $lang['input_placeholder'] ?>" 
                               class="w-full px-6 py-4 bg-gray-100 dark:bg-gray-700 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    </div>
                    <div class="cf-turnstile flex justify-center" data-sitekey="<?= getenv('CF_SITE_KEY') ?>"></div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                        <?= $lang['btn_generate'] ?> 🚀
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($message): ?>
                <p class="mt-4 text-red-500 text-center text-sm"><?= $message ?></p>
            <?php endif; ?>

            <?php if ($generated_links): ?>
                <div class="mt-8 space-y-3">
                    <?php foreach ($generated_links as $type => $val): ?>
                        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <code class="text-xs flex-1 truncate opacity-70 px-2"><?= htmlspecialchars($val) ?></code>
                            <button @click="copyLink(<?= htmlspecialchars(json_encode($val)) ?>)" class="text-blue-500 hover:text-blue-400 p-2 shrink-0">📋</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div x-show="toast" x-cloak x-transition class="fixed top-10 bg-green-500 text-white px-6 py-3 rounded-full shadow-xl font-bold z-[100]" x-text="toastMsg"></div>

    <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" 
            class="fixed bottom-8 right-8 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 hover:scale-110 transition-transform z-50">
        <span x-show="!darkMode">🌙</span><span x-show="darkMode">☀️</span>
    </button>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toast: false,
                toastMsg: '',
                init() {
                    this.$watch('darkMode', val => document.documentElement.classList.toggle('dark', val));
                },
                copyLink(text) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.toastMsg = <?= json_encode($lang['copy_success']) ?>;
                        this.toast = true;
                        setTimeout(() => this.toast = false, 2000);
                    });
                }
            }))
        })
    </script>
</body>
</html>