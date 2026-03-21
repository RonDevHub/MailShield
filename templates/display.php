<div class="bg-white dark:bg-darkcard p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 text-center">
    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-6">Die geschützte Adresse lautet:</h3>
    <div class="p-6 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-900 mb-8">
        <span class="text-2xl font-mono font-bold text-green-700 dark:text-green-400 break-all" id="mail-output"><?= htmlspecialchars($displayEmail) ?></span>
    </div>
    <button onclick="copyToClipboard()" class="flex items-center justify-center gap-2 mx-auto bg-gray-800 dark:bg-gray-700 text-white px-8 py-3 rounded-lg hover:scale-105 transition-all">
        [FA-COPY] Kopieren
    </button>
    <script>
    function copyToClipboard() {
        navigator.clipboard.writeText(document.getElementById('mail-output').innerText);
        alert('Kopiert!');
    }
    </script>
</div>