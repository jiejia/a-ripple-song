<?php

namespace App\Providers;

use App\Contracts\CustomAreaInterface;
use App\CustomAreas\FooterLinks;
use App\CustomAreas\HomeMain;
use App\CustomAreas\LeftbarPrimary;
use App\CustomAreas\RightbarPrimary;
use Illuminate\Support\ServiceProvider;

/**
 * Registers theme widget areas.
 */
class CustomAreasServiceProvider extends ServiceProvider
{
    /**
     * Custom area classes registered by this provider.
     *
     * @var array<int,class-string<CustomAreaInterface>>
     */
    private array $customAreas = [
        FooterLinks::class,
        HomeMain::class,
        RightbarPrimary::class,
        LeftbarPrimary::class,
    ];

    /**
     * Register custom area hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('widgets_init', [$this, 'registerCustomAreas']);
    }

    /**
     * Bootstrap custom area services.
     *
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * Register all configured widget areas.
     *
     * @return void
     */
    public function registerCustomAreas(): void
    {
        foreach ($this->customAreas as $customAreaClass) {
            // Build each custom area definition before registering it with WordPress.
            $customArea = new $customAreaClass();

            register_sidebar($customArea->args());
        }
    }
}
