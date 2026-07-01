<?php

/**
 * Register predefined demo import files for the theme.
 *
 * @return array<int,array<string,string>>
 */
function aripplesong_register_demo_import_files(): array
{
    // Reuse the theme data directory for all One Click Demo Import assets.
    $dataDirectory = get_theme_file_path('resources/data');
    $contentFile = trailingslashit($dataDirectory) . 'demo.xml';
    $widgetFile = trailingslashit($dataDirectory) . 'demo.wie';
    $importFiles = [
        'import_file_name' => __('A Ripple Song Demo', 'a-ripple-song'),
        'local_import_file' => $contentFile,
        'import_notice' => __(
            'Imports the theme demo posts, pages, navigation, and widgets from the bundled local files.',
            'a-ripple-song'
        ),
    ];

    // Skip the predefined import when the required content file is unavailable.
    if (! file_exists($contentFile)) {
        return [];
    }

    // Attach the widget import file only when the bundled widget data exists.
    if (file_exists($widgetFile)) {
        $importFiles['local_import_widget_file'] = $widgetFile;
    }

    return [
        $importFiles,
    ];
}

/**
 * Register theme plugin suggestions inside One Click Demo Import.
 *
 * @param array<int,array<string,mixed>> $plugins Existing recommended plugins.
 * @return array<int,array<string,mixed>>
 */
function aripplesong_register_demo_import_plugins(array $plugins): array
{
    // Append the theme plugin recommendations to the OCDI plugin installer list.
    $plugins[] = [
        'name' => 'Advanced Media Offloader',
        'slug' => 'advanced-media-offloader',
        'required' => false,
        'preselected' => false,
    ];

    return $plugins;
}

/**
 * Return whether the selected import matches this theme's predefined demo import.
 *
 * @param array<string,mixed> $selectedImport Imported demo metadata from OCDI.
 * @return bool
 */
function aripplesong_is_theme_demo_import(array $selectedImport): bool
{
    return ($selectedImport['import_file_name'] ?? null) === __('A Ripple Song Demo', 'a-ripple-song');
}

/**
 * Resolve the imported homepage and normalize its slug for front-page usage.
 *
 * @return \WP_Post|null
 */
function aripplesong_resolve_home_front_page(): ?\WP_Post
{
    // Prefer an existing page that already uses the target slug.
    $frontPage = get_page_by_path('home', OBJECT, 'page');

    if ($frontPage instanceof \WP_Post) {
        return $frontPage;
    }

    // Fall back to the imported podcast template page when the slug was suffixed during import.
    $podcastPages = get_pages([
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_key' => '_wp_page_template',
        'meta_value' => 'template-podcast.blade.php',
        'number' => 1,
    ]);

    if (empty($podcastPages) || ! $podcastPages[0] instanceof \WP_Post) {
        return null;
    }

    // Rename the imported page back to the desired slug before using it as the homepage.
    $updatedPageId = wp_update_post([
        'ID' => $podcastPages[0]->ID,
        'post_name' => 'home',
    ], true);

    if (is_wp_error($updatedPageId)) {
        return $podcastPages[0];
    }

    $frontPage = get_post($updatedPageId);

    return $frontPage instanceof \WP_Post ? $frontPage : $podcastPages[0];
}

/**
 * Clear the theme's auto-injected homepage widgets before OCDI imports widget data.
 *
 * @param array<string,mixed> $selectedImport Imported demo metadata from OCDI.
 * @return void
 */
function aripplesong_clear_default_home_widgets_before_import(array $selectedImport): void
{
    // Only clear default widgets for the bundled theme demo import flow.
    if (! aripplesong_is_theme_demo_import($selectedImport)) {
        return;
    }

    $homeSidebarId = \App\Theme::SIDEBAR_HOME_MAIN;
    $sidebarsWidgets = get_option('sidebars_widgets', []);

    if (! is_array($sidebarsWidgets)) {
        return;
    }

    $homeWidgets = $sidebarsWidgets[$homeSidebarId] ?? [];

    if (! is_array($homeWidgets) || $homeWidgets === []) {
        update_option('aripplesong_home_widgets_set', false);
        return;
    }

    $widgetOptionMap = [
        'banner_carousel_widget-' => 'widget_banner_carousel_widget',
        'podcast_list_widget-' => 'widget_podcast_list_widget',
        'blog_list_widget-' => 'widget_blog_list_widget',
    ];
    $removedWidgetIds = [];

    // Remove the theme's temporary default widgets from the homepage sidebar before import.
    $sidebarsWidgets[$homeSidebarId] = array_values(array_filter($homeWidgets, function ($widgetId) use ($widgetOptionMap, &$removedWidgetIds) {
        if (! is_string($widgetId)) {
            return true;
        }

        foreach ($widgetOptionMap as $widgetPrefix => $optionName) {
            if (! str_starts_with($widgetId, $widgetPrefix)) {
                continue;
            }

            $removedWidgetIds[$optionName][] = substr($widgetId, strlen($widgetPrefix));

            return false;
        }

        return true;
    }));

    update_option('sidebars_widgets', $sidebarsWidgets);

    // Delete stored widget instances for the temporary defaults so imported widgets get a clean state.
    foreach ($removedWidgetIds as $optionName => $instanceIds) {
        $widgetOptions = get_option($optionName, []);

        if (! is_array($widgetOptions)) {
            continue;
        }

        foreach ($instanceIds as $instanceId) {
            unset($widgetOptions[(int) $instanceId], $widgetOptions[$instanceId]);
        }

        update_option($optionName, $widgetOptions);
    }

    update_option('aripplesong_home_widgets_set', false);
}

/**
 * Return the sidebar IDs that are fully managed by the bundled demo widget file.
 *
 * @return array<int,string>
 */
function aripplesong_demo_widget_sidebar_ids(): array
{
    return [
        \App\Theme::SIDEBAR_HOME_MAIN,
        \App\Theme::SIDEBAR_PRIMARY,
        \App\Theme::SIDEBAR_LEFTBAR,
    ];
}

/**
 * Move the current widgets from a sidebar into Inactive Widgets and clear the sidebar.
 *
 * @param array<string,mixed> $sidebarsWidgets Current WordPress sidebar assignments.
 * @param string $sidebarId Sidebar ID to clear before demo widget import.
 * @return array<string,mixed>
 */
function aripplesong_move_sidebar_widgets_to_inactive(array $sidebarsWidgets, string $sidebarId): array
{
    $sidebarWidgets = $sidebarsWidgets[$sidebarId] ?? [];

    if (! is_array($sidebarWidgets) || $sidebarWidgets === []) {
        return $sidebarsWidgets;
    }

    $inactiveWidgets = $sidebarsWidgets['wp_inactive_widgets'] ?? [];

    if (! is_array($inactiveWidgets)) {
        $inactiveWidgets = [];
    }

    $inactiveWidgets = array_values(array_filter(array_merge($inactiveWidgets, $sidebarWidgets), 'is_string'));
    $sidebarsWidgets['wp_inactive_widgets'] = array_values(array_unique($inactiveWidgets));
    $sidebarsWidgets[$sidebarId] = [];

    return $sidebarsWidgets;
}

/**
 * Clear sidebars that will be replaced by the bundled demo widget import file.
 *
 * @param array<string,mixed> $selectedImport Imported demo metadata from OCDI.
 * @return void
 */
function aripplesong_prepare_demo_widget_sidebars_before_import(array $selectedImport): void
{
    // Limit sidebar cleanup to the bundled theme demo import flow.
    if (! aripplesong_is_theme_demo_import($selectedImport)) {
        return;
    }

    // Remove the activation-time homepage defaults before clearing the managed sidebars.
    aripplesong_clear_default_home_widgets_before_import($selectedImport);

    $sidebarsWidgets = get_option('sidebars_widgets', []);

    if (! is_array($sidebarsWidgets)) {
        return;
    }

    foreach (aripplesong_demo_widget_sidebar_ids() as $sidebarId) {
        $sidebarsWidgets = aripplesong_move_sidebar_widgets_to_inactive($sidebarsWidgets, $sidebarId);
    }

    update_option('sidebars_widgets', $sidebarsWidgets);
}

/**
 * Resolve the imported navigation menu that should be assigned to Primary Navigation.
 *
 * @return \WP_Term|null
 */
function aripplesong_resolve_primary_navigation_menu(): ?\WP_Term
{
    // Prefer the menu bundled in demo.xml by its stable imported slug.
    $primaryMenu = wp_get_nav_menu_object('ars-primary-navigation');

    if ($primaryMenu instanceof \WP_Term) {
        return $primaryMenu;
    }

    $primaryMenu = wp_get_nav_menu_object('ARS Primary Navigation');

    if ($primaryMenu instanceof \WP_Term) {
        return $primaryMenu;
    }

    // Keep the previous demo menu identifier as a compatibility fallback.
    $primaryMenu = wp_get_nav_menu_object('menu-1');

    if ($primaryMenu instanceof \WP_Term) {
        return $primaryMenu;
    }

    $primaryMenu = wp_get_nav_menu_object('Menu 1');

    if ($primaryMenu instanceof \WP_Term) {
        return $primaryMenu;
    }

    $menus = wp_get_nav_menus([
        'orderby' => 'term_id',
        'order' => 'ASC',
    ]);

    foreach ($menus as $menu) {
        if ($menu instanceof \WP_Term && (int) $menu->count > 0) {
            return $menu;
        }
    }

    return null;
}

/**
 * Bind the imported menu to the Primary Navigation theme location.
 *
 * @param array<string,mixed> $selectedImport Imported demo metadata from OCDI.
 * @return void
 */
function aripplesong_assign_primary_navigation_menu(array $selectedImport): void
{
    // Only bind menus for the bundled theme demo import flow.
    if (! aripplesong_is_theme_demo_import($selectedImport)) {
        return;
    }

    $primaryMenu = aripplesong_resolve_primary_navigation_menu();

    if (! $primaryMenu instanceof \WP_Term) {
        return;
    }

    $menuLocations = get_theme_mod('nav_menu_locations', []);

    if (! is_array($menuLocations)) {
        $menuLocations = [];
    }

    $menuLocations['primary_navigation'] = (int) $primaryMenu->term_id;

    set_theme_mod('nav_menu_locations', $menuLocations);
}

/**
 * Normalize imported custom home links so demo navigation points to the current site root.
 *
 * @param array<string,mixed> $selectedImport Imported demo metadata from OCDI.
 * @return void
 */
function aripplesong_normalize_primary_navigation_home_link(array $selectedImport): void
{
    // Limit the menu-link normalization to this theme's predefined demo import.
    if (! aripplesong_is_theme_demo_import($selectedImport)) {
        return;
    }

    $primaryMenu = aripplesong_resolve_primary_navigation_menu();

    if (! $primaryMenu instanceof \WP_Term) {
        return;
    }

    $menuItems = wp_get_nav_menu_items($primaryMenu->term_id, [
        'post_status' => 'any',
    ]);

    if (! is_array($menuItems) || $menuItems === []) {
        return;
    }

    foreach ($menuItems as $menuItem) {
        if (! $menuItem instanceof \WP_Post) {
            continue;
        }

        $menuItemTitle = trim(wp_strip_all_tags((string) $menuItem->post_title));
        $menuItemType = get_post_meta($menuItem->ID, '_menu_item_type', true);
        $menuItemObject = get_post_meta($menuItem->ID, '_menu_item_object', true);
        $menuItemUrl = get_post_meta($menuItem->ID, '_menu_item_url', true);

        if ($menuItemTitle !== 'Home' || $menuItemType !== 'custom' || $menuItemObject !== 'custom') {
            continue;
        }

        // Force the imported Home entry to stay portable across local domains and environments.
        if ($menuItemUrl !== '/') {
            update_post_meta($menuItem->ID, '_menu_item_url', '/');
        }

        break;
    }
}

/**
 * Assign the imported homepage as the static front page.
 *
 * @param array<string,mixed> $selectedImport Imported demo metadata from OCDI.
 * @return void
 */
function aripplesong_assign_home_front_page(array $selectedImport): void
{
    // Limit the homepage assignment to this theme's predefined demo import.
    if (! aripplesong_is_theme_demo_import($selectedImport)) {
        return;
    }

    // Resolve the imported homepage after content and widget imports are finished.
    $frontPage = aripplesong_resolve_home_front_page();

    if (! $frontPage instanceof \WP_Post) {
        return;
    }

    // Persist the WordPress reading settings so the imported page becomes the homepage.
    update_option('show_on_front', 'page');
    update_option('page_on_front', $frontPage->ID);
    update_option('aripplesong_home_widgets_set', true);
}

add_filter('ocdi/import_files', 'aripplesong_register_demo_import_files');
add_filter('ocdi/register_plugins', 'aripplesong_register_demo_import_plugins');
add_action('ocdi/before_widgets_import', 'aripplesong_prepare_demo_widget_sidebars_before_import');
add_action('ocdi/after_import', 'aripplesong_assign_primary_navigation_menu');
add_action('ocdi/after_import', 'aripplesong_normalize_primary_navigation_home_link');
add_action('ocdi/after_import', 'aripplesong_assign_home_front_page');
