<?php

namespace App\Customizers;

use App\Abstracts\CustomizerAbstract;
use App\Constants\ThemeConstant;
use App\Theme;
use WP_Customize_Manager;

/**
 * Registers theme color Customizer options.
 */
class ThemeColor extends CustomizerAbstract
{
    /**
     * Return the configured light theme.
     */
    public static function getLightTheme(): string
    {
        return self::sanitizeLightTheme((string) get_theme_mod(self::lightThemeSettingKey(), ThemeConstant::DEFAULT_LIGHT_THEME));
    }

    /**
     * Return the configured dark theme.
     */
    public static function getDarkTheme(): string
    {
        return self::sanitizeDarkTheme((string) get_theme_mod(self::darkThemeSettingKey(), ThemeConstant::DEFAULT_DARK_THEME));
    }

    /**
     * Return supported light theme slugs.
     *
     * @return array<int,string>
     */
    public static function getLightThemeSlugs(): array
    {
        return array_keys(ThemeConstant::LIGHT);
    }

    /**
     * Return supported dark theme slugs.
     *
     * @return array<int,string>
     */
    public static function getDarkThemeSlugs(): array
    {
        return array_keys(ThemeConstant::DARK);
    }

    /**
     * Register theme color Customizer fields.
     *
     * @param WP_Customize_Manager $wpCustomize WordPress Customizer manager.
     */
    public function register(WP_Customize_Manager $wpCustomize): void
    {
        $wpCustomize->add_section(Theme::fieldKey('theme_color'), [
            'title' => __('Theme Color', 'a-ripple-song'),
            'priority' => 180,
        ]);

        $wpCustomize->add_setting(self::lightThemeSettingKey(), [
            'default' => ThemeConstant::DEFAULT_LIGHT_THEME,
            'sanitize_callback' => [self::class, 'sanitizeLightTheme'],
            'transport' => 'refresh',
        ]);

        $wpCustomize->add_control(self::lightThemeSettingKey(), [
            'section' => Theme::fieldKey('theme_color'),
            'label' => __('Light Theme', 'a-ripple-song'),
            'description' => __('This is the default theme used when the site is in light mode.', 'a-ripple-song'),
            'type' => 'select',
            'choices' => ThemeConstant::getLightThemeLabels(),
        ]);

        $wpCustomize->add_setting(self::darkThemeSettingKey(), [
            'default' => ThemeConstant::DEFAULT_DARK_THEME,
            'sanitize_callback' => [self::class, 'sanitizeDarkTheme'],
            'transport' => 'refresh',
        ]);

        $wpCustomize->add_control(self::darkThemeSettingKey(), [
            'section' => Theme::fieldKey('theme_color'),
            'label' => __('Dark Theme', 'a-ripple-song'),
            'description' => __('This is the default theme used when the site is in dark mode.', 'a-ripple-song'),
            'type' => 'select',
            'choices' => ThemeConstant::getDarkThemeLabels(),
        ]);
    }

    /**
     * Sanitize the selected light theme.
     *
     * @param mixed $themeSlug Selected theme slug.
     */
    public static function sanitizeLightTheme($themeSlug): string
    {
        if (! is_string($themeSlug)) {
            return ThemeConstant::DEFAULT_LIGHT_THEME;
        }

        return array_key_exists($themeSlug, ThemeConstant::LIGHT) ? $themeSlug : ThemeConstant::DEFAULT_LIGHT_THEME;
    }

    /**
     * Sanitize the selected dark theme.
     *
     * @param mixed $themeSlug Selected theme slug.
     */
    public static function sanitizeDarkTheme($themeSlug): string
    {
        if (! is_string($themeSlug)) {
            return ThemeConstant::DEFAULT_DARK_THEME;
        }

        return array_key_exists($themeSlug, ThemeConstant::DARK) ? $themeSlug : ThemeConstant::DEFAULT_DARK_THEME;
    }

    /**
     * Return the light theme setting key.
     */
    private static function lightThemeSettingKey(): string
    {
        return Theme::fieldKey('light_theme');
    }

    /**
     * Return the dark theme setting key.
     */
    private static function darkThemeSettingKey(): string
    {
        return Theme::fieldKey('dark_theme');
    }
}
