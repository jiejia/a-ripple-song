<?php

namespace ARippleSong\Podcast\Taxonomies;

use ARippleSong\Podcast\PostTypes\Episode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Episode category taxonomy registration.
 */
class EpisodeCategory {

	/**
	 * Taxonomy key.
	 */
	public const TAXONOMY = 'ars_episode_category';

	/**
	 * Register taxonomy for episode categories.
	 */
	public function register() {
		register_taxonomy(
			self::TAXONOMY,
			Episode::POST_TYPE,
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
	public function registerTags() {
		register_taxonomy_for_object_type( 'post_tag', Episode::POST_TYPE );
	}
}
