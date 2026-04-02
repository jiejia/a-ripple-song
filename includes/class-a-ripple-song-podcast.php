<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/jiejia
 * @since      1.0.0
 *
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/includes
 * @author     jiejia <jiejia2009@gmail.com>
 */
class A_Ripple_Song_Podcast {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      A_Ripple_Song_Podcast_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'A_RIPPLE_SONG_PODCAST_VERSION' ) ) {
			$this->version = A_RIPPLE_SONG_PODCAST_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'a-ripple-song-podcast';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_podcast_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - A_Ripple_Song_Podcast_Loader. Orchestrates the hooks of the plugin.
	 * - A_Ripple_Song_Podcast_i18n. Defines internationalization functionality.
	 * - A_Ripple_Song_Podcast_Admin. Defines all hooks for the admin area.
	 * - A_Ripple_Song_Podcast_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a-ripple-song-podcast-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a-ripple-song-podcast-i18n.php';

		/**
		 * Carbon Fields bootstrap.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a-ripple-song-podcast-carbon.php';

		/**
		 * Carbon Fields compat (scoped/unscoped builds).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a-ripple-song-podcast-carbon-compat.php';

		/**
		 * Podcast Episodes custom post type and taxonomy.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a-ripple-song-podcast-episodes.php';

		/**
		 * Episode meta fields (Carbon Fields).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a-ripple-song-podcast-episode-fields.php';

		/**
		 * Carbon Fields UI translations (fix for missing locales).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a-ripple-song-podcast-carbon-fields-ui-i18n.php';

		/**
		 * Episode save hooks.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a-ripple-song-podcast-episode-save.php';

		/**
		 * Podcast Settings page (Carbon Fields).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a-ripple-song-podcast-podcast-settings.php';

		/**
		 * Recommended themes admin page.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a-ripple-song-podcast-recommended-themes.php';

		/**
		 * REST API integration.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-a-ripple-song-podcast-rest.php';

		/**
		 * Podcast RSS feed (/feed/podcast).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-a-ripple-song-podcast-podcast-feed.php';

		/**
		 * Admin upload MIME support.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a-ripple-song-podcast-media.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-a-ripple-song-podcast-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-a-ripple-song-podcast-public.php';

		$this->loader = new A_Ripple_Song_Podcast_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the A_Ripple_Song_Podcast_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new A_Ripple_Song_Podcast_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to podcast functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_podcast_hooks() {

		$carbon = new A_Ripple_Song_Podcast_Carbon();
		$this->loader->add_action( 'after_setup_theme', $carbon, 'boot' );

		$carbon_ui_i18n = new A_Ripple_Song_Podcast_Carbon_Fields_UI_I18n();
		$this->loader->add_action( 'admin_init', $carbon_ui_i18n, 'load_php_textdomain' );
		$this->loader->add_filter( 'carbon_fields_config', $carbon_ui_i18n, 'filter_carbon_fields_config' );

		$episodes = new A_Ripple_Song_Podcast_Episodes();
		$this->loader->add_action( 'after_setup_theme', $episodes, 'enable_thumbnail_theme_support' );
		$this->loader->add_action( 'init', $episodes, 'register_post_type' );
		$this->loader->add_action( 'init', $episodes, 'register_metric_meta_fields' );
		$this->loader->add_action( 'init', $episodes, 'register_tags' );
		$this->loader->add_action( 'init', $episodes, 'register_category_taxonomy' );
		$this->loader->add_action( 'save_post', $episodes, 'ensure_metric_defaults', 10, 2 );
		$this->loader->add_filter( 'wp_insert_post_data', $episodes, 'set_default_comment_status', 10, 2 );

		$rest = new A_Ripple_Song_Podcast_REST();
		$this->loader->add_action( 'init', $rest, 'register_episode_meta' );
		$this->loader->add_action( 'rest_api_init', $rest, 'register_episode_rest_fields' );

		$episode_fields = new A_Ripple_Song_Podcast_Episode_Fields();
		$this->loader->add_action( 'carbon_fields_register_fields', $episode_fields, 'register_fields' );

		$podcast_settings = new A_Ripple_Song_Podcast_Podcast_Settings();
		$this->loader->add_action( 'carbon_fields_register_fields', $podcast_settings, 'register_fields' );
		$this->loader->add_filter( 'carbon_fields_should_save_field_value', $podcast_settings, 'validate_cover_field_value', 10, 3 );
		$this->loader->add_action( 'admin_notices', $podcast_settings, 'display_cover_validation_errors' );
		$this->loader->add_filter( 'carbon_fields_attachment_not_found_metadata', $podcast_settings, 'preview_external_cover_url', 10, 3 );
		$this->loader->add_filter( 'rest_pre_dispatch', $podcast_settings, 'validate_cover_on_rest_save', 10, 3 );
		$this->loader->add_action( 'admin_menu', $podcast_settings, 'remove_landing_submenu_item', 999 );
		$this->loader->add_action( 'admin_init', $podcast_settings, 'maybe_redirect_settings_landing_page' );

		$episode_save = new A_Ripple_Song_Podcast_Episode_Save();
		$this->loader->add_action( 'carbon_fields_post_meta_container_saved', $episode_save, 'on_post_meta_saved', 10, 2 );
		$this->loader->add_action( 'admin_notices', $episode_save, 'show_audio_meta_error_notice' );

		$feed = new A_Ripple_Song_Podcast_Podcast_Feed();
		$this->loader->add_action( 'init', $feed, 'register_feed', 20 );
		$this->loader->add_action( 'pre_get_posts', $feed, 'fix_podcast_archive_query', 1 );
		$this->loader->add_action( 'template_redirect', $feed, 'prevent_podcast_slug_from_rendering_feed', 0 );
		$this->loader->add_action( 'admin_init', $feed, 'maybe_flush_rewrite_rules' );
		$this->loader->add_action( 'send_headers', $feed, 'force_podcast_feed_headers', 0 );
		$this->loader->add_filter( 'redirect_canonical', $feed, 'prevent_canonical_redirect_for_podcast_feed', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new A_Ripple_Song_Podcast_Admin( $this->get_plugin_name(), $this->get_version() );
		$media_admin  = new A_Ripple_Song_Podcast_Media();
		$recommended_themes = new A_Ripple_Song_Podcast_Recommended_Themes();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// Print as the very last stylesheet tag in admin HTML.
		$this->loader->add_action( 'admin_print_footer_scripts', $plugin_admin, 'print_styles', 9999 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_filter( 'upload_mimes', $media_admin, 'allow_upload_mimes' );
		$this->loader->add_filter( 'wp_check_filetype_and_ext', $media_admin, 'fix_filetype_and_ext', 10, 4 );
		$this->loader->add_action( 'admin_menu', $recommended_themes, 'register_page', 1100 );
		$this->loader->add_action( 'admin_post_ars_activate_recommended_theme', $recommended_themes, 'handle_activate_action' );
		$this->loader->add_action( 'admin_post_ars_install_recommended_theme', $recommended_themes, 'handle_install_action' );
		$this->loader->add_action( 'admin_notices', $recommended_themes, 'render_admin_notice' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new A_Ripple_Song_Podcast_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    A_Ripple_Song_Podcast_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
