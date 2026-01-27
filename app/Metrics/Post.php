<?php

namespace App\Metrics;

/**
 * Register and handle post metrics (views and podcast plays).
 */
class Post
{
    /**
     * Wire up WordPress hooks for metrics.
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerMetaFields']);
        add_action('save_post', [$this, 'ensureDefaults'], 10, 2);

        add_action('wp_ajax_aripplesong_increment_view', [$this, 'incrementViewCount']);
        add_action('wp_ajax_nopriv_aripplesong_increment_view', [$this, 'incrementViewCount']);

        add_action('wp_ajax_aripplesong_increment_play', [$this, 'incrementPlayCount']);
        add_action('wp_ajax_nopriv_aripplesong_increment_play', [$this, 'incrementPlayCount']);

        add_action('wp_ajax_aripplesong_get_metrics', [$this, 'getMetrics']);
        add_action('wp_ajax_nopriv_aripplesong_get_metrics', [$this, 'getMetrics']);
    }

    /**
     * Register meta fields for post views and podcast plays.
     */
    public function registerMetaFields(): void
    {
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

        $podcast_post_type = \function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
        if ($podcast_post_type) {
            register_post_meta($podcast_post_type, '_play_count', [
                'type' => 'integer',
                'single' => true,
                'default' => 0,
                'show_in_rest' => false,
                'auth_callback' => function ($allowed, $meta_key, $post_id) {
                    return current_user_can('edit_post', $post_id);
                },
            ]);
        }
    }

    /**
     * Ensure defaults exist on save.
     */
    public function ensureDefaults(int $post_id, \WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (!metadata_exists('post', $post_id, '_views_count')) {
            update_post_meta($post_id, '_views_count', 0);
        }

        $podcast_post_type = \function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
        if ($podcast_post_type && $post->post_type === $podcast_post_type && !metadata_exists('post', $post_id, '_play_count')) {
            update_post_meta($post_id, '_play_count', 0);
        }
    }

    /**
     * Whether the current user can read a post for metrics purposes.
     */
    private function canReadPost(\WP_Post $post): bool
    {
        if ($post->post_status === 'publish') {
            return true;
        }

        return current_user_can('read_post', $post->ID);
    }

    /**
     * Handle AJAX view increment for any post type.
     */
    public function incrementViewCount(): void
    {
        check_ajax_referer('aripplesong-ajax');

        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        $post    = $post_id ? get_post($post_id) : null;

        if (!$post || !$this->canReadPost($post)) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'a-ripple-song')], 400);
        }

        $count = (int) get_post_meta($post_id, '_views_count', true);
        $count = max(0, $count) + 1;

        update_post_meta($post_id, '_views_count', $count);

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Handle AJAX play increment for podcast post type.
     */
    public function incrementPlayCount(): void
    {
        check_ajax_referer('aripplesong-ajax');

        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        $post    = $post_id ? get_post($post_id) : null;

        $podcast_post_type = \function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
        if (!$post || !$podcast_post_type || $post->post_type !== $podcast_post_type || !$this->canReadPost($post)) {
            wp_send_json_error(['message' => __('Invalid podcast post.', 'a-ripple-song')], 400);
        }

        $count = (int) get_post_meta($post_id, '_play_count', true);
        $count = max(0, $count) + 1;

        update_post_meta($post_id, '_play_count', $count);

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Batch fetch metrics (views for all, plays for podcasts).
     */
    public function getMetrics(): void
    {
        check_ajax_referer('aripplesong-ajax');

        $ids = isset($_POST['post_ids']) ? (array) $_POST['post_ids'] : [];
        $ids = array_map(static function ($id): int {
            return absint(wp_unslash($id));
        }, $ids);
        $ids = array_filter($ids);

        if (empty($ids)) {
            wp_send_json_error(['message' => __('No post IDs provided.', 'a-ripple-song')], 400);
        }

        $data = [];

        foreach ($ids as $post_id) {
            $post = get_post($post_id);

            if (!$post || !$this->canReadPost($post)) {
                continue;
            }

            $views = (int) get_post_meta($post_id, '_views_count', true);
            $podcast_post_type = \function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
            $plays = ($podcast_post_type && $post->post_type === $podcast_post_type)
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
}

(new Post())->register();
