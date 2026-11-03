<?php

declare(strict_types=1);

/**
 * PHP-Scoper config: `composer scope` copies the plugin to build/ with every namespace (App\ and
 * any runtime Composer package) moved under ARippleSong\, so it can't clash with other plugins.
 * WordPress core symbols are left alone (sniccowp/php-scoper-wordpress-excludes).
 * Both tools live in vendor-bin/php-scoper (bamarni/composer-bin-plugin), away from the plugin's autoloader.
 *
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 */

use Isolated\Symfony\Component\Finder\Finder;

$wpExcludes = static function (string $kind): array {
    $file = __DIR__ . "/vendor-bin/php-scoper/vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-{$kind}.json";

    return json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
};

// Only runtime packages go into the build; dev packages (composer-bin-plugin, ...) stay out.
$installed = json_decode((string) file_get_contents(__DIR__ . '/vendor/composer/installed.json'), true, 512, JSON_THROW_ON_ERROR);
$devPackages = $installed['dev-package-names'] ?? [];
$runtimePackages = array_values(array_filter(
    array_map(static fn (array $package): string => $package['name'], $installed['packages'] ?? []),
    static fn (string $name): bool => !in_array($name, $devPackages, true),
));

$finders = [
    Finder::create()->files()->in(['app', 'resources', 'public']),
    Finder::create()->append([
        'a-ripple-song.php',
        'README.txt',
        'LICENSE',
        'composer.json',
    ]),
];

if ($runtimePackages !== []) {
    $finders[] = Finder::create()
        ->files()
        ->ignoreVCS(true)
        ->notName('/LICENSE|.*\.md|.*\.dist|Makefile|composer\.json|composer\.lock/')
        ->exclude(['doc', 'docs', 'test', 'tests', 'Tests'])
        ->in(array_map(static fn (string $name): string => 'vendor/' . $name, $runtimePackages));
}

return [
    'prefix' => 'ARippleSong',
    'output-dir' => 'build',
    'finders' => $finders,
    'exclude-classes' => $wpExcludes('classes'),
    'exclude-functions' => $wpExcludes('functions'),
    'exclude-constants' => array_merge($wpExcludes('constants'), ['A_RIPPLE_SONG_PLUGIN_DIR', 'A_RIPPLE_SONG_PLUGIN_URL']),
];
