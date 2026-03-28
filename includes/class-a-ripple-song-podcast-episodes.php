<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Podcast Episodes (CPT) and taxonomy registration.
 *
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/includes
 */
class A_Ripple_Song_Podcast_Episodes {

	/**
	 * Custom post type key.
	 */
	public const POST_TYPE = 'ars_episode';

	/**
	 * Taxonomy key.
	 */
	public const TAXONOMY = 'ars_episode_category';

	/**
	 * Enable featured image support for the episode post type from the plugin.
	 *
	 * WordPress requires thumbnail theme support in addition to CPT support, so
	 * the plugin registers it here for its own post type only.
	 *
	 * @return void
	 */
	public function enable_thumbnail_theme_support() {
		add_theme_support( 'post-thumbnails', array( self::POST_TYPE ) );
	}

	/**
	 * Register custom post type.
	 */
	public function register_post_type() {
		$this->maybe_migrate_post_type_key();

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => array(
					'name'                  => __( 'Episodes', 'a-ripple-song-podcast' ),
					'singular_name'         => __( 'Episode', 'a-ripple-song-podcast' ),
					'add_new'               => __( 'Add New Episode', 'a-ripple-song-podcast' ),
					'add_new_item'          => __( 'Add New Episode', 'a-ripple-song-podcast' ),
					'edit_item'             => __( 'Edit Episode', 'a-ripple-song-podcast' ),
					'new_item'              => __( 'New Episode', 'a-ripple-song-podcast' ),
					'view_item'             => __( 'View Episode', 'a-ripple-song-podcast' ),
					'view_items'            => __( 'View Episodes', 'a-ripple-song-podcast' ),
					'search_items'          => __( 'Search Episodes', 'a-ripple-song-podcast' ),
					'not_found'             => __( 'No episodes found', 'a-ripple-song-podcast' ),
					'not_found_in_trash'    => __( 'No episodes found in Trash', 'a-ripple-song-podcast' ),
					'all_items'             => __( 'All Episodes', 'a-ripple-song-podcast' ),
					'menu_name'             => __( 'ARS Episodes', 'a-ripple-song-podcast' ),
					'name_admin_bar'        => __( 'Episode', 'a-ripple-song-podcast' ),
					'item_published'        => __( 'Episode published.', 'a-ripple-song-podcast' ),
					'item_updated'          => __( 'Episode updated.', 'a-ripple-song-podcast' ),
					'item_reverted_to_draft' => __( 'Episode reverted to draft.', 'a-ripple-song-podcast' ),
					'item_scheduled'        => __( 'Episode scheduled.', 'a-ripple-song-podcast' ),
				),
				'public'             => true,
				'has_archive'        => true,
				'menu_icon'          => 'dashicons-microphone',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'trackbacks' ),
				'taxonomies'         => array( 'post_tag' ),
				'show_in_rest'       => true,
				'show_in_nav_menus'  => true,
				'rewrite'            => array( 'slug' => 'podcasts' ),
				'menu_position'      => 5,
			)
		);

		$this->maybe_flush_rewrite_rules_after_post_type_rename();
	}

	/**
	 * One-time migration from the old CPT key (`ars_episodes`) to the new key (`ars_episode`).
	 */
	private function maybe_migrate_post_type_key() {
		$flag = 'ars_podcast_migrated_v2_post_type_key';
		if ( get_option( $flag ) ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time post type key migration.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s",
				self::POST_TYPE,
				'ars_episodes'
			)
		);

		update_option( $flag, '1', 'no' );
	}

	/**
	 * Flush rewrite rules once after the CPT key rename so existing sites pick up new rules.
	 */
	private function maybe_flush_rewrite_rules_after_post_type_rename() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$flag = 'ars_podcast_rewrite_flushed_v2_post_type_key';
		if ( get_option( $flag ) ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( $flag, '1', 'no' );
	}

	/**
	 * Register taxonomy for episode categories.
	 */
	public function register_category_taxonomy() {
		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'              => __( 'Episode Categories', 'a-ripple-song-podcast' ),
					'singular_name'     => __( 'Episode Category', 'a-ripple-song-podcast' ),
					'search_items'      => __( 'Search Episode Categories', 'a-ripple-song-podcast' ),
					'all_items'         => __( 'All Episode Categories', 'a-ripple-song-podcast' ),
					'parent_item'       => __( 'Parent Episode Category', 'a-ripple-song-podcast' ),
					'parent_item_colon' => __( 'Parent Episode Category:', 'a-ripple-song-podcast' ),
					'edit_item'         => __( 'Edit Episode Category', 'a-ripple-song-podcast' ),
					'update_item'       => __( 'Update Episode Category', 'a-ripple-song-podcast' ),
					'add_new_item'      => __( 'Add New Episode Category', 'a-ripple-song-podcast' ),
					'new_item_name'     => __( 'New Episode Category Name', 'a-ripple-song-podcast' ),
					'menu_name'         => __( 'Episode Categories', 'a-ripple-song-podcast' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'podcast-category' ),
			)
		);
	}

	/**
	 * Allow default post tags on episodes.
	 */
	public function register_tags() {
		register_taxonomy_for_object_type( 'post_tag', self::POST_TYPE );
	}

	/**
	 * Default comment status to open for new episodes.
	 *
	 * @param array $data
	 * @param array $postarr
	 * @return array
	 */
	public function set_default_comment_status( $data, $postarr ) {
		if ( isset( $data['post_type'] ) && $data['post_type'] === self::POST_TYPE && empty( $postarr['ID'] ) ) {
			$data['comment_status'] = 'open';
			$data['ping_status']    = 'open';
		}

		return $data;
	}
}
