<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\CPTs\PodcastCPT;
use Jiejia\ARippleSong\Plugin;
use Jiejia\ARippleSong\Settings\DashboardSetting;
use Jiejia\ARippleSong\Statistics\PlayLog;

/**
 * Records daily plays and loads the dashboard assets.
 */
class StatisticsServiceProvider extends ServiceProviderAbstract
{
    private const HANDLE = Plugin::PREFIX . 'dashboard';

    private PlayLog $log;

    public function __construct()
    {
        $this->log = new PlayLog();
    }

    /**
     * Register statistics hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // The plugin has no activation hook; plays arrive over REST, so install on init rather than admin_init.
        add_action('init', [$this->log, 'install']);
        add_action('a_ripple_song_play_counted', [$this->log, 'record']);
        add_action('deleted_post', [$this, 'forgetEpisode'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Drop the daily rows of a permanently deleted episode.
     *
     * @param int $postId Post ID.
     * @param \WP_Post|null $post Deleted post.
     * @return void
     */
    public function forgetEpisode(int $postId, ?\WP_Post $post = null): void
    {
        if ($post instanceof \WP_Post && $post->post_type === (new PodcastCPT())->slug()) {
            $this->log->forget($postId);
        }
    }

    /**
     * Enqueue the dashboard styles and charts on the dashboard page only.
     *
     * @param string $hookSuffix Current admin page hook.
     * @return void
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'toplevel_page_' . (new DashboardSetting())->slug()) {
            return;
        }

        wp_enqueue_style(
            self::HANDLE,
            A_RIPPLE_SONG_PLUGIN_URL . 'resources/assets/css/dashboard.css',
            ['dashicons'],
            Plugin::assetVersion('resources/assets/css/dashboard.css')
        );

        wp_enqueue_script(
            self::HANDLE,
            A_RIPPLE_SONG_PLUGIN_URL . 'resources/assets/js/dashboard.js',
            [],
            Plugin::assetVersion('resources/assets/js/dashboard.js'),
            true
        );
    }
}
