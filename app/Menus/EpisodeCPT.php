<?php

namespace App\Menus;

use App\CustomPostTypes\Episode;
use App\Taxonomies\EpisodeCategory;
use App\Theme;

/**
 * Registers the podcast episode custom post type admin menu.
 */
class EpisodeCPT
{
    /**
     * Return the episode top-level admin menu title.
     *
     * @return string
     */
    public function topMenuTitle(): string
    {
        return Theme::NAME_PREFIX . ' ' . __('Episodes', 'a-ripple-song');
    }

    /**
     * Return the episode top-level admin menu slug.
     *
     * @return string
     */
    public function topMenuSlug(): string
    {
        return Theme::PREFIX . '_episodes';
    }

    /**
     * Register the top-level episode menu.
     *
     * @return void
     */
    public function topMenu(): void
    {
        add_menu_page(
            $this->topMenuTitle(),
            $this->topMenuTitle(),
            $this->capability(),
            $this->topMenuSlug(),
            [self::class, 'renderLandingPage'],
            'dashicons-microphone',
            5
        );
        add_action('admin_menu', [self::class, 'removeDuplicateLandingPage'], 999);
        add_filter('parent_file', [self::class, 'filterParentFile']);
        add_filter('submenu_file', [self::class, 'filterSubmenuFile']);
    }

    /**
     * Register episode submenu entries.
     *
     * @return void
     */
    public function subMenu(): void
    {
        $postTypeSlug = $this->postTypeSlug();
        $taxonomySlug = $this->taxonomySlug();

        add_submenu_page($this->topMenuSlug(), __('All Episodes', 'a-ripple-song'), __('All Episodes', 'a-ripple-song'), $this->capability(), 'edit.php?post_type=' . $postTypeSlug);
        add_submenu_page($this->topMenuSlug(), __('Add New Episode', 'a-ripple-song'), __('Add New Episode', 'a-ripple-song'), $this->capability(), 'post-new.php?post_type=' . $postTypeSlug);
        add_submenu_page($this->topMenuSlug(), __('Episode Categories', 'a-ripple-song'), __('Episode Categories', 'a-ripple-song'), 'manage_categories', 'edit-tags.php?taxonomy=' . $taxonomySlug . '&post_type=' . $postTypeSlug);
        add_submenu_page($this->topMenuSlug(), __('Tags', 'a-ripple-song'), __('Tags', 'a-ripple-song'), 'manage_categories', 'edit-tags.php?taxonomy=post_tag&post_type=' . $postTypeSlug);
    }

    /**
     * Redirect direct parent menu visits to the episode list table.
     *
     * @return void
     */
    public static function renderLandingPage(): void
    {
        wp_safe_redirect(admin_url('edit.php?post_type=' . Episode::slug()));
        exit;
    }

    /**
     * Remove the duplicate parent submenu created by WordPress.
     *
     * @return void
     */
    public static function removeDuplicateLandingPage(): void
    {
        $menu = new self();
        remove_submenu_page($menu->topMenuSlug(), $menu->topMenuSlug());
    }

    /**
     * Keep native CPT and taxonomy screens highlighted under the custom episode menu.
     *
     * @param string $parentFile Current parent menu file.
     * @return string
     */
    public static function filterParentFile(string $parentFile): string
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (! $screen || ! self::isEpisodeAdminScreen($screen)) {
            return $parentFile;
        }

        return (new self())->topMenuSlug();
    }

    /**
     * Keep the matching episode submenu highlighted on native WordPress screens.
     *
     * @param string|null $submenuFile Current submenu file.
     * @return string|null
     */
    public static function filterSubmenuFile(?string $submenuFile): ?string
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        $postTypeSlug = Episode::slug();

        if (! $screen || ! self::isEpisodeAdminScreen($screen)) {
            return $submenuFile;
        }

        if ($screen->base === 'post' && $screen->post_type === $postTypeSlug) {
            return 'post-new.php?post_type=' . $postTypeSlug;
        }

        if ($screen->base === 'edit-tags') {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only menu highlighting; no state change.
            $taxonomy = isset($_GET['taxonomy']) ? sanitize_key(wp_unslash($_GET['taxonomy'])) : '';

            return 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . $postTypeSlug;
        }

        return 'edit.php?post_type=' . $postTypeSlug;
    }

    /**
     * Return whether the current admin screen belongs to episode management.
     *
     * @param \WP_Screen $screen Current admin screen object.
     * @return bool
     */
    private static function isEpisodeAdminScreen(\WP_Screen $screen): bool
    {
        $postTypeSlug = Episode::slug();

        if ($screen->post_type === $postTypeSlug) {
            return true;
        }

        if ($screen->base !== 'edit-tags') {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen detection; no state change.
        $requestPostType = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : '';

        return $requestPostType === $postTypeSlug;
    }

    /**
     * Return the capability required to manage episodes.
     *
     * @return string
     */
    private function capability(): string
    {
        return 'edit_posts';
    }

    /**
     * Return the episode custom post type slug.
     *
     * @return string
     */
    private function postTypeSlug(): string
    {
        return Episode::slug();
    }

    /**
     * Return the episode category taxonomy slug.
     *
     * @return string
     */
    private function taxonomySlug(): string
    {
        return (new EpisodeCategory())->slug();
    }
}
