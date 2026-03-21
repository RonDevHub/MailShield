<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'de' ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['app_name'] ?? 'MailShield' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <style>
        .darkcard { background-color: #1e293b; }
        .darkbg { background-color: #0f172a; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-darkbg min-h-full flex flex-col transition-colors duration-300">

    <header class="container mx-auto px-4 py-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">MailShield</h1>
        <div class="flex items-center gap-4">
            <form method="GET" class="text-sm">
                <select name="lang" onchange="this.form.submit()" class="bg-white dark:bg-slate-800 border rounded px-2 py-1">
                    <option value="de" <?= ($langCode ?? 'de') === 'de' ? 'selected' : '' ?>>DE</option>
                    <option value="en" <?= ($langCode ?? 'en') === 'en' ? 'selected' : '' ?>>EN</option>
                </select>
            </form>
            <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-slate-700">
                🌙
            </button>
        </div>
    </header>

    <main class="container mx-auto px-4 flex-grow flex items-center justify-center py-12">
        <div class="w-full max-w-2xl">
            <?php include __DIR__ . "/{$view}.php"; ?>
        </div>
    </main>

    <footer class="container mx-auto px-4 py-12 text-center text-slate-500 text-sm">
        <div class="mb-4">
            <span class="text-3xl font-bold text-indigo-600 block mb-1"><?= $totalShielded ?? 0 ?></span>
            <p><?= $lang['stats_suffix'] ?? 'E-Mails erfolgreich geschützt' ?></p>
        </div>
        <p>Online seit: <?= $config['online_since'] ?? '2026' ?> | Support me</p>
    </footer>

    <script>
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
</body>
</html>