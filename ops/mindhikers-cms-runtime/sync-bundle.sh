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

cat > "$WP_ROOT/debug-probe.php" << 'PROBE'
<?php
header('Content-Type: text/plain; charset=utf-8');
$root = dirname(__FILE__);
echo "=== WordPress Content Diagnostic ===\n\n";
echo "Themes:\n";
foreach (glob("$root/wp-content/themes/*") as $t) { echo "  " . basename($t) . "\n"; }
echo "\nMu-plugins:\n";
foreach (glob("$root/wp-content/mu-plugins/*") as $m) { echo "  " . basename($m) . "\n"; }
echo "\nPlugins:\n";
foreach (glob("$root/wp-content/plugins/*") as $p) { echo "  " . basename($p) . "\n"; }
echo "\nAstra parent style.css exists: " . (file_exists("$root/wp-content/themes/astra/style.css") ? "YES" : "NO") . "\n";
echo "Astra child style.css exists: " . (file_exists("$root/wp-content/themes/astra-child/style.css") ? "YES" : "NO") . "\n";
PROBE
chown www-data:www-data "$WP_ROOT/debug-probe.php" || true
