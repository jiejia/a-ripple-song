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
        return Theme::NAME_PREFIX . ' ' . __('Episode Category', 'a-ripple-song');
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
                'name' => __('Episode Categories', 'a-ripple-song'),
                'singular_name' => __('Episode Category', 'a-ripple-song'),
                'search_items' => __('Search Episode Categories', 'a-ripple-song'),
                'all_items' => __('All Episode Categories', 'a-ripple-song'),
                'parent_item' => __('Parent Episode Category', 'a-ripple-song'),
                'parent_item_colon' => __('Parent Episode Category:', 'a-ripple-song'),
                'edit_item' => __('Edit Episode Category', 'a-ripple-song'),
                'update_item' => __('Update Episode Category', 'a-ripple-song'),
                'add_new_item' => __('Add New Episode Category', 'a-ripple-song'),
                'new_item_name' => __('New Episode Category Name', 'a-ripple-song'),
                'menu_name' => __('Episode Categories', 'a-ripple-song'),
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
        // Keep the menu panel visible by default for users who have not saved menu screen preferences yet.
        add_filter('default_hidden_meta_boxes', [$this, 'removeNavMenuMetaBoxFromDefaultHidden'], 10, 2);
        // Add the taxonomy meta box explicitly if WordPress does not add it automatically.
        add_action('load-nav-menus.php', [$this, 'registerNavMenuMetaBox']);
        // Remove any previously saved hidden state for the taxonomy menu panel.
        add_action('load-nav-menus.php', [$this, 'showNavMenuMetaBoxForCurrentUser'], 20);
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

    /**
     * Remove the episode category menu panel from default hidden meta boxes.
     *
     * @param array<int,string> $hidden Default hidden meta box ids.
     * @param \WP_Screen $screen Current admin screen object.
     * @return array<int,string>
     */
    public function removeNavMenuMetaBoxFromDefaultHidden(array $hidden, \WP_Screen $screen): array
    {
        if ($screen->id !== 'nav-menus') {
            return $hidden;
        }

        return array_values(array_diff($hidden, [$this->navMenuMetaBoxId()]));
    }

    /**
     * Remove the hidden state for the episode category menu panel on the current user.
     *
     * @return void
     */
    public function showNavMenuMetaBoxForCurrentUser(): void
    {
        $userId = get_current_user_id();

        if ($userId <= 0) {
            return;
        }

        $hidden = get_user_option('metaboxhidden_nav-menus', $userId);

        if (! is_array($hidden) || ! in_array($this->navMenuMetaBoxId(), $hidden, true)) {
            return;
        }

        $hidden = array_values(array_diff($hidden, [$this->navMenuMetaBoxId()]));

        update_user_option($userId, 'metaboxhidden_nav-menus', $hidden, true);
    }

    /**
     * Return the menu screen meta box id used by WordPress.
     *
     * @return string
     */
    private function navMenuMetaBoxId(): string
    {
        return 'add-' . $this->slug();
    }
}
