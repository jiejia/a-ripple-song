<?php

declare(strict_types=1);

// Prevent direct access via the web. This file is intended for CLI usage (PHP-Scoper).
if ( ! defined( 'ABSPATH' ) && PHP_SAPI !== 'cli' ) {
	exit;
}

/** @var Symfony\Component\Finder\Finder $ars_finder */
$ars_finder = Isolated\Symfony\Component\Finder\Finder::class;

// Allow overriding the input vendor directory (useful for CI/release builds where
// dev dependencies should not be included in the shipped vendor).
$ars_input_vendor_dir = getenv('ARS_SCOPER_INPUT_DIR') ?: (__DIR__ . '/vendor');

// You can do your own things here, e.g. collecting symbols to expose dynamically
// or files to exclude.
// However beware that this file is executed by PHP-Scoper, hence if you are using
// the PHAR it will be loaded by the PHAR. So it is highly recommended to avoid
// to auto-load any code here: it can result in a conflict or even corrupt
// the PHP-Scoper analysis.

// Example of collecting files to include in the scoped build but to not scope
// leveraging the isolated finder.
// $excludedFiles = array_map(
//     static fn (SplFileInfo $fileInfo) => $fileInfo->getPathName(),
//     iterator_to_array(
//         $finder::create()->files()->in(__DIR__),
//         false,
//     ),
// );
$ars_excluded_files = [];

// Keep Composer's autoloader files unscoped so we can bootstrap a working loader,
// then map prefixed classes back to their original names for file resolution.
$ars_excluded_files[] = $ars_input_vendor_dir . '/autoload.php';
$ars_excluded_files   = array_merge(
	$ars_excluded_files,
	array_map(
		static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
		iterator_to_array(
			$ars_finder::create()
				->files()
				->in($ars_input_vendor_dir . '/composer')
				->name('*.php'),
			false
		)
	)
);

// Carbon Fields template files start with HTML and embed PHP blocks. Prefixing them
// will inject a namespace declaration at the first `<?php` tag which breaks parsing.
$ars_carbon_fields_templates_dir = $ars_input_vendor_dir . '/htmlburger/carbon-fields/templates';
if (is_dir($ars_carbon_fields_templates_dir)) {
	$ars_excluded_files = array_merge(
		$ars_excluded_files,
		array_map(
			static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
			iterator_to_array(
				$ars_finder::create()
					->files()
					->in($ars_carbon_fields_templates_dir)
					->name('*.php'),
				false
			)
		)
	);
}

return [
    // The prefix configuration. If a non-null value is used, a random prefix
    // will be generated instead.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#prefix
    // Prefix all bundled vendor dependencies under a dedicated namespace so they never collide
    // with other Composer-based plugins.
    'prefix' => 'ARippleSong\\Podcast\\Vendor',

    // The base output directory for the prefixed files.
    // This will be overridden by the 'output-dir' command line option if present.
    'output-dir' =>  __DIR__ . '/build/scoped',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // This configuration entry is completely ignored when using Box.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#finders-and-paths
    'finders' => [
        // Scope the entire vendor tree, including non-PHP assets (Carbon Fields admin UI).
        $ars_finder::create()
            ->files()
            ->in($ars_input_vendor_dir)
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
    ],

    // List of excluded files, i.e. files for which the content will be left untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#patchers
    'exclude-files' => [
        // 'src/an-excluded-file.php',
        ...$ars_excluded_files,
    ],

    // PHP version (e.g. `'7.2'`) in which the PHP parser and printer will be configured into. This will affect what
    // level of code it will understand and how the code will be printed.
    // If none (or `null`) is configured, then the host version will be used.
    'php-version' => null,

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#patchers
    'patchers' => [],

    // List of symbols to consider internal i.e. to leave untouched.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#excluded-symbols
    'exclude-namespaces' => [
        // 'Acme\Foo'                     // The Acme\Foo namespace (and sub-namespaces)
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],
    'exclude-classes' => [
        // WordPress core classes should never be prefixed.
        '~^WP_~',
        '~^Walker_~',
    ],
    'exclude-functions' => [
        // WordPress core functions should never be prefixed.
        'content_url',
        'get_blog_status',
        'get_current_screen',
        'plugins_url',
        'register_block_type',
        'site_url',
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
    ],

    // List of symbols to expose.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#exposed-symbols
    // Do not expose original symbols: keep vendor isolated.
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,
    'expose-namespaces' => [
        // 'Acme\Foo'                     // The Acme\Foo namespace (and sub-namespaces)
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],
    'expose-classes' => [],
    'expose-functions' => [],
    'expose-constants' => [],
];
