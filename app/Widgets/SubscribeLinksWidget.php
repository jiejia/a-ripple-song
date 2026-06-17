<?php

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

/**
 * Subscribe Links Widget
 * Display subscription platform links.
 */
class SubscribeLinksWidget extends Widget
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
            'subscribe_links_widget',
            __('aripplesong - Subscribe Links', 'sage'),
            __('Display podcast subscription platform links', 'sage'),
            [
                Field::make('text', 'subscribe_links_title', __('Title:', 'sage'))
                    ->set_default_value('SUBSCRIBE'),
                Field::make('text', 'apple_podcast_url', __('Apple Podcast Link:', 'sage'))
                    ->set_help_text(__('Leave blank to hide this button', 'sage')),
                Field::make('text', 'spotify_url', __('Spotify Link:', 'sage'))
                    ->set_help_text(__('Leave blank to hide this button', 'sage')),
                Field::make('text', 'youtube_music_url', __('Youtube Music Link:', 'sage'))
                    ->set_help_text(__('Leave blank to hide this button', 'sage')),
            ]
        );
    }

    /**
     * Render the Carbon Fields form with legacy values mapped to unique keys.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function form($instance)
    {
        parent::form($this->withLegacyAliases($instance, [
            'subscribe_links_title' => 'title',
        ]));
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

        $title = $this->textValue($instance, ['subscribe_links_title', 'title'], 'SUBSCRIBE');
        $apple_podcast_url = $this->urlValue($instance, 'apple_podcast_url');
        $spotify_url = $this->urlValue($instance, 'spotify_url');
        $youtube_music_url = $this->urlValue($instance, 'youtube_music_url');

        // Require at least one link to render.
        $has_links = ! empty($apple_podcast_url) || ! empty($spotify_url) || ! empty($youtube_music_url);

        if (! $has_links) {
            // Hide when nothing is configured.
            echo $args['after_widget'];

            return;
        }

        echo \Roots\view('widgets.subscribe-links', [
            'title' => $title,
            'apple_podcast_url' => $apple_podcast_url,
            'spotify_url' => $spotify_url,
            'youtube_music_url' => $youtube_music_url,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Return a text setting with a fallback.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string  $key  Instance key.
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
     * Return a URL setting.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string  $key  Instance key.
     */
    private function urlValue(array $instance, string $key): string
    {
        return ! empty($instance[$key]) ? esc_url_raw($instance[$key]) : '';
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
