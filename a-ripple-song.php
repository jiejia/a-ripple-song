<?php

defined('ABSPATH') || exit;

if (!defined('A_RIPPLE_SONG_PLUGIN_DIR')) {
    define('A_RIPPLE_SONG_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

new \Jiejia\ARippleSong\Plugin();
