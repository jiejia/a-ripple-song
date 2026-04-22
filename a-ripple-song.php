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
 * Description:       Podcast features for the A Ripple Song theme: Episode CPT + /feed/podcast RSS (iTunes & Podcasting 2.0 tags).
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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( PHP_VERSION_ID < 80200 ) {
	add_action(
		'admin_notices',
		static function () {
			$message = sprintf(
				'A Ripple Song requires PHP %s or higher. Your server is running PHP %s.',
				'8.2',
				PHP_VERSION
			);

			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html( $message )
			);
		}
	);

	return;
}

if ( isset( $GLOBALS['wp_version'] ) && version_compare( $GLOBALS['wp_version'], '6.6', '<' ) ) {
	add_action(
		'admin_notices',
		static function () {
			$message = sprintf(
				'A Ripple Song requires WordPress %s or higher. Your site is running WordPress %s.',
				'6.6',
				$GLOBALS['wp_version']
			);

			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html( $message )
			);
		}
	);

	return;
}

/**
 * Currently plugin version.
 * Start at version 0.5.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'A_RIPPLE_SONG_VERSION', '0.5.0' );
define( 'A_RIPPLE_SONG_FILE', __FILE__ );
define( 'A_RIPPLE_SONG_PATH', plugin_dir_path( __FILE__ ) );
define( 'A_RIPPLE_SONG_URL', plugin_dir_url( __FILE__ ) );

$autoloadPath       = __DIR__ . '/vendor/autoload.php';
$scoperAutoloadPath = __DIR__ . '/vendor/scoper-autoload.php';
if ( file_exists( $scoperAutoloadPath ) ) {
	require_once $scoperAutoloadPath;
} elseif ( file_exists( $autoloadPath ) ) {
	require_once $autoloadPath;
}

/**
 * Register compatibility mappings for multi-class source files.
 */
spl_autoload_register(
	static function ( $class ) {
		$prefix = 'ARippleSong\\';

		$class_map = array(
			'ARippleSong\\PostTypes\\EpisodeSave'   => A_RIPPLE_SONG_PATH . 'src/PostTypes/Episode.php',
			'ARippleSong\\PostTypes\\EpisodeRest'   => A_RIPPLE_SONG_PATH . 'src/PostTypes/Episode.php',
			'ARippleSong\\PostTypes\\EpisodeMedia'  => A_RIPPLE_SONG_PATH . 'src/PostTypes/Episode.php',
		);

		if ( isset( $class_map[ $class ] ) ) {
			require_once $class_map[ $class ];
			return;
		}

		if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, strlen( $prefix ) );
		$file           = A_RIPPLE_SONG_PATH . 'src/' . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

register_activation_hook( __FILE__, array( 'ARippleSong\Core\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ARippleSong\Core\Deactivator', 'deactivate' ) );

/**
 * Start the plugin.
 */
$plugin = new ARippleSong\Core\Plugin();
$plugin->run();
