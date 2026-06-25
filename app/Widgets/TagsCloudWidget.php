<?php

namespace App\Widgets;

use App\Abstracts\WidgetAbstract;

/**
 * Tags Cloud Widget
 * Display a tag cloud.
 */
class TagsCloudWidget extends WidgetAbstract
{
    /**
     * Return the WordPress widget id base.
     */
    public static function idBase(): string
    {
        return 'tags_cloud_widget';
    }

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [
            'tags_cloud_title' => 'title',
            'tags_cloud_number' => 'number',
            'tags_cloud_orderby' => 'orderby',
            'tags_cloud_order' => 'order',
        ];
    }

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::idBase(),
            __('aripplesong - Tags Cloud', 'a-ripple-song'),
            ['description' => __('Display article tags cloud', 'a-ripple-song')]
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

        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : 'TAGS';
        $number = ! empty($instance['number']) ? max(1, absint($instance['number'])) : 20;
        $orderby = ! empty($instance['orderby']) ? sanitize_key((string) $instance['orderby']) : 'count';
        $order = ! empty($instance['order']) ? strtoupper(sanitize_key((string) $instance['order'])) : 'DESC';

        $tags = get_tags([
            'number' => $number,
            'orderby' => in_array($orderby, ['count', 'name', 'term_id', 'rand'], true) ? $orderby : 'count',
            'order' => in_array($order, ['ASC', 'DESC'], true) ? $order : 'DESC',
            'hide_empty' => true,
        ]);

        echo \Roots\view('widgets.tags-cloud', [
            'title' => $title,
            'tags' => $tags,
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
        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : 'TAGS';
        $number = ! empty($instance['number']) ? max(1, absint($instance['number'])) : 20;
        $orderby = ! empty($instance['orderby']) ? sanitize_key((string) $instance['orderby']) : 'count';
        $order = ! empty($instance['order']) ? strtoupper(sanitize_key((string) $instance['order'])) : 'DESC';
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
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>">
                <?php esc_html_e('Number of tags:', 'a-ripple-song'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('number')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('number')); ?>"
                   type="number"
                   step="1"
                   min="1"
                   value="<?php echo esc_attr((string) $number); ?>"
                   size="3">
            <small class="description"><?php esc_html_e('Maximum number of tags to display', 'a-ripple-song'); ?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('orderby')); ?>">
                <?php esc_html_e('Order by:', 'a-ripple-song'); ?>
            </label>
            <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('orderby')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('orderby')); ?>">
                <option value="count" <?php selected($orderby, 'count'); ?>><?php esc_html_e('Post Count', 'a-ripple-song'); ?></option>
                <option value="name" <?php selected($orderby, 'name'); ?>><?php esc_html_e('Tag Name', 'a-ripple-song'); ?></option>
                <option value="term_id" <?php selected($orderby, 'term_id'); ?>><?php esc_html_e('Tag ID', 'a-ripple-song'); ?></option>
                <option value="rand" <?php selected($orderby, 'rand'); ?>><?php esc_html_e('Random', 'a-ripple-song'); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order')); ?>">
                <?php esc_html_e('Sort order:', 'a-ripple-song'); ?>
            </label>
            <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('order')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('order')); ?>">
                <option value="DESC" <?php selected($order, 'DESC'); ?>><?php esc_html_e('Descending (High to Low/Z to A)', 'a-ripple-song'); ?></option>
                <option value="ASC" <?php selected($order, 'ASC'); ?>><?php esc_html_e('Ascending (Low to High/A to Z)', 'a-ripple-song'); ?></option>
            </select>
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
            'number' => ! empty($newInstance['number']) ? max(1, absint($newInstance['number'])) : 20,
            'orderby' => ! empty($newInstance['orderby']) && in_array($newInstance['orderby'], ['count', 'name', 'term_id', 'rand'], true)
                ? (string) $newInstance['orderby']
                : 'count',
            'order' => ! empty($newInstance['order']) && in_array($newInstance['order'], ['ASC', 'DESC'], true)
                ? (string) $newInstance['order']
                : 'DESC',
        ];
    }
}
