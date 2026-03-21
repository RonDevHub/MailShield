<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MailShield - PHP Email Protection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { darkbg: '#121212', darkcard: '#1e1e1e' } } }
        }
    </script>
    <style>
        [v-cloak] { display: none; }
        .font-awesome-placeholder { font-family: sans-serif; font-weight: bold; }
    </style>
</head>
<body class="h-full bg-gray-50 text-gray-900 dark:bg-darkbg dark:text-gray-100 transition-colors duration-300">
    
    <div class="max-w-4xl mx-auto px-4 py-12">
        <header class="flex justify-between items-center mb-12">
            <h1 class="text-3xl font-bold tracking-tight">MailShield</h1>
            <div class="flex gap-4 items-center">
                <select onchange="window.location.href='?setlang='+this.value" class="bg-transparent border border-gray-300 dark:border-gray-700 rounded px-2 py-1 text-sm">
                    <option value="de" <?= $_SESSION['lang'] == 'de' ? 'selected' : '' ?>>DE</option>
                    <option value="en" <?= $_SESSION['lang'] == 'en' ? 'selected' : '' ?>>EN</option>
                </select>
                <button onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-800">
                    <span class="font-awesome-placeholder">[ID: FA-MOON/SUN]</span>
                </button>
            </div>
        </header>

        <main>
            <?php include __DIR__ . '/home.php'; ?>
        </main>

        <footer class="mt-24 pt-8 border-t border-gray-200 dark:border-gray-800 text-center text-sm text-gray-500">
            <div class="mb-4">
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400 counter" data-target="1337">0</span>
                <p>E-Mails erfolgreich geschützt</p>
            </div>
            <p>Online seit: <?= $config['online_since'] ?> | <a href="#" class="hover:underline">Support me</a></p>
        </footer>
    </div>

    <script>
        // Dark Mode Initialization
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        
        // Animated Counter
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const speed = 200; 
                const inc = target / speed;
                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 10);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    </script>
</body>
</html>