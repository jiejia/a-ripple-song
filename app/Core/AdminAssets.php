<?php

namespace ARippleSong\Podcast\Core;

use ARippleSong\Podcast\PostTypes\Episode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ARippleSong\Podcast
 * @subpackage ARippleSong\Podcast/admin
 * @author     jiejia <jiejia2009@gmail.com>
 */
class AdminAssets {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $pluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $pluginName       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $pluginName, $version ) {

		$this->pluginName = $pluginName;
		$this->version    = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueStyles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_style(
			$this->pluginName,
			A_RIPPLE_SONG_PODCAST_URL . 'resources/css/admin.css',
			wp_style_is( 'carbon-fields-metaboxes', 'registered' ) ? array( 'carbon-fields-metaboxes' ) : array(),
			$this->version,
			'all'
		);

	}

	/**
	 * Print plugin admin stylesheet as late as possible.
	 *
	 * WordPress prints "late styles" in admin via `_wp_footer_scripts()`. Carbon Fields
	 * enqueues its styles late, so we print our stylesheet after everything else to
	 * make overrides reliable.
	 *
	 * @since    1.0.0
	 */
	public function printStyles() {
		if ( ! is_admin() ) {
			return;
		}

		wp_print_styles( $this->pluginName );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueueScripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && $screen->base && $screen->post_type && $screen->post_type === Episode::POST_TYPE && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_script( $this->pluginName, A_RIPPLE_SONG_PODCAST_URL . 'resources/js/admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script(
			$this->pluginName,
			'arsPodcastAdmin',
			array(
					'i18n'       => array(
						'upload'     => __( 'Upload', 'a-ripple-song-podcast' ),
						'remove'     => __( 'Remove', 'a-ripple-song-podcast' ),
						'download'   => __( 'Download', 'a-ripple-song-podcast' ),
						'fileLabel'  => __( 'File:', 'a-ripple-song-podcast' ),
						'selectFile' => __( 'Select file', 'a-ripple-song-podcast' ),
						'useFile'    => __( 'Use this file', 'a-ripple-song-podcast' ),
					),
				'mediaTypes' => array(
					'audio'      => 'audio',
					'transcript' => null,
					'chapters'   => 'application',
				),
				'metaboxId'  => 'carbon_fields_container_ars_episode_details',
			)
		);

	}

}
