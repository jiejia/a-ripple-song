<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate plugin information in the admin area.
 *
 * It loads dependencies, registers activation/deactivation hooks, and boots the
 * plugin (custom post type + podcast RSS feed + admin settings).
 *
 * @link              https://github.com/jiejia/a-ripple-song
 * @since             0.5.0
 * @package           ARippleSong
 *
 * @wordpress-plugin
 * Plugin Name:       A Ripple Song
 * Plugin URI:        https://github.com/jiejia/a-ripple-song
 * Description:       Podcast publishing for WordPress: episodes, an Apple Podcasts & Podcasting 2.0 RSS feed, a site-wide audio player and play statistics.
 * Version:           0.5.0
 * Author:            jiejia
 * Author URI:        https://github.com/jiejia/
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Text Domain:       a-ripple-song
 * Domain Path:       /resources/lang
 */

defined('ABSPATH') || exit;

if (!defined('A_RIPPLE_SONG_PLUGIN_DIR')) {
    define('A_RIPPLE_SONG_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('A_RIPPLE_SONG_PLUGIN_URL')) {
    define('A_RIPPLE_SONG_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once A_RIPPLE_SONG_PLUGIN_DIR . 'app/autoload.php';

new \Jiejia\ARippleSong\App();

// A theme with a matching player skin (e.g. ars-petal) selects it when the plugin is activated.
register_activation_hook(__FILE__, [\Jiejia\ARippleSong\Player\ThemeSkin::class, 'sync']);
