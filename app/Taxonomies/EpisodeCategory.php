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

    /**
     * Register hooks required by the taxonomy.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        // Ensure the taxonomy is available in Appearance > Menus.
        add_filter('nav_menu_meta_box_object', [$this, 'enableNavMenuMetaBox']);
        // Add the taxonomy meta box explicitly if WordPress does not add it automatically.
        add_action('load-nav-menus.php', [$this, 'registerNavMenuMetaBox']);
    }

    /**
     * Ensure the episode category taxonomy can be selected on the menu editor screen.
     *
     * @param mixed $taxonomy Current menu meta box object.
     * @return mixed
     */
    public function enableNavMenuMetaBox($taxonomy)
    {
        if (! $taxonomy instanceof \WP_Taxonomy || $taxonomy->name !== $this->slug()) {
            return $taxonomy;
        }

        $taxonomy->show_in_nav_menus = true;

        return $taxonomy;
    }

    /**
     * Register the episode category meta box on Appearance > Menus.
     *
     * @return void
     */
    public function registerNavMenuMetaBox(): void
    {
        $taxonomy = get_taxonomy($this->slug());

        if (! $taxonomy instanceof \WP_Taxonomy) {
            return;
        }

        // Reuse WordPress core taxonomy menu item UI for consistent search and selection behavior.
        add_meta_box(
            'add-' . $taxonomy->name,
            $taxonomy->labels->name,
            'wp_nav_menu_item_taxonomy_meta_box',
            'nav-menus',
            'side',
            'default',
            $taxonomy
        );
    }
}
