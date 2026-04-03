<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/jiejia
 * @since      1.0.0
 *
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/admin
 * @author     jiejia <jiejia2009@gmail.com>
 */
class A_Ripple_Song_Podcast_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in A_Ripple_Song_Podcast_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The A_Ripple_Song_Podcast_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/a-ripple-song-podcast-admin.css',
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
	public function print_styles() {
		if ( ! is_admin() ) {
			return;
		}

		wp_print_styles( $this->plugin_name );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in A_Ripple_Song_Podcast_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The A_Ripple_Song_Podcast_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && $screen->base && $screen->post_type && $screen->post_type === A_Ripple_Song_Podcast_Episodes::POST_TYPE && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/a-ripple-song-podcast-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script(
			$this->plugin_name,
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
