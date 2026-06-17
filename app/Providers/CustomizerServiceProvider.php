<?php

namespace App\Providers;

use App\Contracts\Customizer;
use App\Customizers;
use Illuminate\Support\ServiceProvider;
use WP_Customize_Manager;

/**
 * Registers theme Customizer settings.
 */
class CustomizerServiceProvider extends ServiceProvider
{
    /**
     * Customizer classes registered by this provider.
     *
     * @var array<int,class-string<Customizer>>
     */
    private array $customizers = [
        Customizers\Copyright::class,
        Customizers\SocialLinks::class,
        Customizers\ThemeColor::class,
    ];

    /**
     * Register Customizer hooks.
     */
    public function register(): void
    {
        add_action('customize_register', [$this, 'registerCustomizer']);
    }

    /**
     * Bootstrap Customizer services.
     */
    public function boot(): void
    {
    }

    /**
     * Register configured Customizer fields.
     *
     * @param WP_Customize_Manager $wpCustomize WordPress Customizer manager.
     */
    public function registerCustomizer(WP_Customize_Manager $wpCustomize): void
    {
        foreach ($this->customizers as $customizerClass) {
            // Each customizer class owns one Customizer section or group of controls.
            $customizer = new $customizerClass();
            $customizer->register($wpCustomize);
        }
    }
}
