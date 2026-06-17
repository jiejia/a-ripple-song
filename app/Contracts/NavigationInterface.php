<?php

namespace App\Contracts;

/**
 * Defines a frontend navigation location contract.
 */
interface NavigationInterface
{
    /**
     * Return the navigation location slug used in register_nav_menus().
     *
     * @return string
     */
    public function location(): string;

    /**
     * Return the human-readable label for this navigation location.
     *
     * @return string
     */
    public function label(): string;
}
