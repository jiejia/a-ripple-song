<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/jiejia
 * @since      1.0.0
 *
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/includes
 * @author     jiejia <jiejia2009@gmail.com>
 */
class A_Ripple_Song_Podcast_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'a-ripple-song-podcast',
			false,
			dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/languages/'
		);

		/** @var string $locale Active locale used to resolve the bundled MO file. */
		$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();

		/** @var string $mo_file Absolute path to the bundled MO translation file. */
		$mo_file = plugin_dir_path( dirname( __FILE__ ) ) . 'languages/a-ripple-song-podcast-' . $locale . '.mo';

		if ( file_exists( $mo_file ) ) {
			load_textdomain( 'a-ripple-song-podcast', $mo_file );
		}
	}



}
