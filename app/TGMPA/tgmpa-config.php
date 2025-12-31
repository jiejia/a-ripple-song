<?php
/**
 * TGM Plugin Activation Configuration
 *
 * This file handles the registration of required and recommended plugins
 * for the A Ripple Song theme using the TGM Plugin Activation library.
 *
 * @package ARippleSong
 */

namespace App\TGMPA;

/**
 * Include the TGM_Plugin_Activation class.
 * This file should only be loaded within WordPress context.
 */
require_once __DIR__ . '/class-tgm-plugin-activation.php';

/**
 * Register the required plugins for this theme.
 *
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */
add_action('tgmpa_register', function () {
    /*
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = [
        // One Click Demo Import - for importing theme demo content
        [
            'name'     => 'One Click Demo Import',
            'slug'     => 'one-click-demo-import',
            'required' => false, // Recommended but not required
        ],

        // Example: WordPress.org repository plugin
        // [
        //     'name'     => 'Carbon Fields',
        //     'slug'     => 'carbon-fields',
        //     'required' => true,
        // ],

        // Example: Plugin from an arbitrary external source
        // [
        //     'name'         => 'TGM Example Plugin',
        //     'slug'         => 'tgm-example-plugin',
        //     'source'       => 'https://example.com/plugins/tgm-example-plugin.zip',
        //     'required'     => true,
        //     'external_url' => 'https://example.com/plugins/tgm-example-plugin/',
        // ],

        // Example: Plugin included with the theme
        // [
        //     'name'     => 'A Ripple Song Core',
        //     'slug'     => 'aripplesong-core',
        //     'source'   => get_template_directory() . '/plugins/aripplesong-core.zip',
        //     'required' => true,
        //     'version'  => '1.0.0',
        // ],
    ];

    /*
     * Array of configuration settings. Amend each line as needed.
     *
     * TGMPA will start providing localized text strings soon. If you already have translations of our standard
     * strings available, please help us make TGMPA even better by giving us access to these translations or by
     * sending in a pull-request with .po file(s) with the translations.
     *
     * Only uncomment the strings in the config array if you want to customize the strings.
     */
    $config = [
        'id'           => 'aripplesong',           // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to bundled plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'parent_slug'  => 'themes.php',            // Parent menu slug.
        'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.

        /*
        'strings'      => array(
            'page_title'                      => __( 'Install Required Plugins', 'sage' ),
            'menu_title'                      => __( 'Install Plugins', 'sage' ),
            'installing'                      => __( 'Installing Plugin: %s', 'sage' ),
            'updating'                        => __( 'Updating Plugin: %s', 'sage' ),
            'oops'                            => __( 'Something went wrong with the plugin API.', 'sage' ),
            'notice_can_install_required'     => _n_noop(
                'This theme requires the following plugin: %1$s.',
                'This theme requires the following plugins: %1$s.',
                'sage'
            ),
            'notice_can_install_recommended'  => _n_noop(
                'This theme recommends the following plugin: %1$s.',
                'This theme recommends the following plugins: %1$s.',
                'sage'
            ),
            'notice_ask_to_update'            => _n_noop(
                'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
                'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
                'sage'
            ),
            'notice_ask_to_update_maybe'      => _n_noop(
                'There is an update available for: %1$s.',
                'There are updates available for the following plugins: %1$s.',
                'sage'
            ),
            'notice_can_activate_required'    => _n_noop(
                'The following required plugin is currently inactive: %1$s.',
                'The following required plugins are currently inactive: %1$s.',
                'sage'
            ),
            'notice_can_activate_recommended' => _n_noop(
                'The following recommended plugin is currently inactive: %1$s.',
                'The following recommended plugins are currently inactive: %1$s.',
                'sage'
            ),
            'install_link'                    => _n_noop(
                'Begin installing plugin',
                'Begin installing plugins',
                'sage'
            ),
            'update_link'                     => _n_noop(
                'Begin updating plugin',
                'Begin updating plugins',
                'sage'
            ),
            'activate_link'                   => _n_noop(
                'Begin activating plugin',
                'Begin activating plugins',
                'sage'
            ),
            'return'                          => __( 'Return to Required Plugins Installer', 'sage' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'sage' ),
            'activated_successfully'          => __( 'The following plugin was activated successfully:', 'sage' ),
            'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'sage' ),
            'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'sage' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'sage' ),
            'dismiss'                         => __( 'Dismiss this notice', 'sage' ),
            'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'sage' ),
            'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'sage' ),

            'nag_type'                        => '', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
        ),
        */
    ];

    tgmpa($plugins, $config);
});
