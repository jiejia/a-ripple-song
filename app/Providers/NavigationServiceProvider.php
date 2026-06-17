<?php

namespace App\Providers;

use App\Contracts\NavigationInterface;
use App\Navigations\PrimaryNavigation;
use Illuminate\Support\ServiceProvider;

/**
 * Registers frontend navigation menu locations.
 */
class NavigationServiceProvider extends ServiceProvider
{
    /**
     * Navigation classes that define frontend menu locations.
     *
     * @var array<int,class-string<NavigationInterface>>
     */
    private array $navigations = [
        PrimaryNavigation::class,
    ];

    /**
     * Register navigation hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerNavigations'], 20);
    }

    /**
     * Bootstrap navigation services.
     *
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * Register all configured navigation locations.
     *
     * @return void
     */
    public function registerNavigations(): void
    {
        /** @var array<string,string> $locations Navigation locations keyed by slug. */
        $locations = [];

        foreach ($this->navigations as $navigationClass) {
            // Create each navigation definition and collect it for WordPress.
            $navigation = new $navigationClass();
            $locations[$navigation->location()] = $navigation->label();
        }

        register_nav_menus($locations);
    }
}
