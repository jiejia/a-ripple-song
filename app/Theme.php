<?php
namespace App;

class Theme
{
    public const SLUG = 'a-ripple-song';

    public const NAME = 'A Ripple Song';

    public const VERSION = '1.0.0';

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