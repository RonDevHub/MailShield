<div class="bg-white dark:bg-darkcard p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
    <h2 class="text-xl font-semibold mb-6 text-center"><?= $lang['subtitle'] ?></h2>
    
    <form action="?action=protect" method="POST" class="space-y-4">
        <div class="relative">
            <input type="email" name="email" required 
                   placeholder="<?= $lang['input_placeholder'] ?>" 
                   class="w-full px-5 py-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-darkbg focus:ring-2 focus:ring-blue-500 outline-none transition-all">
        </div>
        
        <div class="hidden">
            <input type="text" name="hp_field" value="">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transform active:scale-95 transition-all">
            <?= $lang['btn_protect'] ?>
        </button>
    </form>

    <div class="mt-12 grid md:grid-cols-3 gap-8 text-sm text-gray-600 dark:text-gray-400">
        <div class="text-center">
            <div class="mb-2 text-blue-500 font-bold">1.</div>
            <p><?= $lang['step1'] ?></p>
        </div>
        <div class="text-center">
            <div class="mb-2 text-blue-500 font-bold">2.</div>
            <p><?= $lang['step2'] ?></p>
        </div>
        <div class="text-center">
            <div class="mb-2 text-blue-500 font-bold">3.</div>
            <p><?= $lang['step3'] ?></p>
        </div>
    </div>
</div>