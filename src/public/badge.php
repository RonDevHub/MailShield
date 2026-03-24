<?php
// badge.php - Generiert das MailShield Badge
$logo_path = 'img/icon.png';
$logo_data = '';

if (file_exists($logo_path)) {
    $logo_data = base64_encode(file_get_contents($logo_path));
}

header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400'); // Cache für 24h
?>
<svg xmlns="http://www.w3.org/2000/svg" width="190" height="28" viewBox="0 0 190 28">
    <rect width="190" height="28" rx="6" fill="#1f2937"/>
    <rect x="105" width="85" height="28" rx="6" fill="#2dd4bf"/>
    <rect x="100" width="10" height="28" fill="#2dd4bf"/>
    
    <?php if ($logo_data): ?>
    <image x="8" y="4" width="20" height="20" href="data:image/png;base64,<?= $logo_data ?>"/>
    <?php endif; ?>
    
    <g fill="#fff" font-family="Verdana,Geneva,sans-serif" font-size="11" font-weight="bold">
        <text x="35" y="18" fill="#fff">MailShield</text>
        <text x="112" y="18" fill="#111827">Protect Email</text>
    </g>
</svg>