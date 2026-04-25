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

cat > "$WP_ROOT/run-seed.php" << 'SEEDRUNNER'
<?php
header('Content-Type: text/plain; charset=utf-8');
$root = dirname(__FILE__);
$seedFile = "$root/wp-content/mu-plugins/mindhikers-cms-core/m1-seed.php";

if (!file_exists($seedFile)) {
    echo "Seed file not found at: $seedFile\n";
    echo "Checking alternative locations...\n";
    $alt = "$root/wp-content/plugins/m1-rest/m1-seed.php";
    if (file_exists($alt)) {
        $seedFile = $alt;
    } else {
        echo "No seed file found.\n";
        exit(1);
    }
}

echo "Running seed from: $seedFile\n\n";
require_once $seedFile;
echo "\nSeed execution completed.\n";

// Self-destruct for security
unlink(__FILE__);
echo "Cleaned up runner.\n";
SEEDRUNNER
chown www-data:www-data "$WP_ROOT/run-seed.php" || true
