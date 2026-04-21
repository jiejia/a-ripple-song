<?php

namespace ARippleSong\Podcast\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Carbon Fields compatibility layer for scoped/unscoped vendor builds.
 *
 * When building a release, vendor dependencies may be prefixed with PHP-Scoper.
 * This class resolves Carbon Fields classes at runtime in either scenario.
 *
 * @package    ARippleSong\Podcast
 * @subpackage ARippleSong\Podcast/includes
 */
class CarbonCompat {

	private const SHARED_PREFIX = '\\A_Ripple_Song_Theme\\Vendor\\';
	private const SCOPED_PREFIX = '\\ARippleSong\\Podcast\\Vendor\\';

	/**
	 * Boot Carbon Fields (scoped or unscoped).
	 *
	 * @return void
	 */
	public static function bootCarbonFields() {
		$class = self::resolveClass( '\\Carbon_Fields\\Carbon_Fields', self::SCOPED_PREFIX . 'Carbon_Fields\\Carbon_Fields', self::SHARED_PREFIX . 'Carbon_Fields\\Carbon_Fields' );
		if ( $class && method_exists( $class, 'boot' ) ) {
			$class::boot();
		}
	}

	/**
	 * Resolve the Container proxy class name.
	 *
	 * @return string|null
	 */
	public static function containerClass() {
		return self::resolveClass( '\\Carbon_Fields\\Container', self::SCOPED_PREFIX . 'Carbon_Fields\\Container', self::SHARED_PREFIX . 'Carbon_Fields\\Container' );
	}

	/**
	 * Resolve the Field proxy class name.
	 *
	 * @return string|null
	 */
	public static function fieldClass() {
		return self::resolveClass( '\\Carbon_Fields\\Field', self::SCOPED_PREFIX . 'Carbon_Fields\\Field', self::SHARED_PREFIX . 'Carbon_Fields\\Field' );
	}

	/**
	 * Resolve the Helper class name.
	 *
	 * @return string|null
	 */
	private static function helperClass() {
		return self::resolveClass( '\\Carbon_Fields\\Helper\\Helper', self::SCOPED_PREFIX . 'Carbon_Fields\\Helper\\Helper', self::SHARED_PREFIX . 'Carbon_Fields\\Helper\\Helper' );
	}

	/**
	 * Get a Carbon Fields theme option when available.
	 *
	 * @param string $name
	 * @param string $container_id
	 * @return mixed|null
	 */
	public static function getThemeOption( $name, $container_id = '' ) {
		$helper = self::helperClass();
		if ( $helper && method_exists( $helper, 'get_theme_option' ) ) {
			return $helper::get_theme_option( (string) $name, (string) $container_id );
		}

		if ( function_exists( 'carbon_get_theme_option' ) ) {
			return carbon_get_theme_option( (string) $name, (string) $container_id );
		}

		return null;
	}

	/**
	 * Get a Carbon Fields post meta value when available.
	 *
	 * @param int    $post_id
	 * @param string $name
	 * @param string $container_id
	 * @return mixed|null
	 */
	public static function getPostMeta( $post_id, $name, $container_id = '' ) {
		$helper = self::helperClass();
		if ( $helper && method_exists( $helper, 'get_post_meta' ) ) {
			return $helper::get_post_meta( (int) $post_id, (string) $name, (string) $container_id );
		}

		if ( function_exists( 'carbon_get_post_meta' ) ) {
			return carbon_get_post_meta( (int) $post_id, (string) $name, (string) $container_id );
		}

		return null;
	}

	/**
	 * Set a Carbon Fields post meta value when available.
	 *
	 * @param int         $post_id
	 * @param string      $name
	 * @param string|int  $value
	 * @param string      $container_id
	 * @return bool
	 */
	public static function setPostMeta( $post_id, $name, $value, $container_id = '' ) {
		$helper = self::helperClass();
		if ( $helper && method_exists( $helper, 'set_post_meta' ) ) {
			$helper::set_post_meta( (int) $post_id, (string) $name, $value, (string) $container_id );
			return true;
		}

		if ( function_exists( 'carbon_set_post_meta' ) ) {
			carbon_set_post_meta( (int) $post_id, (string) $name, $value, (string) $container_id );
			return true;
		}

		return false;
	}

	/**
	 * Resolve a class which may be present in unscoped or scoped builds.
	 *
	 * First prefer classes that are already loaded, then only autoload known scoped
	 * candidates. This avoids probing unscoped Carbon Fields classes in a scoped build,
	 * which can include the same file twice through different class names.
	 *
	 * @param string ...$candidates
	 * @return string|null
	 */
	private static function resolveClass( ...$candidates ) {
		foreach ( $candidates as $candidate ) {
			if ( is_string( $candidate ) && $candidate !== '' && class_exists( $candidate, false ) ) {
				return $candidate;
			}
		}

		$shared_booted = did_action( 'carbon_fields_loaded' ) && class_exists( '\\A_Ripple_Song_Theme\\Vendor\\Carbon_Fields\\Carbon_Fields', false );
		$plugin_booted = did_action( 'carbon_fields_loaded' ) && class_exists( '\\ARippleSong\\Podcast\\Vendor\\Carbon_Fields\\Carbon_Fields', false );

		foreach ( $candidates as $candidate ) {
			if ( ! is_string( $candidate ) || $candidate === '' ) {
				continue;
			}

			if ( strpos( $candidate, '\\Carbon_Fields\\' ) === 0 ) {
				/**
				 * Source checkouts use the Composer autoloaded unscoped Carbon Fields
				 * classes. Only skip probing them after a scoped build has booted.
				 */
				if ( ! $shared_booted && ! $plugin_booted && class_exists( $candidate ) ) {
					return $candidate;
				}

				continue;
			}

			if ( $shared_booted && strpos( $candidate, self::SHARED_PREFIX ) !== 0 ) {
				continue;
			}

			if ( $plugin_booted && strpos( $candidate, self::SCOPED_PREFIX ) !== 0 ) {
				continue;
			}

			if ( class_exists( $candidate ) ) {
				return $candidate;
			}
		}

		return null;
	}
}

/**
 * Carbon Fields bootstrap.
 *
 * @package    ARippleSong\Podcast
 * @subpackage ARippleSong\Podcast/includes
 */
class Carbon {

	/**
	 * Boot Carbon Fields.
	 */
	public function boot() {
		if ( did_action( 'carbon_fields_loaded' ) ) {
			return;
		}

		if ( class_exists( CarbonCompat::class ) ) {
			CarbonCompat::bootCarbonFields();
			return;
		}

		if ( class_exists( '\Carbon_Fields\Carbon_Fields' ) ) {
			\Carbon_Fields\Carbon_Fields::boot();
		}
	}

	/**
	 * Boot Carbon Fields immediately when the normal bootstrap hook has already passed.
	 *
	 * @return void
	 */
	public function bootIfNeeded() {
		if ( did_action( 'after_setup_theme' ) ) {
			$this->boot();
		}
	}
}

/**
 * Carbon Fields UI translations hotfix.
 *
 * Carbon Fields relies on the `carbon-fields-ui` textdomain for JS strings like:
 * - "There are no entries yet."
 * - "Add %s"
 *
 * This plugin bundles Carbon Fields, but the shipped Carbon Fields UI language
 * files may not cover all locales (e.g. zh_CN/zh_TW/zh_HK). Also, some Carbon
 * Fields versions load UI textdomains using `get_locale()` instead of the admin
 * user's locale.
 *
 * We inject missing locale strings via `carbon_fields_config` so the UI always
 * reflects the current admin language without modifying Carbon Fields itself.
 *
 * @package    ARippleSong\Podcast
 * @subpackage ARippleSong\Podcast/admin
 */
class CarbonFieldsUiI18n {

	/**
	 * Load Carbon Fields PHP translations from the plugin bundle.
	 *
	 * @return void
	 */
	public function loadPhpTextdomain() {
		/**
		 * Carbon Fields uses its own `carbon-fields` textdomain for PHP-rendered
		 * labels such as the options-page "Actions" heading.
		 */
		$locale = is_admin() ? get_user_locale() : get_locale();
		$locale = (string) $locale;
		$mo_file = A_RIPPLE_SONG_PODCAST_PATH . 'resources/lang/carbon-fields-' . $locale . '.mo';

		if ( file_exists( $mo_file ) ) {
			load_textdomain( 'carbon-fields', $mo_file );
		}
	}

	/**
	 * Inject UI translations used by Carbon Fields JS.
	 *
	 * @param array $config
	 * @return array
	 */
	public function filterCarbonFieldsConfig( $config ) {
		if ( ! is_array( $config ) || ! isset( $config['config'] ) || ! is_array( $config['config'] ) ) {
			return $config;
		}

		if ( ! isset( $config['config']['locale'] ) || ! is_array( $config['config']['locale'] ) ) {
			$config['config']['locale'] = array();
		}

		$locale = is_admin() ? get_user_locale() : get_locale();

		$translations = $this->getOverridesForLocale( $locale );
		if ( empty( $translations ) ) {
			return $config;
		}

		// Ensure Jed metadata exists.
		if ( ! isset( $config['config']['locale'][''] ) || ! is_array( $config['config']['locale'][''] ) ) {
			$config['config']['locale'][''] = array(
				'domain' => 'carbon-fields-ui',
				'lang'   => $locale,
			);
		}

		foreach ( $translations as $msgid => $translation ) {
			$config['config']['locale'][ $msgid ] = array( $translation );
		}

		return $config;
	}

	/**
	 * Get string overrides for a given locale.
	 *
	 * @param string $locale
	 * @return array<string,string>
	 */
	private function getOverridesForLocale( $locale ) {
		$locale = (string) $locale;

		// Normalize locales like zh_HK -> zh_HK, but also accept zh_CN etc.
		switch ( $locale ) {
			case 'ja':
				return array(
					'There are no entries yet.' => 'まだ項目はありません。',
					'Add %s'                    => '%sを追加',
				);
			case 'zh_CN':
				return array(
					'There are no entries yet.' => '暂无条目。',
					'Add %s'                    => '添加 %s',
				);
			case 'zh_TW':
			case 'zh_HK':
				return array(
					'There are no entries yet.' => '尚無項目。',
					'Add %s'                    => '新增 %s',
				);
			default:
				return array();
		}
	}
}
