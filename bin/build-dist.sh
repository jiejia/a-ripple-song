#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

VERSION="${1:-}"
if [[ -z "${VERSION}" ]]; then
  if command -v git >/dev/null 2>&1 && git -C "${ROOT_DIR}" rev-parse --git-dir >/dev/null 2>&1; then
    VERSION="$(git -C "${ROOT_DIR}" describe --tags --always --dirty)"
  else
    VERSION="dev"
  fi
fi

BUILD_DIR="${ROOT_DIR}/build"
SCOPE_DIR="${BUILD_DIR}/scoped"
DIST_ROOT="${BUILD_DIR}/dist"
DIST_PLUGIN_DIR="${DIST_ROOT}/a-ripple-song"

rm -rf "${DIST_ROOT}"
mkdir -p "${DIST_PLUGIN_DIR}"

if [[ ! -f "${SCOPE_DIR}/autoload.php" ]]; then
  echo "Missing scoped vendor in ${SCOPE_DIR}. Run: composer run scoper:build"
  exit 1
fi

# Copy plugin sources excluding dev/build artifacts and unscoped vendor.
rsync -a --delete \
  --exclude "/.git/" \
  --exclude "/.github/" \
  --exclude "/.gitignore" \
  --exclude "/.idea/" \
  --exclude "/scoper.inc.php" \
  --exclude "/build/" \
  --exclude "/bin/" \
  --exclude "/node_modules/" \
  --exclude "/vendor/" \
  "${ROOT_DIR}/" "${DIST_PLUGIN_DIR}/"

# Add scoped vendor as the shipped vendor directory.
rsync -a --delete "${SCOPE_DIR}/" "${DIST_PLUGIN_DIR}/vendor/"

# Provide a tiny autoload bridge so prefixed classes can be resolved via the original Composer map.
cat > "${DIST_PLUGIN_DIR}/vendor/scoper-autoload.php" <<'PHP'
<?php
/**
 * Autoload bridge for PHP-Scoper prefixed vendor.
 *
 * Composer's autoloader still maps the original vendor namespaces (e.g. Carbon_Fields\\),
 * while the PHP files themselves have been prefixed (e.g. ARippleSong\\Podcast\\Vendor\\Carbon_Fields\\).
 *
 * This file registers an autoloader that maps the prefixed class back to its original
 * class name to locate the correct file, then lets the included file declare the
 * prefixed class.
 */

$loader = require __DIR__ . '/autoload.php';

$prefix = 'ARippleSong\\Podcast\\Vendor\\';
$prefix_len = strlen($prefix);

spl_autoload_register(
    static function ($class) use ($loader, $prefix, $prefix_len) {
        if (strncmp($class, $prefix, $prefix_len) !== 0) {
            return;
        }

        $unprefixed = substr($class, $prefix_len);
        if ($unprefixed === '') {
            return;
        }

        // Delegate file resolution to Composer using the original class name.
        $loader->loadClass($unprefixed);
    },
    true,
    true
);

return $loader;
PHP

ZIP_NAME="${ARS_DIST_ZIP_NAME:-${2:-}}"
if [[ -z "${ZIP_NAME}" ]]; then
  ZIP_NAME="a-ripple-song-${VERSION}.zip"
fi

ZIP_PATH="${BUILD_DIR}/${ZIP_NAME}"
rm -f "${BUILD_DIR}/a-ripple-song-podcast.zip"
rm -f "${ZIP_PATH}"

(cd "${DIST_ROOT}" && zip -qr "${ZIP_PATH}" "a-ripple-song")

echo "Built: ${ZIP_PATH}"
