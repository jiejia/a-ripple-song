<?php

namespace App\Contracts;

interface SettingInterface
{
    /**
     * Return the Carbon Fields page slug.
     *
     * @return string
     */
    public function pageSlug(): string;

    /**
     * Return the settings page title.
     *
     * @return string
     */
    public function pageTitle(): string;

    /**
     * Return all fields for the settings page.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array;

    /**
     * Return default values for the settings page.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array;

    /**
     * Return the field prefix for this settings page.
     *
     * @return string
     */
    public function fieldPrefix(): string;

    /**
     * Return the parent menu slug for this settings page.
     *
     * @return string
     */
    public function parentPageSlug(): string;
}
