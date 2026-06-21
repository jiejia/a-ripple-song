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
}
