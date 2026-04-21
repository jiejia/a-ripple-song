<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate plugin information in the admin area.
 *
 * It loads dependencies, registers activation/deactivation hooks, and boots the
 * plugin (custom post type + podcast RSS feed + admin settings).
 *
 * @link              https://github.com/jiejia/a-ripple-song-podcast
 * @since             0.5.0
 * @package           ARippleSong\Podcast
 *
 * @wordpress-plugin
 * Plugin Name:       A Ripple Song Podcast
 * Plugin URI:        https://github.com/jiejia/a-ripple-song-podcast
 * Description:       Podcast features for the A Ripple Song theme: Episode CPT + /feed/podcast RSS (iTunes & Podcasting 2.0 tags).
 * Version:           0.5.0
 * Author:            jiejia
 * Author URI:        https://github.com/jiejia/
 * License:           GPL-3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Text Domain:       a-ripple-song-podcast
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
				'A Ripple Song Podcast requires PHP %s or higher. Your server is running PHP %s.',
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
				'A Ripple Song Podcast requires WordPress %s or higher. Your site is running WordPress %s.',
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
define( 'A_RIPPLE_SONG_PODCAST_VERSION', '0.5.0' );
define( 'A_RIPPLE_SONG_PODCAST_FILE', __FILE__ );
define( 'A_RIPPLE_SONG_PODCAST_PATH', plugin_dir_path( __FILE__ ) );
define( 'A_RIPPLE_SONG_PODCAST_URL', plugin_dir_url( __FILE__ ) );

$autoloadPath       = __DIR__ . '/vendor/autoload.php';
$scoperAutoloadPath = __DIR__ . '/vendor/scoper-autoload.php';
if ( file_exists( $scoperAutoloadPath ) ) {
	require_once $scoperAutoloadPath;
} elseif ( file_exists( $autoloadPath ) ) {
	require_once $autoloadPath;
}

/**
 * Register the plugin source autoloader.
 */
spl_autoload_register(
	static function ( $class ) {
		$prefix = 'ARippleSong\\Podcast\\';

		$class_map = array(
			'ARippleSong\\Podcast\\Core\\CarbonCompat'        => A_RIPPLE_SONG_PODCAST_PATH . 'app/Core/Carbon.php',
			'ARippleSong\\Podcast\\Core\\CarbonFieldsUiI18n' => A_RIPPLE_SONG_PODCAST_PATH . 'app/Core/Carbon.php',
			'ARippleSong\\Podcast\\PostTypes\\EpisodeFields' => A_RIPPLE_SONG_PODCAST_PATH . 'app/PostTypes/Episode.php',
			'ARippleSong\\Podcast\\PostTypes\\EpisodeSave'   => A_RIPPLE_SONG_PODCAST_PATH . 'app/PostTypes/Episode.php',
			'ARippleSong\\Podcast\\PostTypes\\EpisodeRest'   => A_RIPPLE_SONG_PODCAST_PATH . 'app/PostTypes/Episode.php',
			'ARippleSong\\Podcast\\PostTypes\\EpisodeMedia'  => A_RIPPLE_SONG_PODCAST_PATH . 'app/PostTypes/Episode.php',
		);

		if ( isset( $class_map[ $class ] ) ) {
			require_once $class_map[ $class ];
			return;
		}

		if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, strlen( $prefix ) );
		$file           = A_RIPPLE_SONG_PODCAST_PATH . 'app/' . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

register_activation_hook( __FILE__, array( 'ARippleSong\\Podcast\\Core\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ARippleSong\\Podcast\\Core\\Deactivator', 'deactivate' ) );

/**
 * Start the plugin.
 */
$plugin = new ARippleSong\Podcast\Core\Plugin();
$plugin->run();
