<?php

declare(strict_types=1);

/** @var Symfony\Component\Finder\Finder $finder */
$finder = Symfony\Component\Finder\Finder::class;

// Allow overriding the input vendor directory (useful for CI/release builds where
// dev dependencies should not be included in the shipped vendor).
$inputVendorDir = getenv('ARS_SCOPER_INPUT_DIR') ?: (__DIR__ . '/vendor');

$excludedFiles = [];

// Keep Composer's autoloader files unscoped so we can bootstrap a working loader,
// then map prefixed classes back to their original names for file resolution.
$excludedFiles[] = $inputVendorDir . '/autoload.php';
$excludedFiles   = array_merge(
    $excludedFiles,
    array_map(
        static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
        iterator_to_array(
            $finder::create()
                ->files()
                ->in($inputVendorDir . '/composer')
                ->name('*.php'),
            false
        )
    )
);

// Carbon Fields template files start with HTML and embed PHP blocks. Prefixing them
// will inject a namespace declaration at the first `<?php` tag which breaks parsing.
$carbonFieldsTemplatesDir = $inputVendorDir . '/htmlburger/carbon-fields/templates';
if (is_dir($carbonFieldsTemplatesDir)) {
    $excludedFiles = array_merge(
        $excludedFiles,
        array_map(
            static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
            iterator_to_array(
                $finder::create()
                    ->files()
                    ->in($carbonFieldsTemplatesDir)
                    ->name('*.php'),
                false
            )
        )
    );
}

return [
    // Prefix all bundled vendor dependencies under a dedicated namespace so they never collide
    // with other Composer-based plugins/themes.
    'prefix' => 'A_Ripple_Song\\Vendor',

    // The base output directory for the prefixed files.
    'output-dir' =>  __DIR__ . '/build/scoped',

    'finders' => [
        // Scope the entire vendor tree, including non-PHP assets (Carbon Fields admin UI).
        $finder::create()
            ->files()
            ->in($inputVendorDir)
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->exclude([
                'bin',
                'doc',
                'docs',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ]),

        // Scope theme PHP sources so vendor references are rewritten to the prefixed namespace.
        $finder::create()
            ->files()
            ->in(__DIR__ . '/app')
            ->name('*.php'),

        $finder::create()
            ->files()
            ->in(__DIR__)
            ->depth('== 0')
            ->name(['functions.php', 'index.php', 'searchform.php']),
    ],

    'exclude-files' => [
        ...$excludedFiles,
    ],

    'exclude-namespaces' => [
        // Keep theme code in its original namespaces.
        'App\\',

        // Keep theme global functions/classes in the global namespace (widgets, helpers).
        '~^$~',
    ],

    'exclude-classes' => [
        // WordPress core classes should never be prefixed.
        '~^WP_~',
        '~^Walker_~',
    ],

    'exclude-functions' => [
        // Theme helpers referenced from vendor patches.
        'aripplesong_cf_hook',
        '~^aripplesong_~',

        // WordPress core functions should never be prefixed (esp. in function_exists strings).
        'content_url',
        'count_many_users_posts',
        'get_blog_status',
        'get_current_screen',
        'is_multisite',
        'plugins_url',
        'post_type_exists',
        'register_block_pattern',
        'register_block_style',
        'site_url',
        'taxonomy_exists',
        'trailingslashit',
        'untrailingslashit',
    ],

    'exclude-constants' => [
        // WordPress environment constants.
        'ABSPATH',
        '~^DOING_~',
        '~^WP_~',
        'SCRIPT_DEBUG',
        'SITE_ID_CURRENT_SITE',

        // Theme constants.
        'ARIPPLESONG_CARBON_FIELDS_HOOK_PREFIX',
    ],

    // Keep vendor isolated.
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,
    'expose-namespaces' => [],
    'expose-classes' => [],
    'expose-functions' => [],
    'expose-constants' => [],
];
