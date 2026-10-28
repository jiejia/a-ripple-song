<?php

namespace Jiejia\ARippleSong\Libraries;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Plugin;

/**
 * Inline Lucide icons from resources/assets/icons (copied there by `npm run build:icons`).
 *
 * Icons are printed as SVG markup, so they need no JavaScript and survive swup page swaps.
 */
final class Icon
{
    /**
     * Loaded SVG markup keyed by icon name.
     *
     * @var array<string,string>
     */
    private static array $cache = [];

    /**
     * Return an icon as inline SVG.
     *
     * @param string $name Lucide icon name, e.g. "play".
     * @param string $class CSS classes; the icon is sized by them and colored by currentColor.
     * @return string Empty when the icon was not copied.
     */
    public static function svg(string $name, string $class = ''): string
    {
        self::$cache[$name] ??= self::load($name);

        if (self::$cache[$name] === '') {
            return '';
        }

        $attributes = Html::attributes(['class' => $class !== '' ? $class : null, 'aria-hidden' => 'true', 'focusable' => 'false']);

        return (string) preg_replace('/^<svg\b/', '<svg' . $attributes, self::$cache[$name], 1);
    }

    /**
     * Print an icon as inline SVG.
     *
     * @param string $name Lucide icon name.
     * @param string $class CSS classes.
     * @return void
     */
    public static function render(string $name, string $class = ''): void
    {
        echo self::svg($name, $class); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG shipped with the plugin.
    }

    /**
     * Read an icon file.
     *
     * @param string $name Lucide icon name.
     * @return string
     */
    private static function load(string $name): string
    {
        $file = Plugin::DIR . 'resources/assets/icons/' . sanitize_file_name($name) . '.svg';

        return is_readable($file) ? trim((string) file_get_contents($file)) : '';
    }
}
