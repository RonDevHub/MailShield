<div class="bg-white dark:bg-darkcard p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 text-center">
    <h2 class="text-xl font-semibold mb-4">Sicherheitsprüfung</h2>
    <p class="text-gray-500 mb-6">Bitte beweise, dass du kein Bot bist, um die E-Mail zu sehen.</p>
    
    <form action="" method="POST" class="space-y-6">
        <div class="p-4 bg-gray-50 dark:bg-darkbg/50 rounded-xl border border-gray-100 dark:border-gray-700">
            <?php if ($config['captcha_type'] === 'cloudflare'): ?>
                <div class="flex justify-center">
                    <div class="cf-turnstile" data-sitekey="<?= getenv('CF_SITE_KEY') ?: $config['cf_site_key'] ?>"></div>
                </div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <?php else: ?>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-lg font-mono font-bold text-blue-600">
                        <?= \App\Captcha::generateMathTask() ?>
                    </span>
                    <input type="number" name="captcha_input" required 
                           class="w-24 p-2 rounded-lg border border-gray-200 dark:bg-darkbg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-all">
            E-Mail anzeigen
        </button>
    </form>
</div>