<?php
namespace Jiejia\ARippleSong\Abstracts;


use Jiejia\ARippleSong\Libraries\Fields\OptionStore;
use Jiejia\ARippleSong\Plugin;

abstract class SettingAbstract
{
    public string $slug;

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
     * Return the settings page title.
     *
     * @return string
     */
    abstract public function title(): string;


    /**
     * Output the settings page; WordPress echoes nothing a menu callback returns.
     *
     * @return void
     */
    public function page(): void
    {
        (new OptionStore($this))->renderPage();
    }

    public function slug(): string
    {
        return Plugin::PREFIX . $this->slug;
    }

    /**
     * Return one saved setting, falling back to the field default.
     *
     * @param string $key Field key.
     * @return mixed Null when no field uses the key.
     */
    public function value(string $key): mixed
    {
        return (new OptionStore($this))->value($key);
    }


    /**
     * Return all fields for the settings page.
     *
     * @return array
     */
    abstract public function fields(): array;

}