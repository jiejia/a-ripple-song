<?php

namespace ARippleSong\Core;

use ARippleSong\Feed\Podcast as PodcastFeed;
use ARippleSong\PostTypes\Episode;
use ARippleSong\PostTypes\EpisodeFields;
use ARippleSong\PostTypes\EpisodeMedia;
use ARippleSong\PostTypes\EpisodeRest;
use ARippleSong\PostTypes\EpisodeSave;
use ARippleSong\Settings\Podcast as PodcastSettings;
use ARippleSong\Taxonomies\EpisodeCategory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin bootstrap and hook registry.
 */
class Plugin {

	/**
	 * Hook loader.
	 *
	 * @var Loader
	 */
	protected $loader;

	/**
	 * Plugin handle.
	 *
	 * @var string
	 */
	protected $pluginName;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Initialize plugin services and hooks.
	 */
	public function __construct() {
		$this->version    = defined( 'A_RIPPLE_SONG_PODCAST_VERSION' ) ? A_RIPPLE_SONG_PODCAST_VERSION : '1.0.0';
		$this->pluginName = 'a-ripple-song-podcast';
		$this->loader     = new Loader();

		$this->setLocale();
		$this->definePodcastHooks();
		$this->defineAdminHooks();
		$this->definePublicHooks();
	}

	/**
	 * Register internationalization hooks.
	 */
	private function setLocale() {
		$i18n = new I18n();

		$this->loader->addAction( 'plugins_loaded', $i18n, 'loadPluginTextdomain' );
	}

	/**
	 * Register podcast content, settings, REST, and feed hooks.
	 */
	private function definePodcastHooks() {
		$carbon = new Carbon();
		$this->loader->addAction( 'after_setup_theme', $carbon, 'boot' );
		$this->loader->addAction( 'init', $carbon, 'bootIfNeeded', 0 );

		$carbonUiI18n = new CarbonFieldsUiI18n();
		$this->loader->addAction( 'admin_init', $carbonUiI18n, 'loadPhpTextdomain' );
		$this->loader->addFilter( 'carbon_fields_config', $carbonUiI18n, 'filterCarbonFieldsConfig' );

		$episode = new Episode();
		$this->loader->addAction( 'after_setup_theme', $episode, 'enableThumbnailThemeSupport' );
		$this->loader->addAction( 'init', $episode, 'registerPostType' );
		$this->loader->addAction( 'init', $episode, 'registerMetricMetaFields' );
		$this->loader->addAction( 'save_post', $episode, 'ensureMetricDefaults', 10, 2 );
		$this->loader->addFilter( 'wp_insert_post_data', $episode, 'setDefaultCommentStatus', 10, 2 );

		$episodeCategory = new EpisodeCategory();
		$this->loader->addAction( 'init', $episodeCategory, 'registerTags' );
		$this->loader->addAction( 'init', $episodeCategory, 'register' );

		$episodeRest = new EpisodeRest();
		$this->loader->addAction( 'init', $episodeRest, 'registerEpisodeMeta' );
		$this->loader->addAction( 'rest_api_init', $episodeRest, 'registerEpisodeRestFields' );

		$episodeFields = new EpisodeFields();
		$this->loader->addAction( 'carbon_fields_register_fields', $episodeFields, 'registerFields' );

		$podcastSettings = new PodcastSettings();
		$this->loader->addAction( 'carbon_fields_register_fields', $podcastSettings, 'registerFields' );
		$this->loader->addFilter( 'carbon_fields_should_save_field_value', $podcastSettings, 'validateCoverFieldValue', 10, 3 );
		$this->loader->addAction( 'admin_notices', $podcastSettings, 'displayCoverValidationErrors' );
		$this->loader->addFilter( 'carbon_fields_attachment_not_found_metadata', $podcastSettings, 'previewExternalCoverUrl', 10, 3 );
		$this->loader->addFilter( 'rest_pre_dispatch', $podcastSettings, 'validateCoverOnRestSave', 10, 3 );
		$this->loader->addAction( 'admin_menu', $podcastSettings, 'removeLandingSubmenuItem', 999 );
		$this->loader->addAction( 'admin_init', $podcastSettings, 'maybeRedirectSettingsLandingPage' );

		$episodeSave = new EpisodeSave();
		$this->loader->addAction( 'carbon_fields_post_meta_container_saved', $episodeSave, 'onPostMetaSaved', 10, 2 );
		$this->loader->addAction( 'admin_notices', $episodeSave, 'showAudioMetaErrorNotice' );

		$feed = new PodcastFeed();
		$this->loader->addAction( 'init', $feed, 'registerFeed', 20 );
		$this->loader->addAction( 'pre_get_posts', $feed, 'fixPodcastArchiveQuery', 1 );
		$this->loader->addAction( 'template_redirect', $feed, 'preventPodcastSlugFromRenderingFeed', 0 );
		$this->loader->addAction( 'admin_init', $feed, 'maybeFlushRewriteRules' );
		$this->loader->addAction( 'send_headers', $feed, 'forcePodcastFeedHeaders', 0 );
		$this->loader->addFilter( 'redirect_canonical', $feed, 'preventCanonicalRedirectForPodcastFeed', 10, 2 );
	}

	/**
	 * Register admin asset and media hooks.
	 */
	private function defineAdminHooks() {
		$adminAssets  = new AdminAssets( $this->getPluginName(), $this->getVersion() );
		$episodeMedia = new EpisodeMedia();

		$this->loader->addAction( 'admin_enqueue_scripts', $adminAssets, 'enqueueStyles' );
		$this->loader->addAction( 'admin_print_footer_scripts', $adminAssets, 'printStyles', 9999 );
		$this->loader->addAction( 'admin_enqueue_scripts', $adminAssets, 'enqueueScripts' );
		$this->loader->addFilter( 'upload_mimes', $episodeMedia, 'allowUploadMimes' );
		$this->loader->addFilter( 'wp_check_filetype_and_ext', $episodeMedia, 'fixFiletypeAndExt', 10, 4 );
	}

	/**
	 * Register public asset hooks.
	 */
	private function definePublicHooks() {
		$publicAssets = new PublicAssets( $this->getPluginName(), $this->getVersion() );

		$this->loader->addAction( 'wp_enqueue_scripts', $publicAssets, 'enqueueStyles' );
		$this->loader->addAction( 'wp_enqueue_scripts', $publicAssets, 'enqueueScripts' );
	}

	/**
	 * Run all registered hooks.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the plugin handle.
	 *
	 * @return string
	 */
	public function getPluginName() {
		return $this->pluginName;
	}

	/**
	 * Get the hook loader.
	 *
	 * @return Loader
	 */
	public function getLoader() {
		return $this->loader;
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}
}
