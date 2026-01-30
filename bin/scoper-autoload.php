<?php
/**
 * Autoload bridge for PHP-Scoper prefixed vendor.
 *
 * Composer's autoloader still maps the original vendor namespaces (e.g. Carbon_Fields\\),
 * while the PHP files themselves have been prefixed (e.g. A_Ripple_Song\\Vendor\\Carbon_Fields\\).
 *
 * This file registers an autoloader that maps the prefixed class back to its original
 * class name to locate the correct file, then lets the included file declare the
 * prefixed class.
 */

$loader = require __DIR__ . '/autoload.php';

$prefix = 'A_Ripple_Song\\Vendor\\';
$prefix_len = strlen($prefix);

spl_autoload_register(
    static function ($class) use ($loader, $prefix, $prefix_len) {
        if (strncmp($class, $prefix, $prefix_len) !== 0) {
            return;
        }

        $unprefixed = substr($class, $prefix_len);
        if ($unprefixed === '') {
            return;
        }

        // Delegate file resolution to Composer using the original class name.
        $loader->loadClass($unprefixed);
    },
    true,
    true
);

return $loader;

