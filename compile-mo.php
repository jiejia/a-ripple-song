<?php
/**
 * Compile .po files to .mo files.
 *
 * Usage:
 *   php compile-mo.php
 *
 * Prefer WP-CLI `wp i18n make-mo` (correctly handles contexts/plurals/encoding).
 * Fallback to `msgfmt` if available.
 */

declare(strict_types=1);

function find_executable(string $name): ?string
{
    $cmd = 'command -v ' . escapeshellarg($name) . ' 2>/dev/null';
    $path = trim((string) shell_exec($cmd));
    return $path !== '' ? $path : null;
}

function get_wp_root_dir(): ?string
{
    // Theme dir: wordpress/wp-content/themes/aripplesong
    $candidate = realpath(__DIR__ . '/../../../');
    if (!$candidate || !is_dir($candidate)) {
        return null;
    }

    if (is_file($candidate . '/wp-load.php')) {
        return $candidate;
    }

    return null;
}

$lang_dir = __DIR__ . '/resources/lang';
if (!is_dir($lang_dir)) {
    fwrite(STDERR, "Error: lang dir not found: {$lang_dir}\n");
    exit(1);
}

$po_files = glob($lang_dir . '/*.po') ?: [];
echo 'Found ' . count($po_files) . " PO files.\n";

$wp = find_executable('wp');
if ($wp) {
    $wp_root = get_wp_root_dir();
    if ($wp_root) {
        $cmd = sprintf(
            '%s i18n make-mo %s --path=%s',
            escapeshellarg($wp),
            escapeshellarg($lang_dir),
            escapeshellarg($wp_root)
        );

        passthru($cmd, $code);
        if ($code === 0) {
            echo "Compilation complete (wp i18n make-mo).\n";
            exit(0);
        }

        fwrite(STDERR, "Warning: wp i18n make-mo failed (exit {$code}). Falling back...\n");
    } else {
        fwrite(STDERR, "Warning: WordPress root not found, skipping wp i18n make-mo...\n");
    }
}

$msgfmt = find_executable('msgfmt');
if (!$msgfmt) {
    fwrite(STDERR, "Error: neither `wp i18n make-mo` nor `msgfmt` is available.\n");
    exit(1);
}

foreach ($po_files as $po_file) {
    $mo_file = preg_replace('/\\.po$/', '.mo', $po_file) ?: ($po_file . '.mo');
    $cmd = sprintf(
        '%s -o %s %s',
        escapeshellarg($msgfmt),
        escapeshellarg($mo_file),
        escapeshellarg($po_file)
    );

    passthru($cmd, $code);
    if ($code !== 0) {
        fwrite(STDERR, "Failed to compile: {$po_file}\n");
        exit(1);
    }
}

echo "Compilation complete (msgfmt).\n";

