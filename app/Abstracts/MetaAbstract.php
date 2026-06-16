<?php

namespace App\Abstracts;

use App\Theme;

/**
 * Base class for theme post meta features.
 */
abstract class MetaAbstract
{
    /**
     * Return a prefixed top-level field key for shared meta.
     *
     * @param string $key Raw field key.
     * @return string
     */
    public static function fieldKey(string $key): string
    {
        return Theme::PREFIX . '_' . ltrim($key, '_');
    }

    /**
     * Return the stored private post meta key for shared meta.
     *
     * @param string $key Raw field key.
     * @return string
     */
    public static function storedFieldKey(string $key): string
    {
        return '_' . static::fieldKey($key);
    }
}
