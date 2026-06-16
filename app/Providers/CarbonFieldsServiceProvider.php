<?php

namespace App\Providers;

use App\CustomPostTypes\Episode;
use App\Theme;
use Carbon_Fields\Carbon_Fields;
use Illuminate\Support\ServiceProvider;

/**
 * Boots the Carbon Fields library for theme-owned fields.
 */
class CarbonFieldsServiceProvider extends ServiceProvider
{
    /**
     * Register the Carbon Fields boot and field registration hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'bootCarbonFields']);
        add_action('carbon_fields_register_fields', [$this, 'registerThemeFields']);
        add_action('current_screen', [$this, 'prioritizeThemeCarbonFieldsAssets']);

        if (did_action('after_setup_theme') && ! did_action('init')) {
            // Boot immediately if Acorn loads after after_setup_theme but before init.
            $this->bootCarbonFields();
        }
    }

    /**
     * Boot Carbon Fields once before WordPress init registers fields.
     *
     * @return void
     */
    public function bootCarbonFields(): void
    {
        if (Carbon_Fields::is_booted()) {
            return;
        }

        Carbon_Fields::boot();
    }

    /**
     * Trigger theme-owned Carbon Fields registration.
     *
     * @return void
     */
    public function registerThemeFields(): void
    {
        do_action('aripplesong_carbon_fields_register_fields');
    }

    /**
     * Reorder this theme's Carbon Fields footer callbacks on theme-owned admin screens.
     *
     * @return void
     */
    public function prioritizeThemeCarbonFieldsAssets(): void
    {
        if (! $this->isThemeCarbonFieldsScreen() || ! Carbon_Fields::is_booted()) {
            return;
        }

        // Re-adding at the same priority moves this theme after other Carbon Fields instances.
        $loader = Carbon_Fields::resolve('loader');
        remove_action('admin_print_footer_scripts', [$loader, 'enqueue_assets'], 9);
        add_action('admin_print_footer_scripts', [$loader, 'enqueue_assets'], 9);
        remove_action('admin_print_footer_scripts', [$loader, 'initialize_ui'], 9999);
        add_action('admin_print_footer_scripts', [$loader, 'initialize_ui'], 9999);
    }

    /**
     * Return whether the current admin screen belongs to this theme's Carbon Fields UI.
     *
     * @return bool
     */
    private function isThemeCarbonFieldsScreen(): bool
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if ($screen && $screen->post_type === Episode::slug()) {
            return true;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen detection; no state change.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        return $page !== '' && str_starts_with($page, Theme::PREFIX);
    }
}
