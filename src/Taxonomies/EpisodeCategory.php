<?php

namespace ARippleSong\Taxonomies;

use ARippleSong\PostTypes\Episode;

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
					'name'              => __( 'Episode Categories', 'a-ripple-song' ),
					'singular_name'     => __( 'Episode Category', 'a-ripple-song' ),
					'search_items'      => __( 'Search Episode Categories', 'a-ripple-song' ),
					'all_items'         => __( 'All Episode Categories', 'a-ripple-song' ),
					'parent_item'       => __( 'Parent Episode Category', 'a-ripple-song' ),
					'parent_item_colon' => __( 'Parent Episode Category:', 'a-ripple-song' ),
					'edit_item'         => __( 'Edit Episode Category', 'a-ripple-song' ),
					'update_item'       => __( 'Update Episode Category', 'a-ripple-song' ),
					'add_new_item'      => __( 'Add New Episode Category', 'a-ripple-song' ),
					'new_item_name'     => __( 'New Episode Category Name', 'a-ripple-song' ),
					'menu_name'         => __( 'Episode Categories', 'a-ripple-song' ),
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
