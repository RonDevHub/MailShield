<div class="bg-white dark:bg-darkcard p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 text-center">
    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4">Geschützte E-Mail Adresse:</h2>
    
    <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-xl border border-blue-100 dark:border-blue-800 mb-8">
        <span class="text-2xl font-mono font-bold text-blue-700 dark:text-blue-300 break-all" id="email-text">
            <?= htmlspecialchars($displayEmail) ?>
        </span>
    </div>

    <button onclick="copyEmail()" class="bg-gray-800 dark:bg-gray-700 text-white px-6 py-3 rounded-lg hover:bg-black transition-all flex items-center justify-center gap-2 mx-auto">
        <span class="font-awesome-placeholder">[FA-COPY]</span>
        Kopieren
    </button>

    <div class="mt-8">
        <a href="/" class="text-gray-400 hover:text-blue-500 text-sm">Neuen Link erstellen</a>
    </div>
</div>

<script>
function copyEmail() {
    const email = document.getElementById('email-text').innerText;
    navigator.clipboard.writeText(email);
    alert("E-Mail kopiert!");
}
</script>