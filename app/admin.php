<?php

/**
 * Admin UX helpers.
 */

namespace App;

use function add_action;

if (! is_admin()) {
    return;
}

const ARIPPLESONG_RECOMMENDED_PLUGINS_NOTICE_META_KEY = 'aripplesong_dismissed_recommended_plugins_notice';

add_action('admin_init', function (): void {
    if (! isset($_GET['aripplesong_dismiss_recommended_plugins_notice'])) {
        return;
    }

    if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'aripplesong_dismiss_recommended_plugins_notice')) {
        return;
    }

    $userId = get_current_user_id();
    if ($userId) {
        update_user_meta($userId, ARIPPLESONG_RECOMMENDED_PLUGINS_NOTICE_META_KEY, 1);
    }

    $redirect = remove_query_arg(['aripplesong_dismiss_recommended_plugins_notice', '_wpnonce']);
    wp_safe_redirect(is_string($redirect) ? $redirect : admin_url('themes.php'));
    exit;
});

add_action('admin_notices', function (): void {
    if (! current_user_can('install_plugins') && ! current_user_can('activate_plugins')) {
        return;
    }

    if (! aripplesong_is_active_theme()) {
        return;
    }

    if (! aripplesong_is_recommended_plugins_notice_screen()) {
        return;
    }

    if (get_user_meta(get_current_user_id(), ARIPPLESONG_RECOMMENDED_PLUGINS_NOTICE_META_KEY, true)) {
        return;
    }

    $plugins = aripplesong_get_recommended_plugins();

    $missing = array_filter($plugins, static function (array $plugin): bool {
        return empty($plugin['active']);
    });

    if (! $missing) {
        return;
    }

    $dismissUrl = wp_nonce_url(
        add_query_arg('aripplesong_dismiss_recommended_plugins_notice', '1'),
        'aripplesong_dismiss_recommended_plugins_notice'
    );

    echo '<div class="notice notice-info">';
    echo '<p><strong>' . esc_html__('Optional plugins', 'a-ripple-song') . '</strong></p>';
    echo '<p>' . esc_html__('For the full A Ripple Song experience (podcast features and demo import), we recommend installing the following free plugins from WordPress.org:', 'a-ripple-song') . '</p>';
    echo '<ul style="margin-left: 1.2em; list-style: disc;">';

    foreach ($plugins as $plugin) {
        echo '<li>';
        echo '<strong>' . esc_html($plugin['name']) . '</strong> — ' . esc_html($plugin['description']) . ' ';

        if (! empty($plugin['action'])) {
            echo $plugin['action'];
        } else {
            echo '<span style="color: #1d2327;">' . esc_html__('Active', 'a-ripple-song') . '</span>';
        }

        echo '</li>';
    }

    echo '</ul>';
    echo '<p style="margin-top: 10px;">';
    echo '<a href="' . esc_url($dismissUrl) . '" class="button button-secondary">' . esc_html__('Dismiss', 'a-ripple-song') . '</a>';
    echo '</p>';
    echo '<p class="description" style="margin-top: 0;">' . esc_html__('You can keep using the theme without these plugins.', 'a-ripple-song') . '</p>';
    echo '</div>';
});

/**
 * @return array<string, array{name: string, description: string, slug: string, plugin_file: string|null, installed: bool, active: bool, action: string}>
 */
function aripplesong_get_recommended_plugins(): array
{
    $definitions = [
        [
            'slug' => 'a-ripple-song-podcast',
            'name' => __('A Ripple Song Podcast', 'a-ripple-song'),
            'description' => __('Adds the episode post type and podcast RSS feed used by this theme.', 'a-ripple-song'),
            'expected_file' => 'a-ripple-song-podcast/a-ripple-song-podcast.php',
        ],
        [
            'slug' => 'one-click-demo-import',
            'name' => __('One Click Demo Import', 'a-ripple-song'),
            'description' => __('Imports demo content and widgets (Appearance → Import Demo Data).', 'a-ripple-song'),
            'expected_file' => 'one-click-demo-import/one-click-demo-import.php',
        ],
    ];

    $plugins = [];
    foreach ($definitions as $definition) {
        $slug = $definition['slug'];
        $pluginFile = aripplesong_find_plugin_file($slug, $definition['expected_file']);

        $installed = is_string($pluginFile) && file_exists(WP_PLUGIN_DIR . '/' . $pluginFile);
        $active = $installed && aripplesong_is_plugin_active($pluginFile);

        $action = '';
        if (! $active) {
            if (! $installed && current_user_can('install_plugins')) {
                $installUrl = wp_nonce_url(
                    self_admin_url('update.php?action=install-plugin&plugin=' . $slug),
                    'install-plugin_' . $slug
                );

                $action = '<a class="button button-secondary" href="' . esc_url($installUrl) . '">' . esc_html__('Install', 'a-ripple-song') . '</a>';
            } elseif ($installed && current_user_can('activate_plugins') && is_string($pluginFile)) {
                $activateUrl = wp_nonce_url(
                    self_admin_url('plugins.php?action=activate&plugin=' . rawurlencode($pluginFile)),
                    'activate-plugin_' . $pluginFile
                );

                $action = '<a class="button button-primary" href="' . esc_url($activateUrl) . '">' . esc_html__('Activate', 'a-ripple-song') . '</a>';
            }
        }

        $plugins[$slug] = [
            'slug' => $slug,
            'name' => $definition['name'],
            'description' => $definition['description'],
            'plugin_file' => $pluginFile,
            'installed' => $installed,
            'active' => $active,
            'action' => $action,
        ];
    }

    return $plugins;
}

function aripplesong_find_plugin_file(string $slug, ?string $expectedFile = null): ?string
{
    if (is_string($expectedFile) && $expectedFile !== '' && file_exists(WP_PLUGIN_DIR . '/' . $expectedFile)) {
        return $expectedFile;
    }

    if (! function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugins = get_plugins();
    $candidates = [];

    foreach ($plugins as $pluginFile => $pluginData) {
        if (! is_string($pluginFile)) {
            continue;
        }

        if (strpos($pluginFile, $slug . '/') !== 0) {
            continue;
        }

        $candidates[$pluginFile] = $pluginData;
    }

    if (! $candidates) {
        return null;
    }

    $preferred = "{$slug}/{$slug}.php";
    if (isset($candidates[$preferred])) {
        return $preferred;
    }

    return array_key_first($candidates);
}

function aripplesong_is_plugin_active(string $pluginFile): bool
{
    if (! function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (is_plugin_active($pluginFile)) {
        return true;
    }

    if (is_multisite() && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network($pluginFile)) {
        return true;
    }

    return false;
}

function aripplesong_is_active_theme(): bool
{
    $stylesheet = get_stylesheet();
    return is_string($stylesheet) && $stylesheet === 'a-ripple-song';
}

function aripplesong_is_recommended_plugins_notice_screen(): bool
{
    $phpSelf = isset($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : '';
    $base = basename($phpSelf);

    if ($base === 'themes.php') {
        return true;
    }

    if ($base !== 'admin.php') {
        return false;
    }

    $page = isset($_GET['page']) ? (string) wp_unslash($_GET['page']) : '';
    if ($page === '') {
        return false;
    }

    return $page === 'crb_carbon_fields_container_theme_settings.php';
}

