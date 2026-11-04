<?php

namespace App\Providers;

defined('ABSPATH') || exit;

use App\Abstracts\ServiceProviderAbstract;
use App\Abstracts\SettingAbstract;
use App\Libraries\Fields\OptionStore;
use App\Settings\DashboardSetting;
use App\Settings\MemberSetting;
use App\Settings\PlayerSetting;
use App\Settings\PodcastSetting;
use App\Settings\SubscriptionSetting;
use App\App;

/**
 * Registers plugin settings pages.
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
        MemberSetting::class,
        SubscriptionSetting::class,
        PlayerSetting::class,
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
            $topPageSlug = App::PREFIX . "dashboard";
            foreach ($this->settings as $settingClass) {
                $setting = new $settingClass();
                if ($setting->isTopLevelMenu()) {
                    $topPageSlug = App::PREFIX . $setting->slug;

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

                $pageSlug = $setting->slug();

                //  Register submenu page
                add_submenu_page(
                    $topPageSlug,                // parent page slug
                    $setting->title(),                  // page title
                    $setting->title(),                  // menu title
                    'manage_options',           // required capability
                    $pageSlug,                // page slug
                    // The top-level page already renders through add_menu_page(); a second callback would print it twice.
                    $pageSlug === $topPageSlug ? '' : [$setting, 'page']
                );
            }
        });

        foreach ($this->settings as $settingClass) {
            // Each settings page saves its fields into a single option.
            (new OptionStore(new $settingClass()))->register();
        }
    }
}