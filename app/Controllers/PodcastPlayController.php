<?php

namespace Jiejia\ARippleSong\Controllers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ControllerAbstract;
use Jiejia\ARippleSong\CPTs\PodcastCPT;
use Jiejia\ARippleSong\Plugin;

/**
 * REST endpoints for reading and recording podcast episode plays.
 *
 * GET  /wp-json/a-ripple-song/v1/episodes/{id}/plays
 * POST /wp-json/a-ripple-song/v1/episodes/{id}/plays
 * GET  /wp-json/a-ripple-song/v1/episodes/plays?ids=1,2,3
 */
class PodcastPlayController extends ControllerAbstract
{
    /**
     * Repeated plays of the same episode from the same IP within this window count once.
     */
    private const DEDUPE_SECONDS = 30 * MINUTE_IN_SECONDS;

    private PodcastCPT $podcast;

    public function __construct()
    {
        $this->podcast = new PodcastCPT();
    }

    /**
     * Register play count routes.
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        $idArg = [
            'id' => [
                'type' => 'integer',
                'minimum' => 1,
                'sanitize_callback' => 'absint',
            ],
        ];

        register_rest_route(self::NAMESPACE, '/episodes/(?P<id>\d+)/plays', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'show'],
                'permission_callback' => '__return_true',
                'args' => $idArg,
            ],
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'store'],
                'permission_callback' => '__return_true',
                'args' => $idArg,
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/episodes/plays', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'index'],
            'permission_callback' => '__return_true',
            'args' => [
                // Accepts ?ids=1,2,3 or ?ids[]=1&ids[]=2
                'ids' => [
                    'required' => true,
                    'type' => 'array',
                    'items' => ['type' => 'integer', 'minimum' => 1],
                    'maxItems' => 100,
                ],
            ],
        ]);
    }

    /**
     * Return the play count of one episode.
     *
     * @param \WP_REST_Request $request REST request.
     * @return \WP_REST_Response|\WP_Error
     */
    public function show(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $post = $this->readableEpisode((int) $request['id']);

        if (is_wp_error($post)) {
            return $post;
        }

        return rest_ensure_response([
            'id' => $post->ID,
            'play_count' => $this->podcast->playCount($post->ID),
        ]);
    }

    /**
     * Record one play of a published episode.
     *
     * @param \WP_REST_Request $request REST request.
     * @return \WP_REST_Response|\WP_Error
     */
    public function store(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $post = $this->readableEpisode((int) $request['id']);

        if (is_wp_error($post)) {
            return $post;
        }

        // Previews of drafts must not inflate the count.
        $counted = $post->post_status === 'publish' && $this->claimPlay($post->ID);
        $count = $counted ? $this->podcast->incrementPlayCount($post->ID) : $this->podcast->playCount($post->ID);

        return rest_ensure_response([
            'id' => $post->ID,
            'play_count' => $count,
            'counted' => $counted,
        ]);
    }

    /**
     * Return play counts of several episodes; unknown or unreadable IDs are left out.
     *
     * @param \WP_REST_Request $request REST request.
     * @return \WP_REST_Response
     */
    public function index(\WP_REST_Request $request): \WP_REST_Response
    {
        $counts = [];

        foreach ((array) $request['ids'] as $id) {
            $post = $this->readableEpisode((int) $id);

            if (! is_wp_error($post)) {
                $counts[$post->ID] = $this->podcast->playCount($post->ID);
            }
        }

        // Cast to object so JSON keeps the IDs as keys even for a single item.
        return rest_ensure_response(['play_counts' => (object) $counts]);
    }

    /**
     * Return the episode when it exists and the current visitor may read it.
     *
     * @param int $id Post ID.
     * @return \WP_Post|\WP_Error
     */
    private function readableEpisode(int $id): \WP_Post|\WP_Error
    {
        $post = get_post($id);

        if (
            ! $post instanceof \WP_Post
            || $post->post_type !== $this->podcast->slug()
            || ($post->post_status !== 'publish' && ! current_user_can('read_post', $post->ID))
        ) {
            return new \WP_Error('ars_episode_not_found', __('Episode not found.', 'a-ripple-song'), ['status' => 404]);
        }

        return $post;
    }

    /**
     * Whether this visitor's play should be counted, remembering it for the dedupe window.
     *
     * @param int $postId Episode ID.
     * @return bool
     */
    private function claimPlay(int $postId): bool
    {
        /**
         * Filters the window in seconds during which repeated plays from one IP count once; 0 disables it.
         *
         * @param int $seconds Dedupe window.
         * @param int $postId Episode ID.
         */
        $seconds = (int) apply_filters(Plugin::PREFIX . 'play_dedupe_seconds', self::DEDUPE_SECONDS, $postId);

        if ($seconds <= 0) {
            return true;
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        // Only a salted hash is stored, never the raw IP.
        $key = Plugin::PREFIX . 'play_' . md5(wp_hash($ip . '|' . $postId));

        if (get_transient($key)) {
            return false;
        }

        set_transient($key, 1, $seconds);

        return true;
    }
}
