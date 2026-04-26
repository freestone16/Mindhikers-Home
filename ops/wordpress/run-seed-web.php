<?php
$expectedSecret = getenv('MINDHIKERS_SEED_SECRET') ?: 'mindhikers-staging-seed-2026';
$providedSecret = $_GET['secret'] ?? '';

if ($providedSecret !== $expectedSecret) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden: invalid or missing secret.\n";
    exit;
}

set_time_limit(120);

require_once '/var/www/html/wp-load.php';
require_once '/var/www/html/wp-content/plugins/carbon-fields/carbon-fields-plugin.php';

\Carbon_Fields\Carbon_Fields::boot();

header('Content-Type: text/plain; charset=utf-8');
echo "=== M1 Seed Web Runner ===\n";
echo "Starting seed at " . date('Y-m-d H:i:s') . "\n\n";

ob_start();
include '/opt/wp-bundle/seed/m1-seed.php';
$output = ob_get_clean();

echo $output;

echo "\n=== Seed completed at " . date('Y-m-d H:i:s') . " ===\n";
echo "\nVerification:\n";

$checks = [
    'hero_title_zh',
    'product_title_zh',
    'product_desc_zh',
    'blog_title_zh',
    'blog_desc_zh',
    'contact_title_zh',
    'contact_desc_zh',
];

foreach ($checks as $field) {
    $value = carbon_get_theme_option($field);
    $status = $value ? '✅' : '❌';
    echo "{$status} {$field}: " . ($value ? substr((string)$value, 0, 50) : 'EMPTY') . "\n";
}

