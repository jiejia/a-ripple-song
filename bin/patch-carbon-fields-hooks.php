<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

/**
 * Patch Carbon Fields WordPress hook names to avoid collisions.
 *
 * Carbon Fields uses a global `carbon_fields_*` hook namespace. When multiple Carbon Fields
 * copies exist (e.g. a plugin ships a scoped Carbon Fields), those hooks can collide and
 * trigger fatal type errors.
 *
 * This script updates Carbon Fields vendor files so any WordPress hook name starting with
 * `carbon_fields_` is passed through `\aripplesong_cf_hook()`.
 *
 * The helper is defined by the theme (see functions.php) and prefixes hooks with a stable
 * theme-specific string.
 */

$root = dirname(__DIR__);
$carbonFieldsDir = $argv[1] ?? ($root . '/vendor/htmlburger/carbon-fields');

$autoload = $root . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

if (!is_dir($carbonFieldsDir)) {
    echo "Carbon Fields not found at: {$carbonFieldsDir}. Skipping.\n";
    exit(0);
}

$filesystem = class_exists(Filesystem::class) ? new Filesystem() : null;

$hookFunctions = [
    'add_action',
    'add_filter',
    'remove_action',
    'remove_filter',
    'has_action',
    'has_filter',
    'did_action',
    'doing_action',
    'do_action',
    'do_action_ref_array',
    'apply_filters',
    'apply_filters_ref_array',
];

$hookFunctionsPattern = implode('|', array_map(static fn (string $fn): string => preg_quote($fn, '/'), $hookFunctions));

$patterns = [
    // Wrap the first argument of WP hook functions when it starts with `carbon_fields_`.
    // Works for both plain strings and concatenations like:
    //   apply_filters('carbon_fields_' . $type . '_...', ...)
    [
        'regex' => '/(?<!->)(?<!::)(\\\\\\\\)?(' . $hookFunctionsPattern . ')\\s*\\(\\s*([\'"]carbon_fields_[^\'"]*[\'"])/m',
        'replace' => '$1$2(\\aripplesong_cf_hook($3)',
    ],
    // Wrap hook-name variables commonly used inside Carbon Fields (e.g. $register_action, $filter_name, $hook).
    [
        'regex' => '/(\\$[A-Za-z0-9_]*(?:action|filter|hook|name)[A-Za-z0-9_]*\\s*=\\s*)([\'"]carbon_fields_[^\'"]*[\'"])/i',
        'replace' => '$1\\aripplesong_cf_hook($2)',
    ],
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($carbonFieldsDir, FilesystemIterator::SKIP_DOTS)
);

$changedFiles = 0;
$totalReplacements = 0;

/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $contents = file_get_contents($path);
    if ($contents === false) {
        echo "Failed to read: {$path}\n";
        exit(1);
    }

    $original = $contents;
    $fileReplacements = 0;

    foreach ($patterns as $p) {
        $count = 0;
        $contents = preg_replace($p['regex'], $p['replace'], $contents, -1, $count);
        if ($contents === null) {
            echo "Regex error while processing: {$path}\n";
            exit(1);
        }
        $fileReplacements += $count;
    }

    if ($contents !== $original) {
        try {
            if ($filesystem) {
                $filesystem->dumpFile($path, $contents);
            } else {
                throw new RuntimeException('Symfony Filesystem is not available.');
            }
        } catch (Throwable $e) {
            echo "Failed to write: {$path}\n";
            exit(1);
        }
        $changedFiles++;
        $totalReplacements += $fileReplacements;
    }
}

echo "Patched Carbon Fields hooks. Files changed: {$changedFiles}, replacements: {$totalReplacements}.\n";
