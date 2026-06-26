#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

BUILD_DIR="${ROOT_DIR}/build"
RUNTIME_DIR="${BUILD_DIR}/runtime"
SCOPE_DIR="${BUILD_DIR}/scoped"
CARBON_FIELDS_DIR="${SCOPE_DIR}/vendor/htmlburger/carbon-fields"

rm -rf "${RUNTIME_DIR}" "${SCOPE_DIR}"
mkdir -p "${RUNTIME_DIR}"

# Copy release inputs into an isolated runtime so local artifacts cannot leak.
rsync -a --delete \
  --exclude "/.git/" \
  --exclude "/.github/" \
  --exclude "/.gitignore" \
  --exclude "/.idea/" \
  --exclude "/bin/" \
  --exclude "/build/" \
  --exclude "/dist/" \
  --exclude "/node_modules/" \
  --exclude "/vendor/" \
  "${ROOT_DIR}/" "${RUNTIME_DIR}/"

(cd "${RUNTIME_DIR}" && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts)

rsync -a --delete "${RUNTIME_DIR}/" "${SCOPE_DIR}/"

if [[ ! -d "${CARBON_FIELDS_DIR}" ]]; then
  echo "Missing Carbon Fields package in ${CARBON_FIELDS_DIR}."
  exit 1
fi

# Isolate Carbon Fields hooks and related runtime identifiers in the release package only.
# Theme bridge hooks in app/ must stay unchanged to avoid recursive do_action() calls.
find "${CARBON_FIELDS_DIR}" -type f \( -name "*.php" -o -name "*.js" \) -print0 \
  | xargs -0 perl -0pi -e 's/(?<!aripplesong_)carbon_fields_(?!core__)/aripplesong_carbon_fields_/g'

if [[ ! -f "${CARBON_FIELDS_DIR}/build/classic/vendor.min.js" || ! -f "${CARBON_FIELDS_DIR}/build/classic/metaboxes.min.css" ]]; then
  echo "Missing required Carbon Fields admin assets in ${CARBON_FIELDS_DIR}/build/classic."
  exit 1
fi

(cd "${SCOPE_DIR}" && composer dump-autoload --no-dev --classmap-authoritative --no-interaction --no-scripts)

echo "Release package staged at: ${SCOPE_DIR}"
