<?php

namespace Jiejia\ARippleSong\Libraries;

defined('ABSPATH') || exit;

/**
 * Small markup helpers used by templates.
 */
final class Html
{
    /**
     * Build an escaped attribute string with a leading space.
     *
     * Null and false values are skipped, true renders a boolean attribute.
     *
     * @param array<string,mixed> $attributes Attribute name => value.
     * @param (callable(string):string)|null $escape Escapes values, esc_attr by default; e.g. esc_url for URL-only attributes.
     * @return string
     */
    public static function attributes(array $attributes, ?callable $escape = null): string
    {
        $escape ??= esc_attr(...);
        $html = '';

        foreach ($attributes as $name => $value) {
            $html .= match (true) {
                $value === null, $value === false => '',
                $value === true => ' ' . esc_attr($name),
                default => sprintf(' %s="%s"', esc_attr($name), $escape((string) $value)),
            };
        }

        return $html;
    }

    /**
     * Print attributes, escaping each name and value at the point of output.
     *
     * @param array<string,mixed> $attributes Attribute name => value.
     * @param bool $urls Whether all values are URLs.
     * @return void
     */
    public static function renderAttributes(array $attributes, bool $urls = false): void
    {
        foreach ($attributes as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            echo ' ' . esc_attr($name);
            if ($value !== true) {
                echo '="';
                if ($urls) {
                    echo esc_url((string) $value);
                } else {
                    echo esc_attr((string) $value);
                }
                echo '"';
            }
        }
    }

    /**
     * Join CSS classes; string keys are added only when their value is truthy.
     *
     * @param array<int|string,mixed> $classes e.g. ['ars-field', 'is-required' => $required].
     * @return string
     */
    public static function classes(array $classes): string
    {
        $list = [];

        foreach ($classes as $class => $enabled) {
            if (is_int($class)) {
                $list[] = (string) $enabled;
            } elseif ($enabled) {
                $list[] = $class;
            }
        }

        return implode(' ', $list);
    }
}
