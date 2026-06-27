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

add_filter('ocdi/import_files', 'aripplesong_register_demo_import_files');
add_filter('ocdi/register_plugins', 'aripplesong_register_demo_import_plugins');
