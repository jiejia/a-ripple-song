<?php
/**
 * One Click Demo Import Configuration
 *
 * Configures the One Click Demo Import plugin to import theme demo content,
 * including automatic setup of menus and homepage after import.
 *
 * @package ARippleSong
 */

/**
 * Define demo import files
 *
 * @return array Demo import configuration
 */
add_filter('ocdi/import_files', function () {
    return [
        [
            'import_file_name'           => 'A Ripple Song Demo',
            'import_file_url'            => get_template_directory_uri() . '/data/demo-data.xml',
            'import_widget_file_url'     => get_template_directory_uri() . '/data/demo-widgets.wie',
            'import_preview_image_url'   => get_template_directory_uri() . '/screenshot.png',
            'preview_url'                => 'https://demo.aripplesong.com/',
            'import_notice'              => __('After importing this demo, please wait for all images and media to be downloaded. This may take a few minutes depending on your server speed.', 'sage'),
        ],
    ];
});

/**
 * Actions to perform after demo import is complete
 *
 * @param array $selected_import Selected demo import data
 */
add_action('ocdi/after_import', function ($selected_import) {
    // Assign "Menu 1" to the primary_navigation location
    aripplesong_assign_menu_to_location();
    
    // Set the homepage to the imported "home" page
    aripplesong_set_static_homepage();
    
    // Flush rewrite rules to ensure permalinks work properly
    flush_rewrite_rules();
});

/**
 * Assign the imported menu to the primary navigation location
 */
function aripplesong_assign_menu_to_location() {
    // Get the menu by name (created during import)
    $menu = wp_get_nav_menu_object('Menu 1');
    
    if (!$menu) {
        // Try getting by slug
        $menu = wp_get_nav_menu_object('menu-1');
    }
    
    if ($menu) {
        // Get current menu locations
        $locations = get_theme_mod('nav_menu_locations', []);
        
        // Assign the menu to primary_navigation
        $locations['primary_navigation'] = $menu->term_id;
        
        // Save the menu locations
        set_theme_mod('nav_menu_locations', $locations);
    }
}

/**
 * Set the "home" page as the static homepage
 */
function aripplesong_set_static_homepage() {
    // Find the "home" page by slug
    $home_page = get_page_by_path('home');
    
    if (!$home_page) {
        // Try finding by title
        $home_page = get_page_by_title('Home');
    }
    
    if ($home_page) {
        // Set the homepage to display a static page
        update_option('show_on_front', 'page');
        
        // Set the front page to our home page
        update_option('page_on_front', $home_page->ID);
    }
    
    // Optionally, find and set a "blog" page for posts if it exists
    $blog_page = get_page_by_path('blog');
    if (!$blog_page) {
        $blog_page = get_page_by_title('Blog');
    }
    
    if ($blog_page) {
        update_option('page_for_posts', $blog_page->ID);
    }
}

/**
 * Disable the intro guide modal for One Click Demo Import
 */
add_filter('ocdi/register_plugins', function ($plugins) {
    return $plugins;
});

/**
 * Change "One Click Demo Import" plugin page location to under Appearance menu
 */
add_filter('ocdi/plugin_page_setup', function ($default_settings) {
    $default_settings['parent_slug'] = 'themes.php';
    $default_settings['page_title']  = __('Import Demo Data', 'sage');
    $default_settings['menu_title']  = __('Import Demo', 'sage');
    $default_settings['capability']  = 'import';
    $default_settings['menu_slug']   = 'one-click-demo-import';
    
    return $default_settings;
});

/**
 * Recommended way to disable branding popup
 */
add_filter('ocdi/disable_pt_branding', '__return_true');
