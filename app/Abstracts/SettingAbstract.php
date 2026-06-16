<?php

namespace App\Abstracts;

use App\Contracts\SettingInterface;

/**
 * Base class for Carbon Fields settings pages.
 */
abstract class SettingAbstract implements SettingInterface
{
    /**
     * Return saved settings merged with defaults.
     *
     * @return array<string,mixed>
     */
    public function getSettings(): array
    {
        // Merge Carbon Fields option values with runtime defaults.
        $settings = $this->defaultSettings();

        foreach (array_keys($settings) as $settingKey) {
            // Read each setting from Carbon Fields when the helper is available.
            $storedValue = function_exists('carbon_get_theme_option')
                ? carbon_get_theme_option($this->fieldName((string) $settingKey))
                : null;

            if ($storedValue !== null && $storedValue !== '') {
                $settings[$settingKey] = $storedValue;
            }
        }

        return $settings;
    }

    /**
     * Return one saved setting.
     *
     * @param string $key Setting key without the theme prefix.
     * @param mixed $default Default value used when the setting does not exist.
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        // Read from the merged settings array for consistent defaults.
        $settings = $this->getSettings();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Return the Carbon Fields option key for a setting.
     *
     * @param string $key Setting key without the theme prefix.
     * @return string
     */
    public function fieldName(string $key): string
    {
        // Prefix every persisted field to avoid option-name collisions.
        return $this->fieldPrefix() . $key;
    }
}
