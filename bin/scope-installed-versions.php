<?php

/**
 * Second half of `composer scope`: Composer's autoloader always maps Composer\InstalledVersions, but
 * `dump-autoload` in build/ doesn't write that class or the installed.php it reads. Copy the class
 * over and write installed.php without the dev packages, so the build's classmap points at real files.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $root . '/build/vendor/composer';

if (!is_dir($target)) {
    fwrite(STDERR, "build/vendor/composer is missing, run php-scoper and dump-autoload first.\n");
    exit(1);
}

copy($root . '/vendor/composer/InstalledVersions.php', $target . '/InstalledVersions.php');

$installed = require $root . '/vendor/composer/installed.php';

$installed['root']['dev'] = false;
$installed['versions'] = array_filter($installed['versions'], static fn (array $package): bool => empty($package['dev_requirement']));

// install_path entries are absolute once required; make them relative to the file again, as Composer does.
$export = str_replace("'" . $root . '/vendor/composer/', "__DIR__ . '/", var_export($installed, true));

file_put_contents($target . '/installed.php', '<?php return ' . $export . ";\n");
