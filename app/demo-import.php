<?php

namespace App;

/**
 * One Click Demo Import (OCDI) integration.
 *
 * Provides the theme demo data import definition and basic post-import setup.
 *
 * Plugin: https://wordpress.org/plugins/one-click-demo-import/
 */

add_filter('pt-ocdi/import_files', function (): array {
    $base = 'https://pub-705281646947462fbf6e5906afe11018.r2.dev/data';

    return [[
        'import_file_name' => 'A Ripple Song',
        'import_file_url' => "{$base}/demo-data.xml",
        'import_widget_file_url' => "{$base}/demo-widgets.wie",
        'import_preview_image_url' => get_theme_file_uri('screenshot.png'),
        'preview_url' => home_url('/'),
    ]];
});

add_action('pt-ocdi/after_import', function (): void {
    // Assign menus.
    $primaryMenu = get_term_by('slug', 'menu-1', 'nav_menu');
    if ($primaryMenu instanceof \WP_Term) {
        set_theme_mod('nav_menu_locations', [
            'primary_navigation' => (int) $primaryMenu->term_id,
        ]);
    }

    // Set front page and posts page.
    $frontPage = get_page_by_path('home');
    if ($frontPage instanceof \WP_Post) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $frontPage->ID);
    }

    $postsPage = get_page_by_path('blog');
    if ($postsPage instanceof \WP_Post) {
        update_option('page_for_posts', (int) $postsPage->ID);
    }
});

