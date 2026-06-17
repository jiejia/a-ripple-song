<?php

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

/**
 * Podcast List Widget
 * Display podcast lists.
 */
class PodcastListWidget extends Widget
{
    /**
     * Keep the original WordPress widget id for existing widget instances.
     *
     * @var string
     */
    protected $widget_id_prefix = '';

    /**
     * Create the widget and its Carbon Fields admin form.
     */
    public function __construct()
    {
        $this->setup(
            'podcast_list_widget',
            __('aripplesong - Podcast List', 'sage'),
            __('Display latest podcast list', 'sage'),
            [
                Field::make('text', 'podcast_list_title', __('Title:', 'sage'))
                    ->set_default_value('PODCAST'),
                Field::make('text', 'podcast_list_posts_per_page', __('Number of posts:', 'sage'))
                    ->set_default_value('3'),
                Field::make('checkbox', 'podcast_list_show_see_all', __('Show "See all" link', 'sage'))
                    ->set_option_value('1')
                    ->set_default_value(true),
            ]
        );
    }

    /**
     * Render the Carbon Fields form with legacy checkbox values normalized.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function form($instance)
    {
        $instance = $this->withLegacyAliases($instance, [
            'podcast_list_title' => 'title',
            'podcast_list_posts_per_page' => 'posts_per_page',
            'podcast_list_show_see_all' => 'show_see_all',
        ]);

        parent::form($this->normalizeCheckboxes($instance, ['podcast_list_show_see_all']));
    }

    /**
     * Render the widget with values normalized from legacy and Carbon Fields storage.
     *
     * @param  array<string,mixed>  $args  Widget wrapper arguments.
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = $this->textValue($instance, ['podcast_list_title', 'title'], 'PODCAST');
        $posts_per_page = $this->numberValue($instance, ['podcast_list_posts_per_page', 'posts_per_page'], 3);
        $show_see_all = $this->booleanValue($instance, ['podcast_list_show_see_all', 'show_see_all'], true);

        // Query latest podcasts (recent).
        $recent_podcasts = new WP_Query([
            'post_type' => 'podcast',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        // Query popular podcasts (by views + plays weighted score).
        // Fetch more posts to ensure we get the most popular ones after sorting.
        $popular_query = new WP_Query([
            'post_type' => 'podcast',
            'posts_per_page' => max($posts_per_page * 3, 20), // Fetch more to sort properly
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        // Calculate weighted score and sort.
        $popular_posts_with_score = [];
        if ($popular_query->have_posts()) {
            while ($popular_query->have_posts()) {
                $popular_query->the_post();
                $pid = get_the_ID();
                $views = (int) get_post_meta($pid, '_views_count', true);
                $plays = (int) get_post_meta($pid, '_play_count', true);
                // Weighted score: views + plays (can adjust weights if needed)
                $score = $views + $plays;
                $popular_posts_with_score[] = [
                    'post' => get_post($pid),
                    'score' => $score,
                ];
            }
            wp_reset_postdata();
        }

        // Sort by score descending.
        usort($popular_posts_with_score, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        // Get top N posts.
        $popular_posts_with_score = array_slice($popular_posts_with_score, 0, $posts_per_page);

        // Query random podcasts.
        $random_podcasts = new WP_Query([
            'post_type' => 'podcast',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby' => 'rand',
        ]);

        // Prepare the podcast data for the three tabs.
        $podcast_data = [
            'recent' => $this->prepare_podcast_list($recent_podcasts),
            'popular' => $this->prepare_podcast_list_from_posts($popular_posts_with_score),
            'random' => $this->prepare_podcast_list($random_podcasts),
        ];

        wp_reset_postdata();

        echo \Roots\view('widgets.podcast-list', [
            'title' => $title,
            'show_see_all' => $show_see_all,
            'podcast_data' => $podcast_data,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Prepare podcast list data.
     */
    private function prepare_podcast_list($query)
    {
        $podcasts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $audio_file = get_post_meta($post_id, 'audio_file', true);
                $episode_data = get_episode_data($post_id);

                $podcasts[] = [
                    'post_id' => $post_id,
                    'audio_file' => $audio_file,
                    'episode_data' => $episode_data,
                    'title' => get_the_title(),
                ];
            }
        }

        return $podcasts;
    }

    /**
     * Prepare podcast list data from pre-sorted posts array.
     */
    private function prepare_podcast_list_from_posts($posts_with_score)
    {
        $podcasts = [];

        foreach ($posts_with_score as $item) {
            $post = $item['post'];
            $post_id = $post->ID;
            $audio_file = get_post_meta($post_id, 'audio_file', true);
            $episode_data = get_episode_data($post_id);

            $podcasts[] = [
                'post_id' => $post_id,
                'audio_file' => $audio_file,
                'episode_data' => $episode_data,
                'title' => $post->post_title,
            ];
        }

        return $podcasts;
    }

    /**
     * Return a text setting with a fallback.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string|array<int,string>  $keys  Instance keys.
     * @param  string  $default  Fallback value.
     */
    private function textValue(array $instance, string|array $keys, string $default): string
    {
        foreach ((array) $keys as $key) {
            if (isset($instance[$key]) && $instance[$key] !== '') {
                return sanitize_text_field($instance[$key]);
            }
        }

        return $default;
    }

    /**
     * Return a positive integer setting with a fallback.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string|array<int,string>  $keys  Instance keys.
     * @param  int  $default  Fallback value.
     */
    private function numberValue(array $instance, string|array $keys, int $default): int
    {
        $value = 0;

        foreach ((array) $keys as $key) {
            if (isset($instance[$key])) {
                $value = absint($instance[$key]);
                break;
            }
        }

        return $value > 0 ? $value : $default;
    }

    /**
     * Return a checkbox setting from legacy and Carbon Fields values.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string|array<int,string>  $keys  Instance keys.
     * @param  bool  $default  Fallback value.
     */
    private function booleanValue(array $instance, string|array $keys, bool $default): bool
    {
        foreach ((array) $keys as $key) {
            if (array_key_exists($key, $instance)) {
                return in_array($instance[$key], [true, 1, '1', 'yes', 'on'], true);
            }
        }

        return $default;
    }

    /**
     * Normalize legacy checkbox values before Carbon Fields renders the form.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  array<int,string>  $keys  Checkbox keys.
     * @return array<string,mixed>
     */
    private function normalizeCheckboxes(array $instance, array $keys): array
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $instance)) {
                continue;
            }

            $instance[$key] = $this->booleanValue($instance, $key, false) ? '1' : '';
        }

        return $instance;
    }

    /**
     * Copy legacy instance values to unique Carbon Fields keys for editing.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  array<string,string>  $aliases  New key to legacy key map.
     * @return array<string,mixed>
     */
    private function withLegacyAliases(array $instance, array $aliases): array
    {
        foreach ($aliases as $newKey => $legacyKey) {
            if (! array_key_exists($newKey, $instance) && array_key_exists($legacyKey, $instance)) {
                $instance[$newKey] = $instance[$legacyKey];
            }
        }

        return $instance;
    }
}
