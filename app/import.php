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
 * Assign the imported homepage as the static front page.
 *
 * @param array<string,mixed> $selectedImport Imported demo metadata from OCDI.
 * @return void
 */
function aripplesong_assign_home_front_page(array $selectedImport): void
{
    // Limit the homepage assignment to this theme's predefined demo import.
    if (($selectedImport['import_file_name'] ?? null) !== __('A Ripple Song Demo', 'a-ripple-song')) {
        return;
    }

    // Resolve the imported homepage after content and widget imports are finished.
    $frontPage = aripplesong_resolve_home_front_page();

    if (! $frontPage instanceof \WP_Post) {
        return;
    }

    // Persist the WordPress reading settings so the podcast page becomes the homepage.
    update_option('show_on_front', 'page');
    update_option('page_on_front', $frontPage->ID);
}

add_filter('ocdi/import_files', 'aripplesong_register_demo_import_files');
add_filter('ocdi/register_plugins', 'aripplesong_register_demo_import_plugins');
add_action('ocdi/after_import', 'aripplesong_assign_home_front_page');
