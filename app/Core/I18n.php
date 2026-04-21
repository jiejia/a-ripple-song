<?php

namespace ARippleSong\Podcast\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    ARippleSong\Podcast
 * @subpackage ARippleSong\Podcast/includes
 * @author     jiejia <jiejia2009@gmail.com>
 */
class I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function loadPluginTextdomain() {
		/** @var string $locale Active locale used to resolve the bundled MO file. */
		$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();

		/** @var string $mo_file Absolute path to the bundled MO translation file. */
		$mo_file = A_RIPPLE_SONG_PODCAST_PATH . 'resources/lang/a-ripple-song-podcast-' . $locale . '.mo';

		if ( file_exists( $mo_file ) ) {
			load_textdomain( 'a-ripple-song-podcast', $mo_file );
		}
	}



}
