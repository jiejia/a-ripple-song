#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

BUILD_DIR="${ROOT_DIR}/build"
RUNTIME_DIR="${BUILD_DIR}/runtime"
SCOPE_DIR="${BUILD_DIR}/scoped"

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

ARS_SCOPER_INPUT_DIR="${RUNTIME_DIR}" \
  php -d error_reporting=6143 "${ROOT_DIR}/vendor/bin/php-scoper" add-prefix \
  --config="${ROOT_DIR}/scoper.inc.php" \
  --output-dir="${SCOPE_DIR}" \
  -f

SCOPE_DIR="${SCOPE_DIR}" php <<'PHP'
<?php

$scopeDir = getenv('SCOPE_DIR');
$vendorDir = $scopeDir . '/vendor';
$prefix = '\\Jiejia\\ARippleSong\\Vendor\\';
$helpers = array_fill_keys([
    'abort',
    'abort_if',
    'abort_unless',
    'action',
    'app',
    'app_path',
    'append_config',
    'array_add',
    'array_collapse',
    'array_divide',
    'array_dot',
    'array_except',
    'array_first',
    'array_flatten',
    'array_forget',
    'array_get',
    'array_has',
    'array_last',
    'array_only',
    'array_pluck',
    'array_prepend',
    'array_pull',
    'array_random',
    'array_set',
    'array_sort',
    'array_sort_recursive',
    'array_where',
    'array_wrap',
    'asset',
    'auth',
    'back',
    'base_path',
    'bcrypt',
    'blank',
    'broadcast',
    'cache',
    'class_basename',
    'class_uses_recursive',
    'collect',
    'config',
    'config_path',
    'cookie',
    'csrf_field',
    'csrf_token',
    'data_fill',
    'data_get',
    'data_set',
    'database_path',
    'decrypt',
    'defer',
    'dispatch',
    'dispatch_sync',
    'e',
    'encrypt',
    'env',
    'event',
    'fake',
    'filled',
    'fluent',
    'head',
    'info',
    'last',
    'literal',
    'logger',
    'method_field',
    'now',
    'object_get',
    'old',
    'once',
    'optional',
    'policy',
    'public_path',
    'redirect',
    'report',
    'report_if',
    'report_unless',
    'request',
    'rescue',
    'resolve',
    'resource_path',
    'response',
    'retry',
    'route',
    'secure_asset',
    'secure_url',
    'session',
    'storage_path',
    'str',
    'tap',
    'throw_if',
    'throw_unless',
    'today',
    'trait_uses_recursive',
    'transform',
    'url',
    'validator',
    'value',
    'view',
    'windows_os',
    'with',
], true);

if (! is_dir($vendorDir)) {
    fwrite(STDERR, "Missing scoped vendor directory: {$vendorDir}\n");
    exit(1);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($vendorDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($files as $fileInfo) {
    if (! $fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }

    $path = $fileInfo->getPathname();
    $contents = file_get_contents($path);
    if ($contents === false) {
        continue;
    }

    $tokens = token_get_all($contents);
    $changed = false;
    $output = '';
    $count = count($tokens);

    for ($index = 0; $index < $count; $index++) {
        $token = $tokens[$index];

        if (! is_array($token) || $token[0] !== T_STRING || ! isset($helpers[$token[1]])) {
            $output .= is_array($token) ? $token[1] : $token;
            continue;
        }

        $previous = null;
        for ($previousIndex = $index - 1; $previousIndex >= 0; $previousIndex--) {
            $candidate = $tokens[$previousIndex];
            if (is_array($candidate) && in_array($candidate[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $previous = $candidate;
            break;
        }

        $next = null;
        for ($nextIndex = $index + 1; $nextIndex < $count; $nextIndex++) {
            $candidate = $tokens[$nextIndex];
            if (is_array($candidate) && $candidate[0] === T_WHITESPACE) {
                continue;
            }
            $next = $candidate;
            break;
        }

        $previousType = is_array($previous) ? $previous[0] : $previous;
        $isCall = $next === '(';
        $isDeclaration = $previousType === T_FUNCTION;
        $isMethodOrStaticCall = in_array($previousType, [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON], true);
        $isAlreadyQualified = $previousType === T_NS_SEPARATOR || $previousType === T_NAME_FULLY_QUALIFIED;

        if ($isCall && ! $isDeclaration && ! $isMethodOrStaticCall && ! $isAlreadyQualified) {
            $output .= $prefix . $token[1];
            $changed = true;
            continue;
        }

        $output .= $token[1];
    }

    if ($changed) {
        file_put_contents($path, $output);
    }
}
PHP

# Copy non-PHP theme files after scoping; Blade templates are not PHP-scoper input.
rsync -a \
  --include "*/" \
  --include "*.blade.php" \
  --include "*.css" \
  --include "*.js" \
  --include "*.json" \
  --include "*.mo" \
  --include "*.po" \
  --include "*.pot" \
  --include "*.png" \
  --include "*.jpg" \
  --include "*.jpeg" \
  --include "*.gif" \
  --include "*.svg" \
  --include "*.webp" \
  --include "*.woff" \
  --include "*.woff2" \
  --include "*.ttf" \
  --include "*.txt" \
  --include "LICENSE" \
  --include "LICENSE.md" \
  --include "README.md" \
  --exclude ".git/" \
  --exclude ".github/" \
  --exclude ".idea/" \
  --exclude "bin/" \
  --exclude "build/" \
  --exclude "node_modules/" \
  --exclude "vendor/" \
  --exclude "*.php" \
  --exclude "*" \
  "${RUNTIME_DIR}/" "${SCOPE_DIR}/"

cp "${RUNTIME_DIR}/composer.json" "${SCOPE_DIR}/composer.json"
cp "${RUNTIME_DIR}/composer.lock" "${SCOPE_DIR}/composer.lock"

# Carbon Fields' distribution assets are required by its WordPress admin UI.
CARBON_FIELDS_BUILD_SOURCE="${ROOT_DIR}/vendor/htmlburger/carbon-fields/build"
CARBON_FIELDS_BUILD_TARGET="${SCOPE_DIR}/vendor/htmlburger/carbon-fields/build"

if [[ ! -d "${CARBON_FIELDS_BUILD_SOURCE}" ]]; then
  echo "Missing Carbon Fields build assets in ${CARBON_FIELDS_BUILD_SOURCE}. Run composer install first."
  exit 1
fi

mkdir -p "${CARBON_FIELDS_BUILD_TARGET}"
rsync -a --delete "${CARBON_FIELDS_BUILD_SOURCE}/" "${CARBON_FIELDS_BUILD_TARGET}/"

# Carbon Fields JavaScript can carry hook names that PHP-Scoper cannot see.
if [[ -d "${SCOPE_DIR}/vendor/htmlburger/carbon-fields" ]]; then
  find "${SCOPE_DIR}/vendor/htmlburger/carbon-fields" -type f -name "*.js" -print0 \
    | xargs -0 perl -0pi -e 's/(?<!aripplesong_)carbon_fields_(?!core__)/aripplesong_carbon_fields_/g'
fi

# Restore vendor PHP templates that intentionally start with HTML before PHP.
while IFS= read -r -d '' template_file; do
  if perl -0777 -ne 's/^\s+//; exit(/^<\?php/ ? 1 : 0)' "${template_file}"; then
    relative_path="${template_file#"${RUNTIME_DIR}/"}"
    mkdir -p "$(dirname "${SCOPE_DIR}/${relative_path}")"
    cp "${template_file}" "${SCOPE_DIR}/${relative_path}"
  fi
done < <(find "${RUNTIME_DIR}/vendor" -type f -name "*.php" -print0)

if [[ ! -f "${CARBON_FIELDS_BUILD_TARGET}/classic/vendor.min.js" || ! -f "${CARBON_FIELDS_BUILD_TARGET}/classic/metaboxes.min.css" ]]; then
  echo "Missing required Carbon Fields admin assets in ${CARBON_FIELDS_BUILD_TARGET}."
  exit 1
fi

echo "Scoped package built at: ${SCOPE_DIR}"
