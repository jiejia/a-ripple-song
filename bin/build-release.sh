#!/usr/bin/env bash

set -euo pipefail

THEME_SLUG="${1:-a-ripple-song}"
ARCHIVE_NAME="${2:-${THEME_SLUG}.zip}"

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORKDIR="${ROOT_DIR}/build/scoper-workdir"
SCOPED_DIR="${WORKDIR}/build/scoped"
DIST_DIR="${ROOT_DIR}/dist/${THEME_SLUG}"

if [[ ! -d "${ROOT_DIR}/public/build" ]]; then
  echo "Missing public/build. Run \`npm run build\` first." >&2
  exit 1
fi

if [[ ! -f "${ROOT_DIR}/vendor/bin/php-scoper" ]]; then
  echo "Missing php-scoper. Run \`composer install\` (with dev dependencies) first." >&2
  exit 1
fi

rm -rf "${WORKDIR}"
mkdir -p "${WORKDIR}"

rsync -a --prune-empty-dirs \
  --include="app/***" \
  --include="functions.php" \
  --include="index.php" \
  --include="searchform.php" \
  --include="composer.json" \
  --include="composer.lock" \
  --include="scoper.inc.php" \
  --exclude="*" \
  "${ROOT_DIR}/" "${WORKDIR}/"

(
  cd "${WORKDIR}"
  composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress --no-scripts
)

php "${ROOT_DIR}/bin/patch-carbon-fields-hooks.php" "${WORKDIR}/vendor/htmlburger/carbon-fields"

(
  cd "${WORKDIR}"
  # PHP 8.5+ triggers new deprecations in PHP-Scoper's internals; silence deprecations so builds remain deterministic.
  php -d error_reporting=8191 "${ROOT_DIR}/vendor/bin/php-scoper" add-prefix --config scoper.inc.php
)

rm -rf "${ROOT_DIR}/dist"
mkdir -p "${DIST_DIR}"

rsync -a --prune-empty-dirs \
  --include="data/***" \
  --include="public/***" \
  --exclude="public/hot" \
  --include="resources/" \
  --include="resources/views/***" \
  --include="resources/lang/***" \
  --include="resources/images/***" \
  --include="resources/fonts/***" \
  --include="composer.json" \
  --include="composer.lock" \
  --include="readme.txt" \
  --include="LICENSE" \
  --include="editor-style.css" \
  --include="style.css" \
  --include="screenshot.png" \
  --include="theme.json" \
  --include="docs/***" \
  --exclude="app/***" \
  --exclude="vendor/***" \
  --exclude="functions.php" \
  --exclude="index.php" \
  --exclude="searchform.php" \
  --exclude="*" \
  "${ROOT_DIR}/" "${DIST_DIR}/"

rsync -a "${SCOPED_DIR}/app/" "${DIST_DIR}/app/"
cp "${SCOPED_DIR}/functions.php" "${DIST_DIR}/functions.php"
cp "${SCOPED_DIR}/index.php" "${DIST_DIR}/index.php"
cp "${SCOPED_DIR}/searchform.php" "${DIST_DIR}/searchform.php"
rsync -a "${SCOPED_DIR}/vendor/" "${DIST_DIR}/vendor/"

cp "${ROOT_DIR}/bin/scoper-autoload.php" "${DIST_DIR}/vendor/scoper-autoload.php"

find "${DIST_DIR}" -name ".DS_Store" -delete

rm -f "${ROOT_DIR}/${ARCHIVE_NAME}"
(cd "${ROOT_DIR}/dist" && zip -r "${ROOT_DIR}/${ARCHIVE_NAME}" "${THEME_SLUG}" -x "**/.DS_Store")

echo "Built ${ARCHIVE_NAME}"
