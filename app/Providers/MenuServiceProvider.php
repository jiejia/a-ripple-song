<?php

namespace App\Providers;

use App\Menus\EpisodeCPT;
use App\Menus\Setting;
use App\Settings\RecommendedPlugins;
use Illuminate\Support\ServiceProvider;

/**
 * Registers theme admin menus.
 */
class MenuServiceProvider extends ServiceProvider
{
    /**
     * Menu classes registered by this provider.
     *
     * @var array<int,class-string>
     */
    private array $menus = [Setting::class, EpisodeCPT::class];

    /**
     * Register admin menu hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Register admin-post handlers before admin-post.php dispatches plugin actions.
        add_action('admin_post_aripplesong_recommended_plugin_install', [RecommendedPlugins::class, 'handleInstallAction']);
        add_action('admin_post_aripplesong_recommended_plugin_activate', [RecommendedPlugins::class, 'handleActivateAction']);

        add_action('admin_menu', [$this, 'registerMenus']);
    }

    /**
     * Register all configured admin menus.
     *
     * @return void
     */
    public function registerMenus(): void
    {
        foreach ($this->menus as $menuClass) {
            // Instantiate each configured menu and let it register its entries.
            $menu = new $menuClass();
            $menu->topMenu();
            $menu->subMenu();
        }
    }
}
