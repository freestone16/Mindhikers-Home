#!/bin/bash
set -e

BUNDLE=/opt/wp-bundle
WP_ROOT=/var/www/html
TARGET="$WP_ROOT/wp-content"

if [ ! -f "$WP_ROOT/index.php" ]; then
  echo "[mh-sync-bundle] installing WordPress core..."
  cp -a /usr/src/wordpress/. "$WP_ROOT/"
fi

echo "[mh-sync-bundle] syncing mu-plugins..."
mkdir -p "$TARGET/mu-plugins"
cp -rf "$BUNDLE/mu-plugins/." "$TARGET/mu-plugins/"
if [ -f "$TARGET/mu-plugins/m1-seed.php" ]; then
  echo "[mh-sync-bundle] removing m1-seed.php from mu-plugins (it is a CLI script, not a plugin)"
  rm -f "$TARGET/mu-plugins/m1-seed.php"
fi

echo "[mh-sync-bundle] syncing themes..."
mkdir -p "$TARGET/themes"
cp -rf "$BUNDLE/themes/." "$TARGET/themes/"

echo "[mh-sync-bundle] syncing bundled plugins..."
mkdir -p "$TARGET/plugins"
for plugin in "$BUNDLE/plugins/"*; do
  name=$(basename "$plugin")
  echo "  - $name"
  rm -rf "$TARGET/plugins/$name"
  cp -rf "$plugin" "$TARGET/plugins/$name"
done

echo "[mh-sync-bundle] setting ownership..."
chown -R www-data:www-data "$TARGET/mu-plugins" "$TARGET/themes" \
  "$TARGET/plugins/carbon-fields" "$TARGET/plugins/polylang"

echo "[mh-sync-bundle] done."

echo "[mh-sync-bundle] themes dir listing:"
ls -la "$TARGET/themes/" || true

cat > "$WP_ROOT/check-cf.php" << 'CFDIAG'
<?php
header('Content-Type: text/plain; charset=utf-8');
require_once '/var/www/html/wp-load.php';

$fields = [
  'hero_title_zh', 'hero_desc_zh', 'hero_eyebrow_zh',
  'about_title_zh', 'about_content_zh',
  'contact_email', 'contact_desc_zh',
  'product_title_zh', 'product_desc_zh',
  'blog_title_zh', 'blog_desc_zh'
];

echo "=== Carbon Fields Direct Read ===\n\n";
foreach ($fields as $f) {
  $val = carbon_get_theme_option($f);
  echo "$f: " . (is_array($val) ? json_encode($val) : $val) . "\n";
}

echo "\n=== WP Options Table ===\n";
global $wpdb;
$rows = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE '%carbon%' LIMIT 20");
foreach ($rows as $r) {
  echo $r->option_name . ": " . substr($r->option_value, 0, 100) . "\n";
}

unlink(__FILE__);
echo "\nCleaned up.\n";
CFDIAG
chown www-data:www-data "$WP_ROOT/check-cf.php" || true

