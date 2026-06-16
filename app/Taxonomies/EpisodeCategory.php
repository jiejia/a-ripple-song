<?php

namespace App\Taxonomies;

use App\Theme;

/**
 * Defines the podcast episode category taxonomy.
 */
class EpisodeCategory
{
    /**
     * Return the WordPress taxonomy key.
     *
     * @return string
     */
    public function slug(): string
    {
        return Theme::PREFIX . '_episode_category';
    }

    /**
     * Return the singular taxonomy name.
     *
     * @return string
     */
    public function name(): string
    {
        return Theme::NAME_PREFIX . ' ' . __('Episode Category', 'sage');
    }

    /**
     * Return WordPress taxonomy registration arguments.
     *
     * @return array<string,mixed>
     */
    public function args(): array
    {
        return [
            'labels' => [
                'name' => __('Episode Categories', 'sage'),
                'singular_name' => __('Episode Category', 'sage'),
                'search_items' => __('Search Episode Categories', 'sage'),
                'all_items' => __('All Episode Categories', 'sage'),
                'parent_item' => __('Parent Episode Category', 'sage'),
                'parent_item_colon' => __('Parent Episode Category:', 'sage'),
                'edit_item' => __('Edit Episode Category', 'sage'),
                'update_item' => __('Update Episode Category', 'sage'),
                'add_new_item' => __('Add New Episode Category', 'sage'),
                'new_item_name' => __('New Episode Category Name', 'sage'),
                'menu_name' => __('Episode Categories', 'sage'),
            ],
            'public' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'query_var' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'podcast-category'],
        ];
    }
}
