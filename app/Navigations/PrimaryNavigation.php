<?php

namespace App\Navigations;

use App\Contracts\NavigationInterface;

/**
 * Defines the primary frontend navigation location.
 */
class PrimaryNavigation implements NavigationInterface
{
    /**
     * Return the navigation location slug used in register_nav_menus().
     *
     * @return string
     */
    public function location(): string
    {
        return 'primary_navigation';
    }

    /**
     * Return the human-readable label for the primary navigation location.
     *
     * @return string
     */
    public function label(): string
    {
        return __('Primary Navigation', 'sage');
    }
}
