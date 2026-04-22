<?php

namespace ARippleSong\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read episode meta and plugin options from native storage.
 */
class LegacyMeta {

	/**
	 * Read a scalar post meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key Meta key without leading underscore.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function getPostMetaValue( $post_id, $key, $default = null ) {
		$value = self::readDirectPostMeta( (int) $post_id, (string) $key );
		if ( null !== $value && '' !== $value ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Read an array-like post meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key Meta key without leading underscore.
	 * @param array  $default Default value.
	 * @return array
	 */
	public static function getPostMetaArray( $post_id, $key, array $default = array() ) {
		$value = self::readDirectPostMeta( (int) $post_id, (string) $key );
		if ( is_array( $value ) ) {
			return $value;
		}

		return $default;
	}

	/**
	 * Read an option value.
	 *
	 * @param string $key Option name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function getOptionValue( $key, $default = null ) {
		$value = get_option( (string) $key, null );
		if ( null === $value || '' === $value ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Read direct post meta first.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key Meta key without leading underscore.
	 * @return mixed|null
	 */
	private static function readDirectPostMeta( $post_id, $key ) {
		$normalized_key = ltrim( (string) $key, '_' );

		$value = get_post_meta( $post_id, '_' . $normalized_key, true );
		if ( null !== $value && '' !== $value ) {
			return $value;
		}

		$value = get_post_meta( $post_id, $normalized_key, true );
		if ( null !== $value && '' !== $value ) {
			return $value;
		}

		return null;
	}

}
