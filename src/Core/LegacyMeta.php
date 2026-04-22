<?php

namespace ARippleSong\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read episode meta and plugin options from native storage with legacy Carbon Fields fallback.
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

		$legacy = self::readLegacyCarbonPostMeta( (int) $post_id, (string) $key );
		if ( null !== $legacy && '' !== $legacy ) {
			return $legacy;
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

		$legacy = self::readLegacyCarbonPostMeta( (int) $post_id, (string) $key );
		if ( is_array( $legacy ) ) {
			return $legacy;
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

	/**
	 * Read legacy Carbon Fields meta rows and normalize them into native arrays.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key Meta key without leading underscore.
	 * @return mixed|null
	 */
	private static function readLegacyCarbonPostMeta( $post_id, $key ) {
		$meta = get_post_meta( $post_id );
		if ( ! is_array( $meta ) || empty( $meta ) ) {
			return null;
		}

		$normalized_key = ltrim( (string) $key, '_' );
		$prefix         = '_' . $normalized_key . '|||';
		$rows           = array();

		foreach ( $meta as $meta_key => $values ) {
			if ( strpos( (string) $meta_key, $prefix ) !== 0 ) {
				continue;
			}

			if ( ! preg_match( '~^' . preg_quote( $prefix, '~' ) . '(\\d+)\\|([^|]+)$~', (string) $meta_key, $matches ) ) {
				continue;
			}

			$index  = (int) $matches[1];
			$field  = (string) $matches[2];
			$value  = is_array( $values ) ? ( $values[0] ?? null ) : null;
			$value  = maybe_unserialize( $value );

			if ( ! isset( $rows[ $index ] ) ) {
				$rows[ $index ] = array();
			}

			$rows[ $index ][ $field ] = $value;
		}

		if ( empty( $rows ) ) {
			return null;
		}

		ksort( $rows );

		if ( in_array( $normalized_key, array( 'members', 'guests' ), true ) ) {
			$ids = array();
			foreach ( $rows as $row ) {
				$candidate = $row['value'] ?? ( $row['id'] ?? null );

				if ( is_array( $candidate ) ) {
					if ( isset( $candidate['value'] ) ) {
						$candidate = $candidate['value'];
					} elseif ( isset( $candidate['id'] ) ) {
						$candidate = $candidate['id'];
					}
				}

				if ( is_string( $candidate ) && strpos( $candidate, ':' ) !== false ) {
					$parts = explode( ':', $candidate );
					$maybe = end( $parts );
					if ( is_numeric( $maybe ) ) {
						$ids[] = (int) $maybe;
					}
				} elseif ( is_numeric( $candidate ) ) {
					$ids[] = (int) $candidate;
				}
			}

			$ids = array_values(
				array_filter(
					array_unique( $ids ),
					static function ( $id ) {
						return $id > 0;
					}
				)
			);

			return $ids;
		}

		$items = array();
		foreach ( $rows as $row ) {
			$item = array();
			foreach ( $row as $field => $value ) {
				if ( $field === '_empty' ) {
					continue;
				}
				if ( $field === 'value' && ( '' === $value || null === $value ) ) {
					continue;
				}
				$item[ $field ] = $value;
			}

			if ( ! empty( $item ) ) {
				$items[] = $item;
			}
		}

		return ! empty( $items ) ? $items : null;
	}
}
