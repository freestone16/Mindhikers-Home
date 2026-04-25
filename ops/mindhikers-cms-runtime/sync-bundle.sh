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

cat > "$WP_ROOT/check-rest.php" << 'CFDIAG'
<?php
header('Content-Type: text/plain; charset=utf-8');
$restFile = '/var/www/html/wp-content/plugins/m1-rest/homepage.php';
echo "=== m1-rest/homepage.php ===\n";
if (file_exists($restFile)) {
    echo file_get_contents($restFile);
} else {
    echo "File not found: $restFile\n";
    echo "Checking plugins dir:\n";
    foreach (glob('/var/www/html/wp-content/plugins/*') as $p) {
        echo "  " . basename($p) . "\n";
    }
}
unlink(__FILE__);
echo "\nCleaned up.\n";
CFDIAG
chown www-data:www-data "$WP_ROOT/check-rest.php" || true

