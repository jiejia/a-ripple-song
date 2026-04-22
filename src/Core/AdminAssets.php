<?php

namespace ARippleSong\Core;

use ARippleSong\PostTypes\Episode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ARippleSong
 * @subpackage ARippleSong/admin
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
		$stylePath    = A_RIPPLE_SONG_PATH . 'resources/css/admin.css';
		$styleVersion = file_exists( $stylePath ) ? (string) filemtime( $stylePath ) : $this->version;

		wp_register_style(
			$this->pluginName,
			A_RIPPLE_SONG_URL . 'resources/css/admin.css',
			array(),
			$styleVersion,
			'all'
		);

	}

	/**
	 * Print plugin admin stylesheet as late as possible.
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
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && $screen->base && $screen->post_type && $screen->post_type === Episode::POST_TYPE && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			wp_enqueue_media();
		} elseif ( $screen && is_string( $screen->id ) && strpos( $screen->id, 'a-ripple-song-podcast' ) !== false ) {
			wp_enqueue_media();
		}

		$scriptPath    = A_RIPPLE_SONG_PATH . 'resources/js/admin.js';
		$scriptVersion = file_exists( $scriptPath ) ? (string) filemtime( $scriptPath ) : $this->version;

		wp_enqueue_script( $this->pluginName, A_RIPPLE_SONG_URL . 'resources/js/admin.js', array( 'jquery' ), $scriptVersion, false );

		wp_localize_script(
			$this->pluginName,
			'arsPodcastConfig',
			array(
				'i18n'       => array(
					'upload'     => __( 'Upload', 'a-ripple-song' ),
					'remove'     => __( 'Remove', 'a-ripple-song' ),
					'download'   => __( 'Download', 'a-ripple-song' ),
					'fileLabel'  => __( 'File:', 'a-ripple-song' ),
					'selectFile' => __( 'Select file', 'a-ripple-song' ),
					'useFile'    => __( 'Use this file', 'a-ripple-song' ),
				),
				'mediaTypes' => array(
					'audio'      => 'audio',
					'image'      => 'image',
					'transcript' => null,
					'chapters'   => 'application',
				),
			)
		);

	}

}
