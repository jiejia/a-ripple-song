<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Carbon Fields compatibility layer for scoped/unscoped vendor builds.
 *
 * When building a release, vendor dependencies may be prefixed with PHP-Scoper.
 * This class resolves Carbon Fields classes at runtime in either scenario.
 *
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/includes
 */
class A_Ripple_Song_Podcast_Carbon_Compat {

	private const SHARED_PREFIX = '\\A_Ripple_Song_Theme\\Vendor\\';
	private const SCOPED_PREFIX = '\\A_Ripple_Song_Podcast\\Vendor\\';

	/**
	 * Boot Carbon Fields (scoped or unscoped).
	 *
	 * @return void
	 */
	public static function boot_carbon_fields() {
		$class = self::resolve_class( '\\Carbon_Fields\\Carbon_Fields', self::SCOPED_PREFIX . 'Carbon_Fields\\Carbon_Fields', self::SHARED_PREFIX . 'Carbon_Fields\\Carbon_Fields' );
		if ( $class && method_exists( $class, 'boot' ) ) {
			$class::boot();
		}
	}

	/**
	 * Resolve the Container proxy class name.
	 *
	 * @return string|null
	 */
	public static function container_class() {
		return self::resolve_class( '\\Carbon_Fields\\Container', self::SCOPED_PREFIX . 'Carbon_Fields\\Container', self::SHARED_PREFIX . 'Carbon_Fields\\Container' );
	}

	/**
	 * Resolve the Field proxy class name.
	 *
	 * @return string|null
	 */
	public static function field_class() {
		return self::resolve_class( '\\Carbon_Fields\\Field', self::SCOPED_PREFIX . 'Carbon_Fields\\Field', self::SHARED_PREFIX . 'Carbon_Fields\\Field' );
	}

	/**
	 * Resolve the Helper class name.
	 *
	 * @return string|null
	 */
	private static function helper_class() {
		return self::resolve_class( '\\Carbon_Fields\\Helper\\Helper', self::SCOPED_PREFIX . 'Carbon_Fields\\Helper\\Helper', self::SHARED_PREFIX . 'Carbon_Fields\\Helper\\Helper' );
	}

	/**
	 * Get a Carbon Fields theme option when available.
	 *
	 * @param string $name
	 * @param string $container_id
	 * @return mixed|null
	 */
	public static function get_theme_option( $name, $container_id = '' ) {
		$helper = self::helper_class();
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
	public static function get_post_meta( $post_id, $name, $container_id = '' ) {
		$helper = self::helper_class();
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
	public static function set_post_meta( $post_id, $name, $value, $container_id = '' ) {
		$helper = self::helper_class();
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
	private static function resolve_class( ...$candidates ) {
		foreach ( $candidates as $candidate ) {
			if ( is_string( $candidate ) && $candidate !== '' && class_exists( $candidate, false ) ) {
				return $candidate;
			}
		}

		foreach ( $candidates as $candidate ) {
			if ( ! is_string( $candidate ) || $candidate === '' ) {
				continue;
			}

			if ( strpos( $candidate, '\\Carbon_Fields\\' ) === 0 ) {
				continue;
			}

			if ( class_exists( $candidate ) ) {
				return $candidate;
			}
		}

		return null;
	}
}
