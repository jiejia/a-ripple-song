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
DIST_THEME_DIR="${DIST_ROOT}/a-ripple-song"

if [[ ! -f "${SCOPE_DIR}/functions.php" || ! -f "${SCOPE_DIR}/vendor/autoload.php" ]]; then
  echo "Missing scoped package in ${SCOPE_DIR}. Run: composer run scoper:build"
  exit 1
fi

rm -rf "${DIST_ROOT}"
mkdir -p "${DIST_THEME_DIR}"

rsync -a --delete \
  --exclude "composer.lock" \
  "${SCOPE_DIR}/" "${DIST_THEME_DIR}/"

if [[ ! -f "${DIST_THEME_DIR}/vendor/htmlburger/carbon-fields/build/classic/vendor.min.js" || ! -f "${DIST_THEME_DIR}/vendor/htmlburger/carbon-fields/build/classic/metaboxes.min.css" ]]; then
  echo "Missing required Carbon Fields admin assets in ${DIST_THEME_DIR}/vendor/htmlburger/carbon-fields/build."
  exit 1
fi

ZIP_NAME="${ARS_DIST_ZIP_NAME:-${2:-}}"
if [[ -z "${ZIP_NAME}" ]]; then
  ZIP_NAME="a-ripple-song-${VERSION}.zip"
fi

ZIP_PATH="${BUILD_DIR}/${ZIP_NAME}"
rm -f "${ZIP_PATH}"

(cd "${DIST_ROOT}" && zip -qr "${ZIP_PATH}" "a-ripple-song")

echo "Built: ${ZIP_PATH}"
