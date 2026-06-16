<?php

namespace App\Contracts;

interface CustomPostTypeInterface
{
    /**
     * Return the WordPress custom post type key.
     *
     * @return string
     */
    public static function slug(): string;

    /**
     * Return the singular custom post type name.
     *
     * @return string
     */
    public static function singularName(): string;

    /**
     * Return the plural custom post type name.
     *
     * @return string
     */
    public static function pluralName(): string;

    /**
     * Return WordPress registration arguments.
     *
     * @return array<string,mixed>
     */
    public function args(): array;

    /**
     * Return Carbon Fields registered for this post type.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array;

    /**
     * Return the Carbon Fields meta box title.
     *
     * @return string
     */
    public function metaBoxTitle(): string;

    /**
     * Register extra hooks required by this custom post type.
     *
     * @return void
     */
    public function registerHooks(): void;
}
