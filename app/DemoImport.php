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
function aripplesong_backup_conflicting_content($selected_import = null) {
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

    // Backup conflicting nav menu items by GUID (OCDI importer dedupes by GUID).
    $import_file_path = null;
    if (is_array($selected_import) && !empty($selected_import['local_import_file'])) {
        $import_file_path = $selected_import['local_import_file'];
    }
    if (!$import_file_path) {
        $import_file_path = get_template_directory() . '/data/demo-data.xml';
    }

    aripplesong_backup_conflicting_nav_menu_items_by_guid($import_file_path);
    
    // Backup conflicting menus
    foreach ($demo_menu_names as $menu_name) {
        $menu = wp_get_nav_menu_object($menu_name);
        if ($menu) {
            aripplesong_backup_menu($menu);
        }
    }
}

/**
 * Backup existing nav_menu_item posts that would be skipped by the importer due to GUID collisions.
 *
 * OCDI's WXR importer v2 can prefill existing posts by GUID (across all post types),
 * which can cause menu items to be treated as already existing and thus skipped.
 *
 * @param string $import_file_path The local demo XML path.
 * @return void
 */
function aripplesong_backup_conflicting_nav_menu_items_by_guid($import_file_path) {
    if (!is_string($import_file_path) || $import_file_path === '' || !file_exists($import_file_path)) {
        return;
    }

    $demo_guids = aripplesong_get_demo_item_guids_by_post_type($import_file_path, ['nav_menu_item']);
    if (empty($demo_guids)) {
        return;
    }

    global $wpdb;

    $placeholders = implode(',', array_fill(0, count($demo_guids), '%s'));
    $sql = $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = 'nav_menu_item'
         AND guid IN ($placeholders)",
        ...$demo_guids
    );

    $menu_item_ids = array_values(array_filter(array_map('intval', (array) $wpdb->get_col($sql))));
    if (empty($menu_item_ids)) {
        return;
    }

    foreach ($menu_item_ids as $menu_item_id) {
        $menu_item = get_post($menu_item_id);
        if ($menu_item) {
            aripplesong_backup_page($menu_item);
        }
    }
}

/**
 * Extract GUIDs from the demo WXR file for specific post types.
 *
 * @param string $import_file_path The local demo XML path.
 * @param array $post_types Post types to include.
 * @return string[] List of GUIDs.
 */
function aripplesong_get_demo_item_guids_by_post_type($import_file_path, $post_types) {
    $post_types = array_values(array_filter(array_map('strval', (array) $post_types)));
    if (empty($post_types)) {
        return [];
    }

    if (!class_exists('XMLReader')) {
        return [];
    }

    $reader = new XMLReader();
    if (!$reader->open($import_file_path)) {
        return [];
    }

    $guids = [];
    $in_item = false;
    $current_post_type = null;
    $current_guid = null;

    while ($reader->read()) {
        if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'item') {
            $in_item = true;
            $current_post_type = null;
            $current_guid = null;
            continue;
        }

        if ($in_item && $reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'item') {
            if ($current_post_type && in_array($current_post_type, $post_types, true) && $current_guid) {
                $guids[] = $current_guid;
            }
            $in_item = false;
            $current_post_type = null;
            $current_guid = null;
            continue;
        }

        if (!$in_item || $reader->nodeType !== XMLReader::ELEMENT) {
            continue;
        }

        if ($reader->name === 'wp:post_type') {
            $current_post_type = trim((string) $reader->readString());
            continue;
        }

        if ($reader->name === 'guid') {
            $current_guid = trim((string) $reader->readString());
            continue;
        }
    }

    $reader->close();

    return array_values(array_filter(array_unique($guids)));
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

    $timestamp = current_time('Ymd-His');
    $slug_suffix = '-bak-' . $timestamp;
    $title_suffix = ' -bak-' . $timestamp;
    $guid_suffix = '#bak-' . $timestamp;

    $current_slug = (string) $page->post_name;
    $current_title = (string) $page->post_title;
    $current_guid = (string) $page->guid;

    $slug_already_backed_up = (bool) preg_match('/-bak-\\d{8}-\\d{6}$/', $current_slug);
    $title_already_backed_up = (bool) preg_match('/\\s-bak-\\d{8}-\\d{6}$/', $current_title);
    $guid_already_backed_up = (bool) preg_match('/#bak-\\d{8}-\\d{6}$/', $current_guid);

    $post_update = [
        'ID' => $page->ID,
    ];

    if (! $slug_already_backed_up) {
        $post_update['post_name'] = $current_slug . $slug_suffix;
    }
    if (! $title_already_backed_up) {
        $post_update['post_title'] = $current_title . $title_suffix;
    }
    if (! $guid_already_backed_up && $current_guid !== '') {
        $post_update['guid'] = $current_guid . $guid_suffix;
    }

    if (count($post_update) === 1) {
        return;
    }

    $result = wp_update_post($post_update, true);

    if (is_wp_error($result)) {
        return;
    }

    global $wpdb;

    if (isset($post_update['post_name'])) {
        $updated_slug = (string) get_post_field('post_name', $page->ID);
        if ($updated_slug !== $post_update['post_name']) {
            $wpdb->update(
                $wpdb->posts,
                ['post_name' => $post_update['post_name']],
                ['ID' => $page->ID],
                ['%s'],
                ['%d']
            );
        }
    }

    if (isset($post_update['post_title'])) {
        $updated_title = (string) get_post_field('post_title', $page->ID);
        if ($updated_title !== $post_update['post_title']) {
            $wpdb->update(
                $wpdb->posts,
                ['post_title' => $post_update['post_title']],
                ['ID' => $page->ID],
                ['%s'],
                ['%d']
            );
        }
    }

    if (isset($post_update['guid'])) {
        $updated_guid = (string) get_post_field('guid', $page->ID);
        if ($updated_guid !== $post_update['guid']) {
            $wpdb->update(
                $wpdb->posts,
                ['guid' => $post_update['guid']],
                ['ID' => $page->ID],
                ['%s'],
                ['%d']
            );
        }
    }

    // Prevent restoring the original slug on untrash, which could reintroduce conflicts.
    delete_post_meta($page->ID, '_wp_desired_post_slug');

    clean_post_cache($page->ID);
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

/**
 * Register legacy podcast CPT/taxonomy used by the bundled demo XML.
 *
 * The demo WXR was generated from an older version where:
 * - post type: `podcast`
 * - taxonomy:  `podcast_category`
 *
 * During import, WordPress only imports terms for registered taxonomies.
 * We register hidden legacy keys so OCDI can import without warnings, then
 * migrate to the plugin keys in `aripplesong_migrate_imported_podcast_content()`.
 */
function aripplesong_register_legacy_podcast_import_types(): void
{
    if (!function_exists('register_post_type') || !function_exists('register_taxonomy')) {
        return;
    }

    if (!post_type_exists('podcast')) {
        register_post_type('podcast', [
            'label' => 'Podcast',
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'has_archive' => false,
            'rewrite' => false,
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'author', 'comments', 'custom-fields', 'revisions'],
        ]);
    }

    if (!taxonomy_exists('podcast_category')) {
        register_taxonomy('podcast_category', ['podcast'], [
            'label' => 'Podcast Categories',
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'show_admin_column' => false,
            'show_in_nav_menus' => false,
            'rewrite' => false,
            'hierarchical' => true,
        ]);
    }
}

/**
 * Migrate imported demo content to the companion podcast plugin slugs.
 *
 * Plugin keys:
 * - post type: `ars_episode`
 * - taxonomy:  `ars_episode_category`
 */
function aripplesong_migrate_imported_podcast_content(): void
{
    $new_post_type = function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
    $new_taxonomy = function_exists('aripplesong_get_podcast_category_taxonomy') ? \aripplesong_get_podcast_category_taxonomy() : null;

    if (!$new_post_type && !$new_taxonomy) {
        return;
    }

    global $wpdb;

    if ($new_post_type) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s",
                $new_post_type,
                'podcast'
            )
        );
    }

    if ($new_taxonomy) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s",
                $new_taxonomy,
                'podcast_category'
            )
        );

        // Refresh term counts for the new taxonomy.
        if (function_exists('get_terms') && function_exists('wp_update_term_count_now')) {
            $term_ids = get_terms([
                'taxonomy' => $new_taxonomy,
                'hide_empty' => false,
                'fields' => 'ids',
            ]);
            if (is_array($term_ids) && !empty($term_ids)) {
                wp_update_term_count_now($term_ids, $new_taxonomy);
            }
        }
    }
}

/**
 * Replace broken remote demo asset URLs (R2) with stable placeholders.
 *
 * The demo XML/widgets reference `*.r2.dev` URLs which may be removed over time.
 * We normalize them so demo sites don't end up with broken banner images/audio.
 */
function aripplesong_normalize_demo_asset_urls(): void
{
    $broken_prefix = 'https://pub-705281646947462fbf6e5906afe11018.r2.dev/';

    $placeholder_image = function_exists('get_theme_file_uri')
        ? (string) get_theme_file_uri('screenshot.png')
        : '';
    $placeholder_audio = 'https://interactive-examples.mdn.mozilla.net/media/cc0-audio/t-rex-roar.mp3';

    // Update Banner Carousel widget slides.
    $widget_option_key = 'widget_banner_carousel_widget';
    $widgets = get_option($widget_option_key, []);
    $changed = false;

    if (is_array($widgets)) {
        foreach ($widgets as $instance_id => $instance) {
            if (!is_array($instance) || empty($instance['slides']) || !is_array($instance['slides'])) {
                continue;
            }

            foreach ($instance['slides'] as $idx => $slide) {
                if (!is_array($slide)) {
                    continue;
                }
                $image = isset($slide['image']) ? (string) $slide['image'] : '';
                if ($image !== '' && strpos($image, $broken_prefix) === 0 && $placeholder_image) {
                    $widgets[$instance_id]['slides'][$idx]['image'] = $placeholder_image;
                    $changed = true;
                }
            }
        }
    }

    if ($changed) {
        update_option($widget_option_key, $widgets);
    }

    // Update imported episode audio_file meta.
    $episode_post_type = function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : 'podcast';
    if (!$episode_post_type) {
        $episode_post_type = 'podcast';
    }

    $episode_ids = get_posts([
        'post_type' => $episode_post_type,
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    foreach ($episode_ids as $post_id) {
        $post_id = (int) $post_id;
        $audio_url = (string) get_post_meta($post_id, 'audio_file', true);
        if ($audio_url !== '' && strpos($audio_url, $broken_prefix) === 0) {
            update_post_meta($post_id, 'audio_file', $placeholder_audio);
            update_post_meta($post_id, 'audio_mime', 'audio/mpeg');
            delete_post_meta($post_id, 'audio_file_id');
        }

        $episode_image = (string) get_post_meta($post_id, 'episode_image', true);
        if ($episode_image !== '' && strpos($episode_image, $broken_prefix) === 0 && $placeholder_image) {
            update_post_meta($post_id, 'episode_image', $placeholder_image);
        }
    }
}
