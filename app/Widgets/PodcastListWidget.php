<?php

namespace App\Widgets;

use App\Abstracts\WidgetAbstract;
use App\CustomPostTypes\Episode;
use App\Metas\PostViewCount;

/**
 * Podcast List Widget
 * Display podcast lists.
 */
class PodcastListWidget extends WidgetAbstract
{
    /**
     * Return the WordPress widget id base.
     */
    public static function idBase(): string
    {
        return 'podcast_list_widget';
    }

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [
            'podcast_list_title' => 'title',
            'podcast_list_posts_per_page' => 'posts_per_page',
            'podcast_list_show_see_all' => 'show_see_all',
        ];
    }

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::idBase(),
            __('aripplesong - Podcast List', 'a-ripple-song'),
            ['description' => __('Display latest podcast list', 'a-ripple-song')]
        );
    }

    /**
     * Front-end display of widget.
     *
     * @param  array<string,mixed>  $args  Widget wrapper arguments.
     * @param  array<string,mixed>  $instance  Saved widget option values.
     */
    public function widget($args, $instance): void
    {
        echo $args['before_widget'];

        $title = $this->getWidgetTitle($instance);
        $postsPerPage = ! empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 3;
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;

        $recentPodcasts = new \WP_Query([
            'post_type' => Episode::slug(),
            'posts_per_page' => $postsPerPage,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $popularQuery = new \WP_Query([
            'post_type' => Episode::slug(),
            'posts_per_page' => max($postsPerPage * 3, 20),
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $popularPostsWithScore = [];

        if ($popularQuery->have_posts()) {
            while ($popularQuery->have_posts()) {
                $popularQuery->the_post();
                $postId = get_the_ID();
                $views = (int) get_post_meta($postId, PostViewCount::storedFieldKey('views_count'), true);
                $plays = (int) get_post_meta($postId, Episode::storedFieldKey('play_count'), true);

                $popularPostsWithScore[] = [
                    'post' => get_post($postId),
                    'score' => $views + $plays,
                ];
            }

            wp_reset_postdata();
        }

        usort($popularPostsWithScore, static function (array $left, array $right): int {
            return $right['score'] <=> $left['score'];
        });

        $popularPostsWithScore = array_slice($popularPostsWithScore, 0, $postsPerPage);

        $randomPodcasts = new \WP_Query([
            'post_type' => Episode::slug(),
            'posts_per_page' => $postsPerPage,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby' => 'rand',
        ]);

        $podcastData = [
            'recent' => $this->preparePodcastList($recentPodcasts),
            'popular' => $this->preparePodcastListFromPosts($popularPostsWithScore),
            'random' => $this->preparePodcastList($randomPodcasts),
        ];

        wp_reset_postdata();

        echo \Roots\view('widgets.podcast-list', [
            'title' => $title,
            'show_see_all' => $showSeeAll,
            'podcast_data' => $podcastData,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form displayed in the WordPress admin.
     *
     * @param  array<string,mixed>  $instance  Current widget settings.
     */
    public function form($instance): void
    {
        $title = $this->getWidgetTitle($instance);
        $postsPerPage = ! empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 3;
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>">
                <?php esc_html_e('Number of episodes:', 'a-ripple-song'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('posts_per_page')); ?>"
                   type="number"
                   step="1"
                   min="1"
                   value="<?php echo esc_attr((string) $postsPerPage); ?>"
                   size="3">
        </p>

        <p>
            <input class="checkbox"
                   type="checkbox"
                   <?php checked($showSeeAll); ?>
                   id="<?php echo esc_attr($this->get_field_id('show_see_all')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('show_see_all')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_see_all')); ?>">
                <?php esc_html_e('Show "See all" link', 'a-ripple-song'); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param  array<string,mixed>  $newInstance  New widget settings submitted from the form.
     * @param  array<string,mixed>  $oldInstance  Previous widget settings.
     * @return array<string,mixed>
     */
    public function update($newInstance, $oldInstance): array
    {
        return [
            'title' => ! empty($newInstance['title']) ? sanitize_text_field((string) $newInstance['title']) : '',
            'posts_per_page' => ! empty($newInstance['posts_per_page']) ? max(1, absint($newInstance['posts_per_page'])) : 3,
            'show_see_all' => ! empty($newInstance['show_see_all']) ? 1 : 0,
        ];
    }

    /**
     * Return the normalized widget title.
     *
     * @param  array<string,mixed>  $instance  Saved widget options.
     */
    protected function getWidgetTitle(array $instance): string
    {
        $savedTitle = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : '';

        if ($savedTitle === '') {
            return 'PODCAST';
        }

        return $savedTitle;
    }

    /**
     * Prepare podcast list data from a query.
     *
     * @param  \WP_Query  $query  Episode query object.
     * @return array<int,array<string,mixed>>
     */
    protected function preparePodcastList(\WP_Query $query): array
    {
        $podcasts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $postId = get_the_ID();

                $podcasts[] = [
                    'post_id' => $postId,
                    'audio_file' => aripplesong_get_episode_meta($postId, 'audio_file'),
                    'episode_data' => get_episode_data($postId),
                    'title' => aripplesong_decode_plain_text(get_the_title()),
                ];
            }
        }

        return $podcasts;
    }

    /**
     * Prepare podcast list data from pre-sorted posts.
     *
     * @param  array<int,array<string,mixed>>  $postsWithScore  Scored episode posts.
     * @return array<int,array<string,mixed>>
     */
    protected function preparePodcastListFromPosts(array $postsWithScore): array
    {
        $podcasts = [];

        foreach ($postsWithScore as $item) {
            $post = $item['post'];
            $postId = $post->ID;

            $podcasts[] = [
                'post_id' => $postId,
                'audio_file' => aripplesong_get_episode_meta($postId, 'audio_file'),
                'episode_data' => get_episode_data($postId),
                'title' => aripplesong_decode_plain_text($post->post_title),
            ];
        }

        return $podcasts;
    }
}
