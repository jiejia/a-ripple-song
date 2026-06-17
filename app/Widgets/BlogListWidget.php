<?php

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

/**
 * Blog List Widget
 * Display a list of latest blog posts.
 */
class BlogListWidget extends Widget
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
            'blog_list_widget',
            __('aripplesong - Blog List', 'sage'),
            __('Display latest blog posts list', 'sage'),
            [
                Field::make('text', 'blog_list_title', __('Title:', 'sage'))
                    ->set_default_value('BLOG'),
                Field::make('text', 'blog_list_posts_per_page', __('Number of posts:', 'sage'))
                    ->set_default_value('6'),
                Field::make('text', 'blog_list_columns', __('Number of columns:', 'sage'))
                    ->set_default_value('3'),
                Field::make('checkbox', 'blog_list_show_see_all', __('Show "See all" link', 'sage'))
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
            'blog_list_title' => 'title',
            'blog_list_posts_per_page' => 'posts_per_page',
            'blog_list_columns' => 'columns',
            'blog_list_show_see_all' => 'show_see_all',
        ]);

        parent::form($this->normalizeCheckboxes($instance, ['blog_list_show_see_all']));
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

        $title = $this->textValue($instance, ['blog_list_title', 'title'], 'BLOG');
        $posts_per_page = $this->numberValue($instance, ['blog_list_posts_per_page', 'posts_per_page'], 6);
        $show_see_all = $this->booleanValue($instance, ['blog_list_show_see_all', 'show_see_all'], true);
        $columns = min($this->numberValue($instance, ['blog_list_columns', 'columns'], 3), 6);

        // Query blog posts.
        $posts = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        echo \Roots\view('widgets.blog-list', [
            'title' => $title,
            'posts' => $posts,
            'show_see_all' => $show_see_all,
            'columns' => $columns,
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
     * Return a positive integer setting with a fallback.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string  $key  Instance key.
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
     * @param  string  $key  Instance key.
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
