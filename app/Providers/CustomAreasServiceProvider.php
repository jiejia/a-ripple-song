<?php

namespace App\Providers;

use App\Contracts\CustomAreaInterface;
use App\CustomAreas\FooterLinks;
use App\CustomAreas\HomeMain;
use App\CustomAreas\LeftbarPrimary;
use App\CustomAreas\RightbarPrimary;
use App\Theme;
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
        add_action('widgets_init', [$this, 'migrateLegacySidebarWidgets'], 1);
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

    /**
     * Move widget assignments from legacy sidebar IDs to prefixed theme IDs.
     *
     * @return void
     */
    public function migrateLegacySidebarWidgets(): void
    {
        $legacySidebarMap = [
            'home-main' => Theme::SIDEBAR_HOME_MAIN,
            'footer-links' => Theme::SIDEBAR_FOOTER_LINKS,
            'leftbar-primary' => Theme::SIDEBAR_LEFTBAR,
            'rightbar-primary' => Theme::SIDEBAR_PRIMARY,
            'sidebar-primary' => Theme::SIDEBAR_PRIMARY,
        ];

        $sidebarsWidgets = get_option('sidebars_widgets', []);

        if (! is_array($sidebarsWidgets)) {
            return;
        }

        $changed = false;

        foreach ($legacySidebarMap as $legacySidebarId => $prefixedSidebarId) {
            if ($legacySidebarId === $prefixedSidebarId) {
                continue;
            }

            if (empty($sidebarsWidgets[$legacySidebarId]) || ! is_array($sidebarsWidgets[$legacySidebarId])) {
                continue;
            }

            $existingWidgets = $sidebarsWidgets[$prefixedSidebarId] ?? [];

            if (! is_array($existingWidgets)) {
                $existingWidgets = [];
            }

            $sidebarsWidgets[$prefixedSidebarId] = array_values(array_unique(array_merge(
                $existingWidgets,
                $sidebarsWidgets[$legacySidebarId]
            )));

            unset($sidebarsWidgets[$legacySidebarId]);
            $changed = true;
        }

        if ($changed) {
            update_option('sidebars_widgets', $sidebarsWidgets);
        }
    }
}