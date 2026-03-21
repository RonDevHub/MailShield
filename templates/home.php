<?php
/**
 * MailShield PHP - Home Template (An dein Design angepasst)
 */
$captchaType = getenv('CAPTCHA_TYPE') ?: $config['captcha_type'];
?>

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

        <div class="p-4 bg-gray-50 dark:bg-darkbg/50 rounded-xl border border-gray-100 dark:border-gray-700">
            <?php if ($captchaType === 'cloudflare'): ?>
                <div class="flex justify-center">
                    <div class="cf-turnstile" data-sitekey="<?= getenv('CF_SITE_KEY') ?: $config['cf_site_key'] ?>" data-theme="auto" data-compact="true"></div>
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