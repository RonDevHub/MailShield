<?php
$lang_code = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'de', 0, 2);
$lang_file = "../lang/$lang_code.php";
$lang = (file_exists($lang_file)) ? require $lang_file : require "../lang/en.php";
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['support_title'] ?></title>
    <link rel="icon" type="image/png" href="/img/mailshield.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center p-6 transition-colors">

    <div class="max-w-lg w-full">
        <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 text-center">
            
            <div class="mb-6 inline-flex p-4 rounded-2xl bg-yellow-500/10 text-yellow-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/></svg>
            </div>

            <h1 class="text-3xl font-extrabold mb-2"><?= $lang['support_title'] ?></h1>
            <p class="text-gray-500 dark:text-gray-400 mb-8"><?= $lang['support_subtitle'] ?></p>

            <div class="grid gap-4">
                <a href="https://paypal.me/Depressionist1/4,99" target="_blank" class="flex items-center justify-between p-4 bg-blue-600/10 hover:bg-blue-600 hover:text-white border border-blue-600/20 rounded-2xl transition-all group">
                    <span class="font-bold"><?= $lang['paypal'] ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-50 group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>

                <a href="https://ko-fi.com/U6U31EV2VS" target="_blank" class="flex items-center justify-between p-4 bg-orange-600/10 hover:bg-orange-600 hover:text-white border border-orange-600/20 rounded-2xl transition-all group">
                    <span class="font-bold"><?= $lang['kofi'] ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-50 group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                </a>

                <a href="https://buymeacoffee.com/rondev" target="_blank" class="flex items-center justify-between p-4 bg-yellow-600/10 hover:bg-yellow-600 hover:text-white border border-yellow-600/20 rounded-2xl transition-all group">
                    <span class="font-bold"><?= $lang['bmac'] ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-50 group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                </a>

                <a href="https://github.com/sponsors/RonDevHub" target="_blank" class="flex items-center justify-between p-4 bg-purple-600/10 hover:bg-purple-600 hover:text-white border border-purple-600/20 rounded-2xl transition-all group">
                    <span class="font-bold">Github Sponsors</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-50 group-hover:opacity-100" viewBox="0 0 640 640" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M442.9 144C415.6 144 389.9 157.1 373.9 179.2L339.5 226.8C335 233 327.8 236.7 320.1 236.7C312.4 236.7 305.2 233 300.7 226.8L266.3 179.2C250.3 157.1 224.6 144 197.3 144C150.3 144 112.2 182.1 112.2 229.1C112.2 279 144.2 327.5 180.3 371.4C221.4 421.4 271.7 465.4 306.2 491.7C309.4 494.1 314.1 495.9 320.2 495.9C326.3 495.9 331 494.1 334.2 491.7C368.7 465.4 419 421.3 460.1 371.4C496.3 327.5 528.2 279 528.2 229.1C528.2 182.1 490.1 144 443.1 144zM335 151.1C360 116.5 400.2 96 442.9 96C516.4 96 576 155.6 576 229.1C576 297.7 533.1 358 496.9 401.9C452.8 455.5 399.6 502 363.1 529.8C350.8 539.2 335.6 543.9 320 543.9C304.4 543.9 289.2 539.2 276.9 529.8C240.4 502 187.2 455.5 143.1 402C106.9 358.1 64 297.7 64 229.1C64 155.6 123.6 96 197.1 96C239.8 96 280 116.5 305 151.1L320 171.8L335 151.1z"/></svg>
                </a>

                <div x-data="{ copied: false }" class="p-4 bg-gray-100 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-widest opacity-50">Bitcoin (BTC)</span>
                        <button @click="navigator.clipboard.writeText('bc1q4xg47x0vet8j7z5zpdqt85vvqt2dturtys7r04'); copied = true; setTimeout(() => copied = false, 2000)" class="text-xs text-blue-500 hover:underline">
                            <span x-show="!copied"><?= $lang['copy_adress'] ?></span>
                            <span x-show="copied" class="text-green-500 italic"><?= $lang['copy_success'] ?></span>
                        </button>
                    </div>
                    <code class="text-[10px] sm:text-xs break-all opacity-80">bc1q4xg47x0vet8j7z5zpdqt85vvqt2dturtys7r04</code>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="/" class="text-sm font-medium opacity-50 hover:opacity-100 transition-all flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    <?= $lang['back'] ?>
                </a>
            </div>
        </div>
        
        <footer class="text-center mt-8 mb-8">
            <p class="text-xs text-gray-500 dark:text-gray-400"><?= $lang['copy'] ?> <a href="https://github.com/RonDevHub/MailShield" class="bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-teal-400 font-semibold no-underline hover:underline decoration-teal-400" target="_blank">RonDevHub</a></p>
        </footer>
    </div>

</body>
</html>