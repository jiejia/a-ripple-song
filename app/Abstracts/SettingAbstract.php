<?php
namespace Jiejia\ARippleSong\Abstracts;


abstract class SettingAbstract
{
    /**
     *  Return whether this settings page should be a top-level menu in the WordPress admin.
     *
     * @return bool
     */
    public function isTopLevelMenu(): bool
    {
        return false;
    }

    /**
     * Return the Carbon Fields page slug.
     *
     * @return string
     */
    abstract public function slug(): string;

    /**
     * Return the settings page title.
     *
     * @return string
     */
    abstract public function title(): string;


    /**
     *  Return the HTML content of the settings page.
     *
     * @return string
     */
    public function page(): string
    {
        return "<div>test</div>";
    }

    /**
     * Return all fields for the settings page.
     *
     * @return array
     */
    abstract public function fields(): array;

}