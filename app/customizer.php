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
 * The output hooks need to be registered independently of customize_register,
 * because customize_register only fires in the Customizer context, not on frontend.
 */
if (!is_admin() || wp_doing_ajax()) {
    $customScripts = new CustomScripts();
    add_action('wp_head', [$customScripts, 'outputHeaderScripts'], 999);
    add_action('wp_footer', [$customScripts, 'outputFooterScripts'], 999);
}

