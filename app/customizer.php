<?php

/**
 * Theme Customizer settings.
 * 
 * This file serves as the entry point for all Customizer modules.
 * Each module is a separate class in the Customizer directory.
 */

namespace App;

use App\Customizer\CustomScripts;

/**
 * Register all Customizer modules.
 *
 * To add a new module:
 * 1. Create a new class in app/Customizer/ directory
 * 2. Add the class to the $modules array below
 * 3. Implement the register($wp_customize) method in your class
 *
 * @return void
 */
add_action('customize_register', function ($wp_customize) {
    // List of Customizer modules to register
    $modules = [
        CustomScripts::class,
        // Add future modules here:
        // SocialLinks::class,
        // ThemeColors::class,
    ];

    foreach ($modules as $module) {
        if (class_exists($module)) {
            (new $module)->register($wp_customize);
        }
    }
});

/**
 * Output custom scripts in the frontend.
 * 
 * Each module is responsible for hooking its own output methods.
 * This is handled in the module's register() method.
 */

