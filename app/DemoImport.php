<?php
/**
 * One Click Demo Import Helpers
 *
 * Helper functions used by One Click Demo Import (OCDI) hooks registered in
 * `app/setup.php`.
 *
 * @package ARippleSong
 */

/**
 * Backup pages and menus that would conflict with demo import.
 * Renames existing content with '-bak' suffix to avoid ID/slug conflicts.
 * Checks ALL post statuses including trash to ensure clean import.
 * 
 * Note: WordPress appends '__trashed' to slugs when posts are trashed,
 * so we use direct database queries to find all variations.
 */
function aripplesong_backup_conflicting_content() {
    global $wpdb;
    
    // Pages expected from demo import (by slug)
    $demo_page_slugs = ['home', 'podcasts', 'blog'];
    $demo_page_titles = ['home', 'podcasts', 'blog'];
    
    // Menus expected from demo import (by name/slug)
    $demo_menu_names = ['Menu 1', 'menu-1'];
    
    // Backup conflicting pages (includes trash + title-based conflicts).
    $conflicting_page_ids = aripplesong_find_conflicting_page_ids($demo_page_slugs, $demo_page_titles);
    foreach ($conflicting_page_ids as $page_id) {
        $page = get_post($page_id);
        if ($page) {
            aripplesong_backup_page($page);
        }
    }
    
    // Backup conflicting menus
    foreach ($demo_menu_names as $menu_name) {
        $menu = wp_get_nav_menu_object($menu_name);
        if ($menu) {
            aripplesong_backup_menu($menu);
        }
    }
}

/**
 * Find pages that would conflict with demo import.
 *
 * We check:
 * - Slug conflicts: exact slug match and "__trashed" variants
 * - Title conflicts: case-insensitive match (some import flows treat titles as unique)
 *
 * @param array $slugs  Expected demo page slugs (without "__trashed").
 * @param array $titles Expected demo page titles (case-insensitive).
 * @return int[] List of post IDs.
 */
function aripplesong_find_conflicting_page_ids($slugs, $titles) {
    global $wpdb;

    $ids = [];

    $slugs = array_values(array_filter(array_map('strval', (array) $slugs)));
    $titles = array_values(array_filter(array_map('strval', (array) $titles)));

    if (!empty($slugs)) {
        $slug_placeholders = implode(',', array_fill(0, count($slugs), '%s'));

        // Exact slug matches.
        $sql = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'page'
             AND post_name IN ($slug_placeholders)",
            ...$slugs
        );
        $ids = array_merge($ids, (array) $wpdb->get_col($sql));

        // Trash variants: WordPress appends "__trashed" (and sometimes increments).
        foreach ($slugs as $slug) {
            $like = $wpdb->esc_like($slug) . '__%';
            $sql = $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE post_type = 'page'
                 AND post_name LIKE %s",
                $like
            );
            $ids = array_merge($ids, (array) $wpdb->get_col($sql));
        }
    }

    if (!empty($titles)) {
        $titles_lower = array_values(array_unique(array_map('strtolower', $titles)));
        $title_placeholders = implode(',', array_fill(0, count($titles_lower), '%s'));

        $sql = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'page'
             AND LOWER(post_title) IN ($title_placeholders)",
            ...$titles_lower
        );
        $ids = array_merge($ids, (array) $wpdb->get_col($sql));
    }

    $ids = array_values(array_unique(array_map('intval', $ids)));

    return array_values(array_filter($ids));
}

/**
 * Backup a page by renaming its slug and title with a "-bak-YYYYmmdd-HHMMSS" suffix.
 *
 * @param WP_Post $page The page to backup
 */
function aripplesong_backup_page($page) {
    if (! $page instanceof WP_Post) {
        return;
    }

    // Avoid repeatedly backing up the same content across multiple imports.
    if (preg_match('/-bak-\\d{8}-\\d{6}$/', (string) $page->post_name)) {
        return;
    }
    if (preg_match('/\\s-bak-\\d{8}-\\d{6}$/', (string) $page->post_title)) {
        return;
    }

    $timestamp = current_time('Ymd-His');
    $new_slug = (string) $page->post_name . '-bak-' . $timestamp;
    $new_title = (string) $page->post_title . ' -bak-' . $timestamp;

    $result = wp_update_post([
        'ID' => $page->ID,
        'post_name' => $new_slug,
        'post_title' => $new_title,
    ], true);

    if (is_wp_error($result)) {
        return;
    }

    $updated_slug = (string) get_post_field('post_name', $page->ID);
    if ($updated_slug !== $new_slug) {
        global $wpdb;

        $wpdb->update(
            $wpdb->posts,
            ['post_name' => $new_slug],
            ['ID' => $page->ID],
            ['%s'],
            ['%d']
        );

        clean_post_cache($page->ID);
    }
}

/**
 * Backup a menu by renaming it with -bak suffix
 *
 * @param WP_Term $menu The menu term object to backup
 */
function aripplesong_backup_menu($menu) {
    $timestamp = date('Ymd-His');
    $new_name = $menu->name . ' (backup ' . $timestamp . ')';
    $new_slug = $menu->slug . '-bak-' . $timestamp;
    
    wp_update_term($menu->term_id, 'nav_menu', [
        'name' => $new_name,
        'slug' => $new_slug,
    ]);
}

/**
 * Clear all widgets from theme-registered sidebars.
 * Moves existing widgets to inactive widgets area to avoid data loss.
 */
function aripplesong_clear_theme_sidebars() {
    // Get current sidebar widgets
    $sidebars_widgets = get_option('sidebars_widgets', []);
    
    // Define our theme's sidebars that should be cleared
    $theme_sidebars = [
        \App\Theme::SIDEBAR_PRIMARY,
        \App\Theme::SIDEBAR_LEFTBAR,
        \App\Theme::SIDEBAR_HOME_MAIN,
        \App\Theme::SIDEBAR_FOOTER_LINKS,
    ];

    // Get inactive widgets (where we'll move the existing ones)
    $inactive_widgets = isset($sidebars_widgets['wp_inactive_widgets']) 
        ? $sidebars_widgets['wp_inactive_widgets'] 
        : [];

    $moved_any = false;

    foreach ($theme_sidebars as $sidebar_id) {
        if (!empty($sidebars_widgets[$sidebar_id]) && is_array($sidebars_widgets[$sidebar_id])) {
            // Move widgets to inactive area
            $inactive_widgets = array_merge($inactive_widgets, $sidebars_widgets[$sidebar_id]);
            // Clear the sidebar
            $sidebars_widgets[$sidebar_id] = [];
            $moved_any = true;
        }
    }

    if ($moved_any) {
        $sidebars_widgets['wp_inactive_widgets'] = $inactive_widgets;
        update_option('sidebars_widgets', $sidebars_widgets);
    }
}

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
