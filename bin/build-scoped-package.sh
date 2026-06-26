#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

BUILD_DIR="${ROOT_DIR}/build"
RUNTIME_DIR="${BUILD_DIR}/runtime"
SCOPE_DIR="${BUILD_DIR}/scoped"
CARBON_SCOPE_DIR="${BUILD_DIR}/carbon-fields-scoped"

rm -rf "${RUNTIME_DIR}" "${SCOPE_DIR}" "${CARBON_SCOPE_DIR}"
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

# Release packages use a theme-owned namespace while source stays Acorn-friendly.
find "${SCOPE_DIR}/app" "${SCOPE_DIR}/resources" -type f \( -name "*.php" -o -name "*.blade.php" \) -print0 \
  | xargs -0 perl -0pi \
    -e 's/namespace App([;\\])/namespace Jiejia\\ARippleSong$1/g;' \
    -e 's/use App\\/use Jiejia\\ARippleSong\\/g;' \
    -e 's/\\App\\/\\Jiejia\\ARippleSong\\/g;'

perl -0pi \
  -e 's/use App\\/use Jiejia\\ARippleSong\\/g;' \
  -e 's/\\App\\/\\Jiejia\\ARippleSong\\/g;' \
  "${SCOPE_DIR}/functions.php"

ARS_SCOPE_DIR="${SCOPE_DIR}" php -r '$path = getenv("ARS_SCOPE_DIR") . "/composer.json"; $composer = json_decode(file_get_contents($path), true); unset($composer["autoload"]["psr-4"]["App\\"]); $composer["autoload"]["psr-4"]["Jiejia\\ARippleSong\\"] = "app/"; file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);'

ARS_SCOPER_INPUT_DIR="${RUNTIME_DIR}" \
  php -d error_reporting=6143 "${ROOT_DIR}/vendor/bin/php-scoper" add-prefix \
  --config="${ROOT_DIR}/scoper.inc.php" \
  --output-dir="${CARBON_SCOPE_DIR}" \
  -f

SCOPED_CARBON_SOURCE="${CARBON_SCOPE_DIR}"
SCOPED_CARBON_TARGET="${SCOPE_DIR}/vendor/htmlburger/carbon-fields"

if [[ ! -d "${SCOPED_CARBON_SOURCE}" ]]; then
  echo "Missing scoped Carbon Fields package in ${SCOPED_CARBON_SOURCE}."
  exit 1
fi

rsync -a --delete "${SCOPED_CARBON_SOURCE}/" "${SCOPED_CARBON_TARGET}/"

# Carbon Fields templates are excluded from PHP-Scoper because some start with HTML.
# Copy them back and isolate their hook names without adding a namespace.
if [[ -d "${RUNTIME_DIR}/vendor/htmlburger/carbon-fields/templates" ]]; then
  rsync -a --delete \
    "${RUNTIME_DIR}/vendor/htmlburger/carbon-fields/templates/" \
    "${SCOPED_CARBON_TARGET}/templates/"
fi

# Carbon Fields build assets are required by the WordPress admin UI.
if [[ ! -d "${RUNTIME_DIR}/vendor/htmlburger/carbon-fields/build" ]]; then
  echo "Missing Carbon Fields build assets in ${RUNTIME_DIR}/vendor/htmlburger/carbon-fields/build."
  exit 1
fi

rsync -a --delete \
  "${RUNTIME_DIR}/vendor/htmlburger/carbon-fields/build/" \
  "${SCOPED_CARBON_TARGET}/build/"

# Update release theme code to reference only the scoped Carbon Fields package.
find "${SCOPE_DIR}/app" -type f -name "*.php" -print0 \
  | xargs -0 perl -0pi \
    -e 's/\\Carbon_Fields\\/\\Jiejia\\ARippleSong\\Vendor\\Carbon_Fields\\/g;' \
    -e 's/use Carbon_Fields\\/use Jiejia\\ARippleSong\\Vendor\\Carbon_Fields\\/g;' \
    -e "s/'carbon_fields_register_fields'/'aripplesong_carbon_fields_register_fields'/g;" \
    -e "s/'carbon_fields_post_meta_container_saved'/'aripplesong_carbon_fields_post_meta_container_saved'/g;" \
    -e "s/function_exists\\('carbon_get_theme_option'\\)/function_exists('Jiejia\\\\ARippleSong\\\\Vendor\\\\carbon_get_theme_option')/g;" \
    -e 's/carbon_get_theme_option\(/\\Jiejia\\ARippleSong\\Vendor\\carbon_get_theme_option(/g;'

# Carbon Fields JavaScript can carry hook names that PHP-Scoper cannot see.
find "${SCOPED_CARBON_TARGET}" -type f \( -name "*.php" -o -name "*.js" \) -print0 \
  | xargs -0 perl -0pi -e 's/(?<!aripplesong_)carbon_fields_(?!core__)/aripplesong_carbon_fields_/g'

ARS_SCOPE_DIR="${SCOPE_DIR}" php -r '$path = getenv("ARS_SCOPE_DIR") . "/composer.json"; $composer = json_decode(file_get_contents($path), true); $composer["autoload"]["psr-4"]["Jiejia\\ARippleSong\\Vendor\\Carbon_Fields\\"] = "vendor/htmlburger/carbon-fields/core/"; file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);'

if [[ ! -f "${SCOPED_CARBON_TARGET}/build/classic/vendor.min.js" || ! -f "${SCOPED_CARBON_TARGET}/build/classic/metaboxes.min.css" ]]; then
  echo "Missing required Carbon Fields admin assets in ${SCOPED_CARBON_TARGET}/build/classic."
  exit 1
fi

(cd "${SCOPE_DIR}" && composer dump-autoload --no-dev --classmap-authoritative --no-interaction --no-scripts)

echo "Scoped package built at: ${SCOPE_DIR}"
