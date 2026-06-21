<?php

namespace App\Widgets;

use App\Abstracts\WidgetAbstract;

/**
 * Blog List Widget
 * Display a list of latest blog posts.
 */
class BlogListWidget extends WidgetAbstract
{
    /**
     * Return the WordPress widget id base.
     */
    public static function idBase(): string
    {
        return 'blog_list_widget';
    }

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [
            'blog_list_title' => 'title',
            'blog_list_posts_per_page' => 'posts_per_page',
            'blog_list_columns' => 'columns',
            'blog_list_show_see_all' => 'show_see_all',
        ];
    }

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::idBase(),
            __('aripplesong - Blog List', 'sage'),
            ['description' => __('Display latest blog posts list', 'sage')]
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

        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : 'BLOG';
        $postsPerPage = ! empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 6;
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;
        $columns = ! empty($instance['columns']) ? min(3, max(1, absint($instance['columns']))) : 3;

        $posts = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $postsPerPage,
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
            'show_see_all' => $showSeeAll,
            'columns' => $columns,
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
        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : 'BLOG';
        $postsPerPage = ! empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 6;
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;
        $columns = ! empty($instance['columns']) ? min(3, max(1, absint($instance['columns']))) : 3;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'sage'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>">
                <?php esc_html_e('Number of posts:', 'sage'); ?>
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
            <label for="<?php echo esc_attr($this->get_field_id('columns')); ?>">
                <?php esc_html_e('Number of columns:', 'sage'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('columns')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('columns')); ?>"
                   type="number"
                   step="1"
                   min="1"
                   max="3"
                   value="<?php echo esc_attr((string) $columns); ?>"
                   size="3">
        </p>

        <p>
            <input class="checkbox"
                   type="checkbox"
                   <?php checked($showSeeAll); ?>
                   id="<?php echo esc_attr($this->get_field_id('show_see_all')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('show_see_all')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_see_all')); ?>">
                <?php esc_html_e('Show "See all" link', 'sage'); ?>
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
            'posts_per_page' => ! empty($newInstance['posts_per_page']) ? max(1, absint($newInstance['posts_per_page'])) : 6,
            'columns' => ! empty($newInstance['columns']) ? min(3, max(1, absint($newInstance['columns']))) : 3,
            'show_see_all' => ! empty($newInstance['show_see_all']) ? 1 : 0,
        ];
    }
}
