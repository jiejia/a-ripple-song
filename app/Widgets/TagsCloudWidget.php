<?php

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

/**
 * Tags Cloud Widget
 * Display a tag cloud.
 */
class TagsCloudWidget extends Widget
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
            'tags_cloud_widget',
            __('aripplesong - Tags Cloud', 'sage'),
            __('Display article tags cloud', 'sage'),
            [
                Field::make('text', 'tags_cloud_title', __('Title:', 'sage'))
                    ->set_default_value('TAGS'),
                Field::make('text', 'tags_cloud_number', __('Number of tags:', 'sage'))
                    ->set_default_value('20')
                    ->set_help_text(__('Maximum number of tags to display', 'sage')),
                Field::make('select', 'tags_cloud_orderby', __('Order by:', 'sage'))
                    ->add_options([
                        'count' => __('Post Count', 'sage'),
                        'name' => __('Tag Name', 'sage'),
                        'term_id' => __('Tag ID', 'sage'),
                        'rand' => __('Random', 'sage'),
                    ])
                    ->set_default_value('count'),
                Field::make('select', 'tags_cloud_order', __('Sort order:', 'sage'))
                    ->add_options([
                        'DESC' => __('Descending (High to Low/Z to A)', 'sage'),
                        'ASC' => __('Ascending (Low to High/A to Z)', 'sage'),
                    ])
                    ->set_default_value('DESC'),
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
            'tags_cloud_title' => 'title',
            'tags_cloud_number' => 'number',
            'tags_cloud_orderby' => 'orderby',
            'tags_cloud_order' => 'order',
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

        $title = $this->textValue($instance, ['tags_cloud_title', 'title'], 'TAGS');
        $number = $this->numberValue($instance, ['tags_cloud_number', 'number'], 20);
        $orderby = $this->choiceValue($instance, ['tags_cloud_orderby', 'orderby'], ['count', 'name', 'term_id', 'rand'], 'count');
        $order = $this->choiceValue($instance, ['tags_cloud_order', 'order'], ['ASC', 'DESC'], 'DESC');

        // Fetch tags.
        $tags = get_tags([
            'number' => $number,
            'orderby' => $orderby,
            'order' => $order,
            'hide_empty' => true,
        ]);

        echo \Roots\view('widgets.tags-cloud', [
            'title' => $title,
            'tags' => $tags,
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
     * Return an allowed select value with a fallback.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string  $key  Instance key.
     * @param  array<int,string>  $allowed  Allowed values.
     * @param  string  $default  Fallback value.
     */
    private function choiceValue(array $instance, string|array $keys, array $allowed, string $default): string
    {
        $value = '';

        foreach ((array) $keys as $key) {
            if (isset($instance[$key])) {
                $value = (string) $instance[$key];
                break;
            }
        }

        return in_array($value, $allowed, true) ? $value : $default;
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
