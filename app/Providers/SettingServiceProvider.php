<?php

namespace App\Providers;

use App\Contracts\SettingInterface;
use App\Providers\Settings\Podcast;
use Carbon_Fields\Container;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Setting page classes registered by this provider.
     *
     * @var array<int,class-string<SettingInterface>>
     */
    private array $settings = [Podcast::class];

    /**
     * Register setting services.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('carbon_fields_register_fields', [$this, 'registerSettings']);
    }

    /**
     * Bootstrap setting services.
     *
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * Register all configured Carbon Fields settings pages.
     *
     * @return void
     */
    public function registerSettings(): void
    {
        foreach ($this->settings as $settingClass) {
            // Create a setting page from the configured class.
            $setting = new $settingClass();
            $container = Container::make_theme_options($setting->pageTitle());
            $container->set_page_parent($setting->parentPageSlug());
            $container->set_page_file($setting->pageSlug());
            $container->set_page_menu_title($setting->pageTitle());
            $container->add_fields($setting->fields());
        }
    }
}
