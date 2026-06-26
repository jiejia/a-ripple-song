<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

$arsInputDir = getenv('ARS_SCOPER_INPUT_DIR') ?: __DIR__;
$arsThemePrefix = 'aripplesong';

$arsCarbonFieldsDir = $arsInputDir . '/vendor/htmlburger/carbon-fields';
$arsExcludedFiles = [];

$arsCarbonFieldsTemplatesDir = $arsCarbonFieldsDir . '/templates';
if (is_dir($arsCarbonFieldsTemplatesDir)) {
    // Carbon Fields templates can start with HTML before the first PHP tag.
    // Prefixing those files injects a namespace after output and breaks parsing.
    $arsExcludedFiles = array_map(
        static fn (SplFileInfo $fileInfo): string => $fileInfo->getPathname(),
        iterator_to_array(
            Finder::create()
                ->files()
                ->in($arsCarbonFieldsTemplatesDir)
                ->name('*.php'),
            false
        )
    );
}

return [
    'prefix' => 'Jiejia\\ARippleSong\\Vendor',

    'output-dir' => 'build/carbon-fields-scoped',

    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in($arsCarbonFieldsDir),
    ],

    'exclude-files' => [
        ...$arsExcludedFiles,
    ],

    'expose-global-classes' => false,
    'expose-global-functions' => false,
    'expose-global-constants' => false,

    'exclude-classes' => [
        // WordPress core classes must resolve from the global namespace.
        '~^Walker_~',
        '~^WP_~',
        'wpdb',
    ],

    'exclude-functions' => [
        // WordPress core functions must resolve from the global namespace.
        '__',
        '_e',
        'add_action',
        'add_filter',
        'apply_filters',
        'did_action',
        'doing_action',
        'content_url',
        'do_action',
        'esc_attr',
        'esc_html',
        'esc_url',
        'get_current_screen',
        'get_option',
        'is_admin',
        'is_feed',
        'plugins_url',
        'remove_action',
        'sanitize_key',
        'site_url',
        'trailingslashit',
        'untrailingslashit',
        'wp_enqueue_script',
        'wp_enqueue_style',
        'wp_json_encode',
        'wp_localize_script',
        'wp_strip_all_tags',
        'wp_unslash',
    ],

    'exclude-constants' => [
        // WordPress environment constants must resolve from the global namespace.
        'ABSPATH',
        '~^DOING_~',
        '~^WP_~',
        'SCRIPT_DEBUG',
    ],

    'patchers' => [
        static function (string $filePath, string $prefix, string $contents) use ($arsThemePrefix): string {
            return preg_replace(
                '/(?<!' . preg_quote($arsThemePrefix, '/') . '_)carbon_fields_(?!core__)/',
                $arsThemePrefix . '_carbon_fields_',
                $contents
            ) ?? $contents;
        },
    ],
];
