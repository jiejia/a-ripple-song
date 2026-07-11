<?php
namespace App;

class Theme
{
    public const SLUG = 'a-ripple-song';

    public const NAME = 'A Ripple Song';

    /**
     * Theme text domain; must match the Text Domain header in style.css.
     */
    public const TEXT_DOMAIN = 'a-ripple-song';

    /**
     * Public source repository URL used for theme attribution links.
     */
    public const REPOSITORY_URI = 'https://github.com/jiejia/a-ripple-song';

    public const VERSION = '0.5.0';

    /**
     * Return the theme version from style.css at runtime.
     */
    public static function version(): string
    {
        static $version = null;

        if ($version === null) {
            $version = function_exists('wp_get_theme')
                ? (string) wp_get_theme()->get('Version')
                : self::VERSION;
        }

        return $version ?: self::VERSION;
    }

    public const DIR = A_RIPPLE_SONG_THEME_DIR;

    public const PREFIX = 'aripplesong';

    public const NAME_PREFIX = 'ARS';

    public const SIDEBAR_PRIMARY = self::PREFIX . '-sidebar-primary';

    public const SIDEBAR_LEFTBAR = self::PREFIX . '-leftbar-primary';

    public const SIDEBAR_HOME_MAIN = self::PREFIX . '-home-main';

    public const SIDEBAR_FOOTER_LINKS = self::PREFIX . '-footer-links';

    /**
     * Return the common theme prefix.
     */
    public static function prefix(): string
    {
        return self::PREFIX;
    }

    /**
     * Build a prefixed identifier for theme-owned IDs.
     */
    public static function prefixed(string $id): string
    {
        return self::PREFIX . '-' . $id;
    }

    /**
     * Build a prefixed Customizer or option field key.
     */
    public static function fieldKey(string $key): string
    {
        return self::PREFIX . '_' . $key;
    }

    /**
     * Return registered theme sidebar IDs.
     *
     * @return array<string, string>
     */
    public static function sidebars(): array
    {
        return [
            'sidebar-primary' => self::SIDEBAR_PRIMARY,
            'leftbar-primary' => self::SIDEBAR_LEFTBAR,
            'home-main' => self::SIDEBAR_HOME_MAIN,
            'footer-links' => self::SIDEBAR_FOOTER_LINKS,
        ];
    }
}