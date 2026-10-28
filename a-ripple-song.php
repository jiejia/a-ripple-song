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
 * @since             0.5.2
 * @package           ARippleSong
 *
 * @wordpress-plugin
 * Plugin Name:       A Ripple Song
 * Plugin URI:        https://github.com/jiejia/a-ripple-song
 * Description:       Podcast features for the A Ripple Song theme: Episode CPT + /feed/podcast RSS (iTunes & Podcasting 2.0 tags).
 * Version:           0.5.2
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

require_once A_RIPPLE_SONG_PLUGIN_DIR . 'app/autoload.php';

new \Jiejia\ARippleSong\Plugin();
