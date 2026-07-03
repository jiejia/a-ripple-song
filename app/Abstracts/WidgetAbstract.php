<?php

namespace App\Abstracts;

/**
 * Base class for theme WordPress widgets.
 */
abstract class WidgetAbstract extends \WP_Widget
{
    /**
     * Return the WordPress widget id base.
     */
    abstract public static function idBase(): string;

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [];
    }

    /**
     * Normalize a widget title while preserving legacy English defaults.
     *
     * @param mixed $value Raw widget title value.
     * @param string $defaultTitle Localized default title.
     * @param array<int,string> $legacyTitles Legacy saved titles that should follow the current locale.
     * @return string
     */
    protected function normalizeWidgetTitle($value, string $defaultTitle, array $legacyTitles = []): string
    {
        $title = is_string($value) ? sanitize_text_field($value) : '';

        if ($title === '' || in_array($title, $legacyTitles, true)) {
            return $defaultTitle;
        }

        return $title;
    }
}
