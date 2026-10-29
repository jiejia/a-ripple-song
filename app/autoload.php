<?php

defined('ABSPATH') || exit;

// PSR-4 autoloader: Jiejia\ARippleSong\Foo\Bar => app/Foo/Bar.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'Jiejia\\ARippleSong\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = A_RIPPLE_SONG_PLUGIN_DIR . 'app/' . $relative . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});