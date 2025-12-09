<?php

namespace App;

/**
 * Register meta fields for post views and podcast plays.
 */
add_action('init', function () {
    $post_types = get_post_types(['public' => true], 'names');

    foreach ($post_types as $post_type) {
        register_post_meta($post_type, '_views_count', [
            'type' => 'integer',
            'single' => true,
            'default' => 0,
            'show_in_rest' => false,
            'auth_callback' => function ($allowed, $meta_key, $post_id) {
                return current_user_can('edit_post', $post_id);
            },
        ]);
    }

    register_post_meta('podcast', '_play_count', [
        'type' => 'integer',
        'single' => true,
        'default' => 0,
        'show_in_rest' => false,
        'auth_callback' => function ($allowed, $meta_key, $post_id) {
            return current_user_can('edit_post', $post_id);
        },
    ]);
});

/**
 * Ensure defaults exist on save.
 */
add_action('save_post', function ($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!metadata_exists('post', $post_id, '_views_count')) {
        update_post_meta($post_id, '_views_count', 0);
    }

    if ($post->post_type === 'podcast' && !metadata_exists('post', $post_id, '_play_count')) {
        update_post_meta($post_id, '_play_count', 0);
    }
}, 10, 2);

/**
 * Handle AJAX view increment for any post type.
 */
function aripplesong_increment_view_count() {
    check_ajax_referer('aripplesong-ajax');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $post    = $post_id ? get_post($post_id) : null;

    if (!$post) {
        wp_send_json_error(['message' => 'Invalid post ID'], 400);
    }

    $count = (int) get_post_meta($post_id, '_views_count', true);
    $count = max(0, $count) + 1;

    update_post_meta($post_id, '_views_count', $count);

    wp_send_json_success(['count' => $count]);
}
add_action('wp_ajax_aripplesong_increment_view', __NAMESPACE__ . '\\aripplesong_increment_view_count');
add_action('wp_ajax_nopriv_aripplesong_increment_view', __NAMESPACE__ . '\\aripplesong_increment_view_count');

/**
 * Handle AJAX play increment for podcast post type.
 */
function aripplesong_increment_play_count() {
    check_ajax_referer('aripplesong-ajax');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $post    = $post_id ? get_post($post_id) : null;

    if (!$post || $post->post_type !== 'podcast') {
        wp_send_json_error(['message' => 'Invalid podcast post'], 400);
    }

    $count = (int) get_post_meta($post_id, '_play_count', true);
    $count = max(0, $count) + 1;

    update_post_meta($post_id, '_play_count', $count);

    wp_send_json_success(['count' => $count]);
}
add_action('wp_ajax_aripplesong_increment_play', __NAMESPACE__ . '\\aripplesong_increment_play_count');
add_action('wp_ajax_nopriv_aripplesong_increment_play', __NAMESPACE__ . '\\aripplesong_increment_play_count');

/**
 * Batch fetch metrics (views for all, plays for podcasts).
 */
function aripplesong_get_metrics() {
    check_ajax_referer('aripplesong-ajax');

    $ids = isset($_POST['post_ids']) ? (array) $_POST['post_ids'] : [];
    $ids = array_filter(array_map('absint', $ids));

    if (empty($ids)) {
        wp_send_json_error(['message' => 'No post IDs provided'], 400);
    }

    $data = [];

    foreach ($ids as $post_id) {
        $post = get_post($post_id);

        if (!$post) {
            continue;
        }

        $views = (int) get_post_meta($post_id, '_views_count', true);
        $plays = $post->post_type === 'podcast'
            ? (int) get_post_meta($post_id, '_play_count', true)
            : null;

        $data[$post_id] = [
            'views' => max(0, $views),
            'plays' => $plays !== null ? max(0, $plays) : null,
            'post_type' => $post->post_type,
        ];
    }

    wp_send_json_success(['counts' => $data]);
}
add_action('wp_ajax_aripplesong_get_metrics', __NAMESPACE__ . '\\aripplesong_get_metrics');
add_action('wp_ajax_nopriv_aripplesong_get_metrics', __NAMESPACE__ . '\\aripplesong_get_metrics');

