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

SEED_SCRIPT="$BUNDLE/seed/m1-seed.php"
SEED_HASH_FLAG="$WP_ROOT/.m1-seed-hash"
CURRENT_HASH=""

if [ -f "$SEED_SCRIPT" ]; then
  CURRENT_HASH=$(md5sum "$SEED_SCRIPT" | awk '{print $1}')
fi

if [ -f "$SEED_HASH_FLAG" ]; then
  STORED_HASH=$(cat "$SEED_HASH_FLAG")
else
  STORED_HASH=""
fi

if [ "$CURRENT_HASH" != "" ] && [ "$CURRENT_HASH" != "$STORED_HASH" ]; then
  echo "[mh-sync-bundle] executing m1-seed.php (hash changed or first run)..."
  cd "$WP_ROOT"
  php "$SEED_SCRIPT" || echo "[mh-sync-bundle] seed failed, continuing..."
  echo "$CURRENT_HASH" > "$SEED_HASH_FLAG"
  echo "[mh-sync-bundle] seed completed."
fi

