<?php

namespace ARippleSong\Podcast\Core;

use ARippleSong\Podcast\Feed\Podcast as PodcastFeed;
use ARippleSong\Podcast\PostTypes\Episode;
use ARippleSong\Podcast\Taxonomies\EpisodeCategory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    ARippleSong\Podcast
 * @subpackage ARippleSong\Podcast/includes
 * @author     jiejia <jiejia2009@gmail.com>
 */
class Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$episode = new Episode();
		$episode->registerPostType();

		$category = new EpisodeCategory();
		$category->register();
		$category->registerTags();

		$feed = new PodcastFeed();
		$feed->registerFeed();

		self::maybeMigrateFromTheme();
		flush_rewrite_rules( false );
	}

	/**
	 * One-time migration from the previous theme's CPT/taxonomy names.
	 *
	 * - `podcast` -> `ars_episode`
	 * - `podcast_category` -> `ars_episode_category`
	 */
	private static function maybeMigrateFromTheme() {
		$flag = 'ars_podcast_migrated_v1';
		if ( get_option( $flag ) ) {
			return;
		}

		global $wpdb;

		// Migrate post type.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time activation migration.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s",
				Episode::POST_TYPE,
				'podcast'
			)
		);

		// Migrate taxonomy.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time activation migration.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s",
				EpisodeCategory::TAXONOMY,
				'podcast_category'
			)
		);

		self::maybeMigrateMemberMeta();

		update_option( $flag, '1', 'no' );
	}

	/**
	 * Migrate old CMB2-style member/guest meta to Carbon Fields association values.
	 *
	 * Old format (multicheck): [ user_id => 'on', ... ]
	 * New format (association): [ 'user:user:123', ... ] (or structured arrays saved by Carbon Fields).
	 */
	private static function maybeMigrateMemberMeta() {
		$post_ids = get_posts(
			array(
				'post_type'      => Episode::POST_TYPE,
				'post_status'    => 'any',
					'fields'         => 'ids',
					'posts_per_page' => -1,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- One-time activation migration.
					'meta_query'     => array(
						'relation' => 'OR',
						array(
							'key'     => 'members',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'guests',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $post_ids as $post_id ) {
			self::maybeMigrateMemberMetaKey( (int) $post_id, 'members' );
			self::maybeMigrateMemberMetaKey( (int) $post_id, 'guests' );
		}
	}

	/**
	 * Migrate one old member meta key to Carbon Fields association values.
	 *
	 * @param int    $post_id  Episode post ID.
	 * @param string $meta_key Meta key to migrate.
	 * @return void
	 */
	private static function maybeMigrateMemberMetaKey( $post_id, $meta_key ) {
		$value = get_post_meta( $post_id, $meta_key, true );

		if ( empty( $value ) || ! is_array( $value ) ) {
			return;
		}

		$first = reset( $value );
		if ( is_array( $first ) && isset( $first['type'] ) && isset( $first['id'] ) ) {
			// Already Carbon Fields association format.
			return;
		}

		if ( is_string( $first ) && strpos( $first, ':' ) !== false ) {
			// Already type:subtype:id strings.
			return;
		}

		$ids = array();
		foreach ( $value as $k => $v ) {
			if ( is_numeric( $k ) ) {
				$ids[] = (int) $k;
			}
			if ( is_numeric( $v ) ) {
				$ids[] = (int) $v;
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

		if ( empty( $ids ) ) {
			return;
		}

		$new = array();
		foreach ( $ids as $id ) {
			$new[] = 'user:user:' . (int) $id;
		}

		update_post_meta( $post_id, $meta_key, $new );
	}

}
