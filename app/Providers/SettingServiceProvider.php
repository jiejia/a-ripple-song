<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Settings\DashboardSetting;
use Jiejia\ARippleSong\Settings\PodcastSetting;
use Jiejia\ARippleSong\Plugin;

/**
 * Registers Carbon Fields settings pages.
 */
class SettingServiceProvider extends ServiceProviderAbstract
{
    /**
     * Setting page classes registered by this provider.
     *
     * @var array<int,class-string<SettingAbstract>>
     */
    private array $settings = [
        DashboardSetting::class,
        PodcastSetting::class,
    ];

    /**
     * Register settings pages
     *
     * @return void
     */
    public function register(): void
    {
        // register admin top level menu
        add_action('admin_menu', function (): void {

            // register admin top level menu
            $topPageSlug = Plugin::PREFIX . "dashboard";
            foreach ($this->settings as $settingClass) {
                $setting = new $settingClass();
                if ($setting->isTopLevelMenu()) {
                    $topPageSlug = Plugin::PREFIX . $setting->slug();

                    add_menu_page(
                        __('A Ripple Song', 'a-ripple-song'),                  // page title
                        __('A Ripple Song', 'a-ripple-song'),                  // menu title
                        'manage_options',           // required capability
                        $topPageSlug,                // 菜单 slug
                        [$setting, 'page'], // page callback function
                        'dashicons-admin-generic',  // menu icon
                        81                          // menu position (below Settings)
                    );
                    break;
                }
            }

            // register admin submenu pages
            foreach ($this->settings as $settingClass) {
                $setting = new $settingClass();

                $pageSlug = Plugin::PREFIX . $setting->slug();

                //  Register submenu page
                add_submenu_page(
                    $topPageSlug,                // parent page slug
                    $setting->title(),                  // page title
                    $setting->title(),                  // menu title
                    'manage_options',           // required capability
                    $pageSlug,                // page slug
                    [$setting, 'page']  // page callback function
                );
            }
        });
    }
}