<?php

namespace App\Contracts;

use WP_Customize_Manager;

/**
 * Defines a theme Customizer registration unit.
 */
interface Customizer
{
    /**
     * Register Customizer settings and controls.
     *
     * @param WP_Customize_Manager $wpCustomize WordPress Customizer manager.
     */
    public function register(WP_Customize_Manager $wpCustomize): void;
}
