<?php

namespace ARippleSong;

if (!\defined('ABSPATH')) {
    exit;
}

// Default local Vite dev server URL for theme assets.
if (!\defined('ARIPPLESONG_VITE_DEV_SERVER')) {
    \define('ARIPPLESONG_VITE_DEV_SERVER', 'http://localhost:5173');
}

// Return the configured Vite dev server URL without a trailing slash.
function vite_dev_server() {
    if (\defined('ARIPPLESONG_VITE_DEV_SERVER')) {
        return \untrailingslashit(ARIPPLESONG_VITE_DEV_SERVER);
    }

    return 'http://localhost:5173';
}

// Return the absolute path to the generated Vite manifest file.
function vite_manifest_path() {
    return \get_theme_file_path('assets/dist/.vite/manifest.json');
}

// Load and cache the Vite manifest so it is parsed only once per request.
function vite_manifest() {
    static $manifest = null;

    if ($manifest !== null) {
        return $manifest;
    }

    $path = vite_manifest_path();

    if (!\file_exists($path)) {
        $manifest = false;
        return $manifest;
    }

    $contents = \file_get_contents($path);
    $data = \json_decode($contents, true);

    $manifest = \is_array($data) ? $data : false;
    return $manifest;
}

// Check whether the Vite dev server is reachable by requesting the HMR client.
function vite_dev_server_running() {
    $url = vite_dev_server() . '/@vite/client';

    $response = \wp_remote_get(
        $url,
        array(
            'timeout' => 1,
            'sslverify' => false,
        )
    );

    return !\is_wp_error($response) && 200 === \wp_remote_retrieve_response_code($response);
}

// Enqueue modules directly from the Vite dev server during local development.
function enqueue_vite_dev_entry($handle_prefix, $entry) {
    $server = vite_dev_server();

    \wp_enqueue_script_module($handle_prefix . '-vite-client', $server . '/@vite/client', array(), null);
    \wp_enqueue_script_module($handle_prefix . '-entry', $server . '/' . \ltrim($entry, '/'), array(), null);
}

// Enqueue built files from the Vite manifest when the dev server is unavailable.
function enqueue_vite_manifest_entry($handle_prefix, $entry) {
    $manifest = vite_manifest();

    if (!$manifest || empty($manifest[$entry])) {
        \error_log('Vite manifest entry missing: ' . $entry);
        return;
    }

    $asset = $manifest[$entry];
    $theme_uri = \get_theme_file_uri('assets/dist');

    if (!empty($asset['css']) && \is_array($asset['css'])) {
        foreach ($asset['css'] as $index => $css_file) {
            \wp_enqueue_style(
                $handle_prefix . '-style-' . $index,
                $theme_uri . '/' . \ltrim($css_file, '/'),
                array(),
                null
            );
        }
    }

    if (!empty($asset['file'])) {
        \wp_enqueue_script_module(
            $handle_prefix . '-script',
            $theme_uri . '/' . \ltrim($asset['file'], '/'),
            array(),
            null
        );
    }
}

// Load the main frontend theme entry, preferring the dev server in development.
function enqueue_frontend_assets() {
    $entry = 'assets/src/main.js';

    if (vite_dev_server_running()) {
        enqueue_vite_dev_entry('a-ripple-song-main', $entry);
        return;
    }

    enqueue_vite_manifest_entry('a-ripple-song-main', $entry);
}

// Hook frontend asset loading into the public site request lifecycle.
\add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend_assets');

// Load the block editor entry so editor-specific assets stay separate from the frontend.
function enqueue_editor_assets() {
    $entry = 'assets/src/editor.js';

    if (vite_dev_server_running()) {
        enqueue_vite_dev_entry('a-ripple-song-editor', $entry);
        return;
    }

    enqueue_vite_manifest_entry('a-ripple-song-editor', $entry);
}

// Hook editor asset loading into the block editor request lifecycle.
\add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_editor_assets');
