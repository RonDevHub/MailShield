<?php $fullLink = $config['base_url'] . "/v/" . $slug; ?>
<div class="bg-white dark:bg-darkcard p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
    <h2 class="text-2xl font-bold mb-8 text-center text-green-600 dark:text-green-400">Link generiert!</h2>
    
    <div class="space-y-6">
        <?php 
        $formats = [
            'Direct Link' => $fullLink,
            'HTML' => '<a href="'.$fullLink.'">Email anzeigen</a>',
            'Markdown' => '[Email anzeigen]('.$fullLink.')'
        ];
        foreach($formats as $label => $val): ?>
        <div class="relative">
            <label class="block text-xs font-bold uppercase tracking-wider mb-2 text-gray-500"><?= $label ?></label>
            <div class="flex">
                <input type="text" readonly value='<?= htmlspecialchars($val) ?>' 
                       id="input-<?= md5($label) ?>"
                       class="w-full bg-gray-50 dark:bg-darkbg border border-gray-200 dark:border-gray-700 p-3 rounded-l-lg text-sm font-mono">
                <button onclick="copyToClipboard('input-<?= md5($label) ?>')" 
                        class="bg-gray-200 dark:bg-gray-700 px-4 rounded-r-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    [FA-COPY]
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="/" class="text-blue-500 hover:underline">← Zurück</a>
    </div>
</div>

<script>
function copyToClipboard(id) {
    const copyText = document.getElementById(id);
    copyText.select();
    navigator.clipboard.writeText(copyText.value);
    alert("<?= $lang['copy_success'] ?>");
}
</script>