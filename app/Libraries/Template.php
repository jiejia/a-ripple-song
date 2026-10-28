<?php

namespace Jiejia\ARippleSong\Libraries;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Plugin;

/**
 * Renders PHP templates from resources/templates.
 */
final class Template
{
    /**
     * @param string $directory Template root, with trailing slash.
     */
    public function __construct(private readonly string $directory = Plugin::DIR . 'resources/templates/')
    {
    }

    /**
     * Print a template.
     *
     * @param string $name Template path without extension, relative to the root, e.g. "fields/field".
     * @param array<string,mixed> $data Variables made available to the template.
     * @return void
     */
    public function render(string $name, array $data = []): void
    {
        $this->include($this->directory . $name . '.php', $name, $data);
    }

    /**
     * Print a front-end template that themes may override.
     *
     * A theme (or child theme) overrides resources/templates/{name}.php by
     * shipping {theme}/a-ripple-song/{name}.php.
     *
     * @param string $name Template path without extension, e.g. "player/bar".
     * @param array<string,mixed> $data Variables made available to the template.
     * @return void
     */
    public function renderThemeable(string $name, array $data = []): void
    {
        $override = locate_template(Plugin::SLUG . '/' . $name . '.php');

        $this->include($override !== '' ? $override : $this->directory . $name . '.php', $name, $data);
    }

    /**
     * Include a template file in an isolated scope.
     *
     * @param string $file Template file.
     * @param string $name Template name, for the error message.
     * @param array<string,mixed> $data Variables made available to the template.
     * @return void
     */
    private function include(string $file, string $name, array $data): void
    {
        if (! is_file($file)) {
            throw new \InvalidArgumentException(sprintf('Template "%s" does not exist.', esc_html($name)));
        }

        // A static closure gives the template its own scope: it only sees $data, not this object.
        (static function (string $__file, array $__data): void {
            extract($__data, EXTR_SKIP);
            require $__file;
        })($file, $data);
    }
}
