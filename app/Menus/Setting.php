<?php

namespace App\Menus;

use App\Providers\Settings\Podcast;
use App\Theme;

/**
 * Registers the theme settings admin menu.
 */
class Setting
{
    /**
     * Return the settings top-level admin menu title.
     *
     * @return string
     */
    public function topMenuTitle(): string
    {
        return __('A Ripple Song', 'sage');
    }

    /**
     * Return the settings top-level admin menu slug.
     *
     * @return string
     */
    public function topMenuSlug(): string
    {
        return Theme::SLUG;
    }

    /**
     * Register the theme top-level settings menu.
     *
     * @return void
     */
    public function topMenu(): void
    {
        // Add the parent menu used by Carbon Fields setting containers.
        add_menu_page(
            $this->topMenuTitle(),
            $this->topMenuTitle(),
            'manage_options',
            $this->topMenuSlug(),
            [self::class, 'renderLandingPage'],
            'dashicons-admin-settings',
            60
        );
        add_action('admin_menu', [self::class, 'removeDuplicateLandingPage'], 999);
    }

    /**
     * Keep submenu registration delegated to Carbon Fields containers.
     *
     * @return void
     */
    public function subMenu(): void
    {
        // Podcast settings are registered from App\Settings\Podcast before Carbon Fields boots.
    }

    /**
     * Redirect direct parent menu visits to the podcast settings page.
     *
     * @return void
     */
    public static function renderLandingPage(): void
    {
        // Send users to the first available settings page.
        wp_safe_redirect(admin_url('admin.php?page=' . (new Podcast())->pageSlug()));
        exit;
    }

    /**
     * Remove the duplicate submenu WordPress creates for top-level pages.
     *
     * @return void
     */
    public static function removeDuplicateLandingPage(): void
    {
        // Keep the settings menu concise.
        remove_submenu_page(Theme::SLUG, Theme::SLUG);
    }
}
