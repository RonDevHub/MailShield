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
                    <span class="font-awesome-placeholder"><svg xmlns="http://www.w3.org/2000/svg" height="26" viewBox="0 0 576 512"><path fill="currentColor" d="M200.6-7.9c-6.7-4.4-15.1-5.2-22.5-2.2S165.4-.5 163.9 7.3L143 110.6 39.7 131.4c-7.8 1.6-14.4 7-17.4 14.3s-2.2 15.8 2.2 22.5L82.7 256 24.5 343.8c-4.4 6.7-5.2 15.1-2.2 22.5s9.6 12.8 17.4 14.3L143 401.4 163.9 504.7c1.6 7.8 7 14.4 14.3 17.4s15.8 2.2 22.5-2.2l87.8-58.2 87.8 58.2c6.7 4.4 15.1 5.2 22.5 2.2s12.8-9.6 14.3-17.4l20.9-103.2 103.2-20.9c7.8-1.6 14.4-7 17.4-14.3s2.2-15.8-2.2-22.5l-58.2-87.8 58.2-87.8c4.4-6.7 5.2-15.1 2.2-22.5s-9.6-12.8-17.4-14.3L433.8 110.6 413 7.3C411.4-.5 406-7 398.6-10.1s-15.8-2.2-22.5 2.2L288.4 50.3 200.6-7.9zM186.9 135.7l17-83.9 71.3 47.3c8 5.3 18.5 5.3 26.5 0l71.3-47.3 17 83.9c1.9 9.5 9.3 16.8 18.8 18.8l83.9 17-47.3 71.3c-5.3 8-5.3 18.5 0 26.5l47.3 71.3-83.9 17c-9.5 1.9-16.9 9.3-18.8 18.8l-17 83.9-71.3-47.3c-8-5.3-18.5-5.3-26.5 0l-71.3 47.3-17-83.9c-1.9-9.5-9.3-16.9-18.8-18.8l-83.9-17 47.3-71.3c5.3-8 5.3-18.5 0-26.5l-47.3-71.3 83.9-17c9.5-1.9 16.8-9.3 18.8-18.8zM192.4 256c0 53 43 96 96 96 27.6 0 52.4-11.6 69.9-30.2 4-4.3 5.4-10.4 3.5-16s-6.6-9.7-12.5-10.7c-30.1-5.2-53-31.5-53-63.1 0-17 6.6-32.4 17.4-43.9 4-4.3 5.4-10.4 3.5-16s-6.6-9.7-12.4-10.7c-5.4-.9-10.9-1.4-16.5-1.4-53 0-96 43-96 96z"/></svg></span>
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