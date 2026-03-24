<?php
session_start();
error_reporting(0);
require_once '../core/Crypto.php';
require_once '../core/Database.php';
require_once '../core/RateLimiter.php';

$db_instance = new Database(); // Retrieves everything from ENV
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

// Support-Routing
if ($slug === 'support') {
    require_once 'support.php';
    exit;
}

// Support-Routing
if ($slug === 'badge') {
    require_once 'badge.php';
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

        $app_url = rtrim(getenv('APP_URL') ?: 'http://localhost', '/');
        $full_url = $app_url . '/' . $final_slug;

        // Badge URLs
        $badge_url = $app_url . '/badge';
        $badge_md = '[![Protected by MailShield](' . $badge_url . ')](' . $full_url . ')';
        $badge_html = '<a href="' . $full_url . '"><img src="' . $badge_url . '" alt="Protected by MailShield"></a>';

        $generated_links = [
            'url'      => $full_url,
            'html'     => '<a href="' . $full_url . '" target="_blank">' . $lang['view_title'] . '</a>',
            'markdown' => '[' . $lang['view_title'] . '](' . $full_url . ')',
            'badge_md' => $badge_md,
            'badge_html' => $badge_html
        ];
    }
}

$total_emails = $db->query("SELECT COUNT(*) FROM shields")->fetchColumn();
$db_since = $db->query("SELECT value FROM metadata WHERE key = 'db_created_at'")->fetchColumn();

$decrypted_email = null;
$is_view_page = false;

if (!empty($slug) && $slug !== 'index.php' && $slug !== 'admin') {
    $is_view_page = true;
    $stmt = $db->prepare("SELECT encrypted_email FROM shields WHERE slug = ?");
    $stmt->execute([$slug]);
    $entry = $stmt->fetch();
    if ($entry && isset($_POST['cf-turnstile-response'])) {
        $decrypted_email = Crypto::decrypt($entry['encrypted_email'], $app_key);
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>" x-data="app" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['title'] ?> | MailShield</title>
    <link rel="icon" type="image/png" href="/img/mailshield.png">

    <?php if (!$is_view_page): ?>
        <meta name="description" content="<?= $lang['subtitle'] ?> - Schütze deine E-Mail-Adresse vor Bots und Scrapern mit sicheren, verschlüsselten Links.">
        <meta name="keywords" content="Email Shield, Bot Protection, Email Scraper Protection, Privacy, Link Generator">
        <meta property="og:title" content="<?= $lang['title'] ?>">
        <meta property="og:description" content="<?= $lang['subtitle'] ?>">
        <meta property="og:image" content="/img/mailshield.png">
        <meta property="og:type" content="website">
        <meta name="robots" content="index, follow">
    <?php else: ?>
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen flex flex-col items-center justify-center p-4">

    <?php if ($admin_enabled): ?>
        <a href="/admin" class="fixed top-4 right-4 text-xs opacity-20 hover:opacity-100 transition-opacity inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 512 512">
                <path fill="currentColor" d="M243.6 37.3c8-3.4 17-3.4 25 0l176.7 75c11.3 4.8 18.9 15.5 18.8 27.6-.5 94-39.4 259.8-195.5 334.5-7.9 3.8-17.2 3.8-25.1 0-156.1-74.7-195-240.4-195.4-334.5-.1-12.1 7.5-22.8 18.8-27.6l176.7-75zM281.1 7.8c-16-6.8-34-6.8-50 0L54.3 82.8c-22 9.3-38.4 31-38.3 57.2 .5 99.2 41.3 280.7 213.6 363.2 16.7 8 36.1 8 52.8 0 172.4-82.5 213.2-264 213.6-363.2 .1-26.2-16.3-47.9-38.3-57.2L281.1 7.8zM200 128l-21.3 0c-32.4 0-58.7 26.3-58.7 58.7l0 29.3c0 22.6 13.4 42.1 32.7 50.9l-15 29.9c-4 7.9-.7 17.5 7.2 21.5s17.5 .7 21.5-7.2L186.4 271c26-4.9 45.6-27.7 45.6-55l0-56 60.2 0c6.1 0 11.6 3.4 14.3 8.8l7.2 14.3c2.7 5.4 8.2 8.8 14.3 8.8l56 0 0 16c0 35.3-28.7 64-64 64l-48 0c-8.8 0-16 7.2-16 16l0 80c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64 32 0c53 0 96-43 96-96l0-16c0-17.7-14.3-32-32-32l-46.1 0-2.7-5.5c-8.1-16.3-24.8-26.5-42.9-26.5L200 128zM175.8 240c-13.1-.1-23.7-10.8-23.7-24l0-29.3c0-10.9 6.6-20.3 16-24.4 3.3-1.4 6.9-2.2 10.7-2.2l21.3 0 0 56c0 13.2-10.6 23.9-23.7 24l-.5 0zM272 208a16 16 0 1 0 0-32 16 16 0 1 0 0 32z" />
            </svg>
        </a>
    <?php endif; ?>

    <div class="max-w-xl w-full">
        <header class="text-center mb-8">
            <h1 class="text-5xl font-extrabold mb-4 flex items-center justify-center gap-4">
                <img src="/img/icon.png" alt="Logo" class="h-[0.9em] w-auto drop-shadow-sm">

                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-teal-400">
                    <?= $lang['title'] ?>
                </span>

                <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                    class="group relative p-2 ml-1 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-full border border-gray-200/50 dark:border-gray-700/50 hover:border-blue-500/50 transition-all duration-500">

                    <div class="absolute inset-0 rounded-full bg-blue-500/0 group-hover:bg-blue-500/10 blur-md transition-all duration-500"></div>

                    <div class="relative flex items-center justify-center">
                        <span x-show="!darkMode" class="text-gray-600 group-hover:text-blue-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 512 512">
                                <path fill="currentColor" d="M256 32c9.5 0 18.9 .6 28 1.7-60.1 38.3-100 105.6-100 182.3 0 117.2 96.4 212.8 210.7 215.9-38.2 30.1-86.3 48.1-138.7 48.1-123.7 0-224-100.3-224-224S132.3 32 256 32zm0-32C114.6 0 0 114.6 0 256S114.6 512 256 512c68.8 0 131.3-27.2 177.3-71.4 7.3-7 9.4-17.9 5.3-27.1s-13.7-14.9-23.8-14.1c-105.4 8.4-198.8-77.3-198.8-183.4 0-72.1 41.5-134.6 102.1-164.8 9.1-4.5 14.3-14.3 13.1-24.4S322.6 8.5 312.7 6.3C294.4 2.2 275.4 0 256 0z" />
                            </svg>
                        </span>

                        <span x-show="darkMode" class="text-yellow-400 group-hover:text-yellow-300 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 512 512">
                                <path fill="currentColor" d="M240 104c0 8.8 7.2 16 16 16s16-7.2 16-16l0-88c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 88zm16 88a64 64 0 1 1 0 128 64 64 0 1 1 0-128zm0 160a96 96 0 1 0 0-192 96 96 0 1 0 0 192zm0 160c8.8 0 16-7.2 16-16l0-80c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 80c0 8.8 7.2 16 16 16zM0 256c0 8.8 7.2 16 16 16l80 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-80 0c-8.8 0-16 7.2-16 16zm408-16c-8.8 0-16 7.2-16 16s7.2 16 16 16l88 0c8.8 0 16-7.2 16-16s-7.2-16-16-16l-88 0zM75 437c6.2 6.2 16.4 6.2 22.6 0l56.6-56.6c6.2-6.2 6.2-16.4 0-22.6s-16.4-6.2-22.6 0L75 414.4c-6.2 6.2-6.2 16.4 0 22.6zM352.2 137.2c-6.2 6.2-6.2 16.4 0 22.6s16.4 6.2 22.6 0L437 97.6c6.2-6.2 6.2-16.4 0-22.6s-16.4-6.2-22.6 0l-62.2 62.2zM75 75c-6.2 6.2-6.2 16.4 0 22.6l56.6 56.6c6.2 6.2 16.4 6.2 22.6 0s6.2-16.4 0-22.6L97.6 75c-6.2-6.2-16.4-6.2-22.6 0zM374.8 352.2c-6.2-6.2-16.4-6.2-22.6 0s-6.2 16.4 0 22.6L414.4 437c6.2 6.2 16.4 6.2 22.6 0s6.2-16.4 0-22.6l-62.2-62.2z" />
                            </svg>
                        </span>
                    </div>
                </button>
            </h1>
            <p class="text-gray-500 dark:text-gray-400"><?= $lang['subtitle'] ?></p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-[10px] uppercase tracking-widest font-bold text-gray-400 dark:text-gray-500">
                <span class="flex items-center gap-2 px-3 py-1.5 bg-gray-100/50 dark:bg-gray-800/40 rounded-full border border-gray-200/20 dark:border-gray-700/20 transition-colors hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 384 512">
                        <defs>
                            <linearGradient id="g1" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#3b82f6" />
                                <stop offset="100%" style="stop-color:#2dd4bf" />
                            </linearGradient>
                        </defs>
                        <path fill="url(#g1)" d="M96 96l0 64 192 0 0-64c0-53-43-96-96-96S96 43 96 96zM64 160l0-64C64 25.3 121.3-32 192-32S320 25.3 320 96l0 64c35.3 0 64 28.7 64 64l0 224c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 224c0-35.3 28.7-64 64-64zM32 224l0 224c0 17.7 14.3 32 32 32l256 0c17.7 0 32-14.3 32-32l0-224c0-17.7-14.3-32-32-32L64 192c-17.7 0-32 14.3-32 32z" />
                    </svg>
                    <?= $lang['no_tracking'] ?>
                </span>

                <span class="flex items-center gap-2 px-3 py-1.5 bg-gray-100/50 dark:bg-gray-800/40 rounded-full border border-gray-200/20 dark:border-gray-700/20 transition-colors hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 576 512">
                        <defs>
                            <linearGradient id="g2" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#3b82f6" />
                                <stop offset="100%" style="stop-color:#2dd4bf" />
                            </linearGradient>
                        </defs>
                        <path fill="url(#g2)" d="M27.3-27.2c-6.2-6.2-16.4-6.2-22.6 0s-6.2 16.4 0 22.6l544 544c6.2 6.2 16.4 6.2 22.6 0s6.2-16.4 0-22.6L466.4 411.9c4.9-4.1 9.6-8.2 14.1-12.5 46.8-43.5 78.1-95.4 93-131.1 3.3-7.9 3.3-16.7 0-24.6-14.9-35.7-46.2-87.7-93-131.1-47.1-43.7-111.8-80.6-192.6-80.6-60 0-111.1 20.3-152.8 48.7L27.3-27.2zm131 131c36.4-23.7 79.7-39.8 129.7-39.8 140.8 0 229.3 128 256 192-13.3 32-42.1 80-85.2 120-4.9 4.5-9.9 8.9-15.1 13.2l-54.6-54.6c16.9-21.7 26.9-48.9 26.9-78.5 0-70.7-57.3-128-128-128-29.6 0-56.9 10-78.5 26.9l-51.2-51.2zM366.2 311.7L232.3 177.8c15.7-11.2 34.9-17.8 55.7-17.8 53 0 96 43 96 96 0 20.8-6.6 40-17.8 55.7zM74.8 133.3c-35.5 38.5-59.7 80.2-72.3 110.4-3.3 7.9-3.3 16.7 0 24.6 14.9 35.7 46.2 87.7 93 131.1 47.1 43.7 111.8 80.6 192.6 80.6 40.6 0 77.1-9.3 109.4-24.1L373 431.5c-25.8 10.3-54.1 16.5-85 16.5-140.8 0-229.3-128-256-192 11.2-26.8 33.2-64.9 65.4-100.1L74.8 133.3z" />
                    </svg>
                    <?= $lang['no_ads'] ?>
                </span>

                <span class="flex items-center gap-2 px-3 py-1.5 bg-gray-100/50 dark:bg-gray-800/40 rounded-full border border-gray-200/20 dark:border-gray-700/20 transition-colors hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 576 512">
                        <defs>
                            <linearGradient id="g3" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#3b82f6" />
                                <stop offset="100%" style="stop-color:#2dd4bf" />
                            </linearGradient>
                        </defs>
                        <path fill="url(#g3)" d="M378.9 64c-32.4 0-62.9 15.6-81.9 41.9l-28 38.7c-3 4.2-7.8 6.6-13 6.6s-10-2.5-13-6.6l-28-38.7 0 0c-19-26.3-49.5-41.9-81.9-41.9-55.9 0-101.1 45.3-101.1 101.1 0 55 34.4 107.1 71.8 152.5 42.1 51.2 93.4 96 128.5 122.9 6.2 4.8 14.4 7.5 23.7 7.5s17.4-2.7 23.7-7.5c35.1-26.8 86.4-71.7 128.5-122.9 37.3-45.4 71.8-97.5 71.8-152.5 0-55.9-45.3-101.1-101.1-101.1zM271 87.1c25-34.6 65.2-55.1 107.9-55.1 73.5 0 133.1 59.6 133.1 133.1 0 67.4-41.6 127.3-79.1 172.8-44.1 53.6-97.3 100.1-133.8 127.9-12.4 9.4-27.6 14.1-43.1 14.1s-30.8-4.6-43.1-14.1C176.4 438 123.2 391.5 79.1 338 41.6 292.4 0 232.5 0 165.1 0 91.6 59.6 32 133.1 32 175.8 32 216 52.5 241 87.1l15 20.7 15-20.7z" />
                    </svg>
                    <?= $lang['free'] ?>
                </span>
            </div>

            <div class="mt-8 flex justify-center gap-12">
                <div class="text-center">
                    <span class="block text-3xl font-bold text-blue-500"><?= (int)$total_emails ?></span>
                    <span class="text-xs uppercase tracking-widest opacity-60"><?= $lang['stats_protected'] ?></span>
                </div>
                <div class="text-center border-l border-gray-200 dark:border-gray-700 pl-12">
                    <span class="block text-3xl font-bold text-teal-500"><?= date('m/y', strtotime($db_since)) ?></span>
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
                    <a href="/" class="mt-6 inline-block text-blue-500 underline text-sm"><?= $lang['back'] ?></a>
                </div>
            <?php elseif ($is_view_page): ?>
                <form method="POST" class="text-center">
                    <h2 class="text-xl mb-4"><?= $lang['view_desc'] ?></h2>
                    <div class="cf-turnstile flex justify-center mb-6"
                        data-sitekey="<?= getenv('CF_SITE_KEY') ?>"
                        data-callback="onCaptchaVerified"
                        :data-theme="darkMode ? 'dark' : 'light'"></div>
                    <button type="submit"
                        :disabled="!captchaVerified"
                        :class="!captchaVerified ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                        <?= $lang['btn_view'] ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 576 512">
                            <path fill="currentColor" d="M288 64c-140.8 0-229.3 128-256 192 26.7 64 115.2 192 256 192 140.8 0 229.3-128 256-192-26.7-64-115.2-192-256-192zM95.4 112.6C142.5 68.8 207.2 32 288 32s145.5 36.8 192.6 80.6c46.8 43.5 78.1 95.4 93 131.1 3.3 7.9 3.3 16.7 0 24.6-14.9-35.7-46.2-87.7-93-131.1-47.1 43.7-111.8 80.6-192.6 80.6S142.5 443.2 95.4 399.4c-46.8-43.5-78.1-95.4-93-131.1-3.3-7.9-3.3-16.7 0-24.6 14.9-35.7-46.2-87.7 93-131.1zM288 352c53 0 96-43 96-96 0-43.3-28.7-79.9-68.1-91.9 2.7 8.8 4.1 18.2 4.1 27.9 0 53-43 96-96 96-9.7 0-19.1-1.4-27.9-4.1 11.9 39.4 48.6 68.1 91.9 68.1zM160.2 263.8c-.2-2.6-.2-5.2-.2-7.8 0-12.2 1.7-23.9 4.9-35 .3-.9 .5-1.8 .8-2.7 12.4-40.4 44.3-72.2 84.7-84.7 11.9-3.7 24.6-5.6 37.7-5.6 2.5 0 5 .1 7.4 .2l.4 0c67.1 4 120.2 59.7 120.2 127.8 0 70.7-57.3 128-128 128-68.1 0-123.8-53.2-127.8-120.2zm32.1-16.1c9.3 5.3 20.1 8.4 31.6 8.4 35.3 0 64-28.7 64-64 0-11.5-3-22.3-8.4-31.6-46.4 4-83.3 40.9-87.3 87.3z" />
                        </svg>
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <input type="text" name="honeypot" class="hidden">
                    <input type="email" name="email" required placeholder="<?= $lang['input_placeholder'] ?>"
                        class="w-full px-6 py-4 bg-gray-100 dark:bg-gray-700 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    <div class="cf-turnstile flex justify-center"
                        data-sitekey="<?= getenv('CF_SITE_KEY') ?>"
                        data-callback="onCaptchaVerified"
                        :data-theme="darkMode ? 'dark' : 'light'"></div>
                    <button type="submit"
                        :disabled="!captchaVerified"
                        :class="!captchaVerified ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                        <?= $lang['btn_generate'] ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 512 512">
                            <path fill="currentColor" d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z" />
                        </svg>
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($message): ?>
                <p class="mt-4 text-red-500 text-center text-sm"><?= $message ?></p>
            <?php endif; ?>

            <?php if ($generated_links): ?>
                <div class="mt-8 space-y-4">
                    <div class="space-y-2">
                        <?php foreach (['url', 'html', 'markdown'] as $key): ?>
                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <code class="text-xs flex-1 truncate opacity-70 px-2"><?= htmlspecialchars($generated_links[$key]) ?></code>
                                <button @click="copyLink(<?= htmlspecialchars(json_encode($generated_links[$key])) ?>)" class="text-blue-500 hover:text-blue-400 p-2 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 384 512">
                                        <path fill="currentColor" d="M280 64h40c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128c0-35.3 28.7-64 64-64h40c8.8 0 16-7.2 16-16V48C120 21.5 141.5 0 168 0h48c26.5 0 48 21.5 48 48v16c0 8.8 7.2 16 16 16zM168 48v16h48V48c0-8.8-7.2-16-16-16h-48c-8.8 0-16 7.2-16 16zM64 112v352h256V112H64z" />
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-3 text-center"><?= $lang['badge_promo'] ?></p>
                        <div class="flex flex-col items-center gap-4">
                            <img src="/badge" alt="MailShield Badge" class="h-8">

                            <div class="w-full space-y-2">
                                <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                    <span class="text-[9px] font-bold opacity-40 uppercase w-12">MD</span>
                                    <code class="text-[10px] flex-1 truncate opacity-70"><?= htmlspecialchars($generated_links['badge_md']) ?></code>
                                    <button @click="copyLink(<?= htmlspecialchars(json_encode($generated_links['badge_md'])) ?>)" class="text-teal-500 hover:text-teal-400 p-2 shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 384 512">
                                            <path fill="currentColor" d="M280 64h40c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128c0-35.3 28.7-64 64-64h40c8.8 0 16-7.2 16-16V48C120 21.5 141.5 0 168 0h48c26.5 0 48 21.5 48 48v16c0 8.8 7.2 16 16 16zM168 48v16h48V48c0-8.8-7.2-16-16-16h-48c-8.8 0-16 7.2-16 16zM64 112v352h256V112H64z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                    <span class="text-[9px] font-bold opacity-40 uppercase w-12">HTML</span>
                                    <code class="text-[10px] flex-1 truncate opacity-70"><?= htmlspecialchars($generated_links['badge_html']) ?></code>
                                    <button @click="copyLink(<?= htmlspecialchars(json_encode($generated_links['badge_html'])) ?>)" class="text-teal-500 hover:text-teal-400 p-2 shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 384 512">
                                            <path fill="currentColor" d="M280 64h40c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128c0-35.3 28.7-64 64-64h40c8.8 0 16-7.2 16-16V48C120 21.5 141.5 0 168 0h48c26.5 0 48 21.5 48 48v16c0 8.8 7.2 16 16 16zM168 48v16h48V48c0-8.8-7.2-16-16-16h-48c-8.8 0-16 7.2-16 16zM64 112v352h256V112H64z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <a href="/" class="text-sm font-medium opacity-50 hover:opacity-100 transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                            <?= $lang['back'] ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <section class="mt-20 mb-12">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">
                    <?= $lang['how_it_works_title'] ?>
                </h2>
                <p class="text-gray-500 dark:text-gray-400">
                    <?= $lang['how_it_works_subtitle'] ?>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 border border-blue-500/20 shadow-lg shadow-blue-500/5 transition-transform hover:scale-110 duration-300">
                        <span class="text-2xl font-black text-blue-500">1</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2"><?= $lang['step1_title'] ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed px-4">
                        <?= $lang['step1_desc'] ?>
                    </p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-teal-500/10 rounded-2xl flex items-center justify-center mb-6 border border-teal-500/20 shadow-lg shadow-teal-500/5 transition-transform hover:scale-110 duration-300">
                        <span class="text-2xl font-black text-teal-500">2</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2"><?= $lang['step2_title'] ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed px-4">
                        <?= $lang['step2_desc'] ?>
                    </p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-purple-500/10 rounded-2xl flex items-center justify-center mb-6 border border-purple-500/20 shadow-lg shadow-purple-500/5 transition-transform hover:scale-110 duration-300">
                        <span class="text-2xl font-black text-purple-500">3</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2"><?= $lang['step3_title'] ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed px-4">
                        <?= $lang['step3_desc'] ?>
                    </p>
                </div>
            </div>
        </section>

        <footer class="mt-16 mb-8 text-center space-y-6">
            <a href="/support" class="inline-flex items-center gap-2 text-[11px] font-bold tracking-[0.2em] uppercase opacity-30 hover:opacity-100 transition-all duration-300 group">
                <span class="p-1.5 rounded-lg bg-gray-200 dark:bg-gray-800 group-hover:bg-red-500/10 group-hover:text-red-500 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.84-8.84 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                    </svg>
                </span>
                Support Project
            </a>

            <div class="flex justify-center items-center gap-4">
                <div class="h-px w-8 bg-gradient-to-r from-transparent to-gray-300 dark:to-gray-700"></div>
                <div class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></div>
                <div class="h-px w-8 bg-gradient-to-l from-transparent to-gray-300 dark:to-gray-700"></div>
            </div>

            <p class="text-[10px] uppercase tracking-widest text-gray-400 dark:text-gray-500">
                <?= $lang['copy'] ?>
                <a href="https://github.com/RonDevHub/MailShield"
                    target="_blank"
                    class="relative group inline-block bg-clip-text text-semibold text-transparent bg-gradient-to-r from-blue-500 to-teal-400 transition-all duration-300 hover:scale-105">
                    <span>RonDevHub</span>
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-blue-500 to-teal-400 transition-all duration-300 group-hover:w-full"></span>
                    <span class="absolute inset-0 bg-blue-500/0 group-hover:bg-blue-500/5 blur-xl transition-all duration-500 -z-10 rounded-full"></span>
                </a>
            </p>
        </footer>
    </div>

    <div x-show="toast" x-cloak x-transition class="fixed top-10 left-1/2 -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-full shadow-xl font-bold z-[100]" x-text="toastMsg"></div>

    <script>
        // Global callback for turnstiles (outside of Alpine)
        function onCaptchaVerified(token) {
            window.dispatchEvent(new CustomEvent('captcha-success', {
                detail: token
            }));
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toast: false,
                toastMsg: '',
                captchaVerified: false,
                init() {
                    this.$watch('darkMode', val => document.documentElement.classList.toggle('dark', val));
                    // Event listener for successful CAPTCHA
                    window.addEventListener('captcha-success', () => {
                        this.captchaVerified = true;
                    });
                },
                copyLink(text) {
                    if (!navigator.clipboard) return;
                    navigator.clipboard.writeText(text).then(() => {
                        this.toastMsg = <?= json_encode($lang['copy_success']) ?>;
                        this.toast = true;
                        setTimeout(() => {
                            this.toast = false;
                        }, 2000);
                    });
                }
            }))
        })
    </script>
</body>

</html>