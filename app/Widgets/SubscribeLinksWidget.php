<?php

namespace App\Widgets;

use App\Abstracts\WidgetAbstract;

/**
 * Subscribe Links Widget
 * Display subscription platform links.
 */
class SubscribeLinksWidget extends WidgetAbstract
{
    /**
     * Return the WordPress widget id base.
     */
    public static function idBase(): string
    {
        return 'subscribe_links_widget';
    }

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [
            'subscribe_links_title' => 'title',
        ];
    }

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::idBase(),
            __('aripplesong - Subscribe Links', 'a-ripple-song'),
            ['description' => __('Display podcast subscription platform links', 'a-ripple-song')]
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

        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : 'SUBSCRIBE';
        $applePodcastUrl = ! empty($instance['apple_podcast_url']) ? esc_url((string) $instance['apple_podcast_url']) : '';
        $spotifyUrl = ! empty($instance['spotify_url']) ? esc_url((string) $instance['spotify_url']) : '';
        $youtubeMusicUrl = ! empty($instance['youtube_music_url']) ? esc_url((string) $instance['youtube_music_url']) : '';

        echo \Roots\view('widgets.subscribe-links', [
            'title' => $title,
            'apple_podcast_url' => $applePodcastUrl,
            'spotify_url' => $spotifyUrl,
            'youtube_music_url' => $youtubeMusicUrl,
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
        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : 'SUBSCRIBE';
        $applePodcastUrl = ! empty($instance['apple_podcast_url']) ? esc_url((string) $instance['apple_podcast_url']) : '';
        $spotifyUrl = ! empty($instance['spotify_url']) ? esc_url((string) $instance['spotify_url']) : '';
        $youtubeMusicUrl = ! empty($instance['youtube_music_url']) ? esc_url((string) $instance['youtube_music_url']) : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>"
                   placeholder="<?php echo esc_attr__('SUBSCRIBE', 'a-ripple-song'); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('apple_podcast_url')); ?>">
                <?php esc_html_e('Apple Podcast Link:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('apple_podcast_url')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('apple_podcast_url')); ?>"
                   type="url"
                   value="<?php echo esc_attr($applePodcastUrl); ?>"
                   placeholder="https://podcasts.apple.com/...">
            <small class="description"><?php esc_html_e('Leave blank to hide this button', 'a-ripple-song'); ?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('spotify_url')); ?>">
                <?php esc_html_e('Spotify Link:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('spotify_url')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('spotify_url')); ?>"
                   type="url"
                   value="<?php echo esc_attr($spotifyUrl); ?>"
                   placeholder="https://open.spotify.com/...">
            <small class="description"><?php esc_html_e('Leave blank to hide this button', 'a-ripple-song'); ?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('youtube_music_url')); ?>">
                <?php esc_html_e('YouTube Music Link:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('youtube_music_url')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('youtube_music_url')); ?>"
                   type="url"
                   value="<?php echo esc_attr($youtubeMusicUrl); ?>"
                   placeholder="https://music.youtube.com/...">
            <small class="description"><?php esc_html_e('Leave blank to hide this button', 'a-ripple-song'); ?></small>
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
            'apple_podcast_url' => ! empty($newInstance['apple_podcast_url']) ? esc_url_raw((string) $newInstance['apple_podcast_url']) : '',
            'spotify_url' => ! empty($newInstance['spotify_url']) ? esc_url_raw((string) $newInstance['spotify_url']) : '',
            'youtube_music_url' => ! empty($newInstance['youtube_music_url']) ? esc_url_raw((string) $newInstance['youtube_music_url']) : '',
        ];
    }
}
