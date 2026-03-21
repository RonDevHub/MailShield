<div class="bg-white dark:bg-darkcard p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
    <h2 class="text-xl font-semibold mb-6 text-center">Sicherheitsprüfung</h2>
    <p class="text-center text-gray-500 mb-6">Bitte löse das Captcha, um die E-Mail-Adresse anzuzeigen.</p>
    
    <form action="" method="POST" class="space-y-4">
        <div class="p-4 bg-gray-50 dark:bg-darkbg/50 rounded-xl border border-gray-100 dark:border-gray-700">
            <?php if ($captchaType === 'cloudflare'): ?>
                <div class="flex justify-center">
                    <div class="cf-turnstile" data-sitekey="<?= getenv('CF_SITE_KEY') ?: $config['cf_site_key'] ?>"></div>
                </div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <?php else: ?>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        <?= \App\Captcha::generateMathTask() ?>
                    </span>
                    <input type="number" name="captcha_input" required 
                           class="w-24 p-2 rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-darkbg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg transform active:scale-95 transition-all">
            E-Mail jetzt anzeigen
        </button>
    </form>
</div>