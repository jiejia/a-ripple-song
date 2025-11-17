<?php

/**
 * Blog List Widget
 * 显示博客文章列表
 */
class Blog_List_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'blog_list_widget',
            __('aripplesong - Blog List', 'sage'),
            ['description' => __('Display latest blog posts list', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'BLOG';
        $posts_per_page = !empty($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 6;
        $show_see_all = !empty($instance['show_see_all']) ? $instance['show_see_all'] : true;
        $columns = !empty($instance['columns']) ? absint($instance['columns']) : 3;
        
        // 查询博客文章
        $posts = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        ?>
        <div class="rounded-lg bg-base-100 p-4">
            <div class="grid grid-cols-[1fr_auto] items-center">
                <h2 class="text-lg font-bold">
                    <?php echo esc_html($title); ?>
                </h2>
                <?php if ($show_see_all): ?>
                <span class="text-xs text-base-content/70">
                    <a href="<?php echo get_permalink(get_page_by_path('blog')); ?>">See all</a>
                </span>
                <?php endif; ?>
            </div>
            <ul class="grid grid-cols-<?php echo esc_attr($columns); ?> gap-4 gap-y-8 mt-4">
                <?php if ($posts->have_posts()): ?>
                    <?php while ($posts->have_posts()): $posts->the_post(); ?>
                        <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
                            <h3 class="text-md font-bold">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <div class="grid grid-flow-row gap-1 mt-2">
                                <?php
                                $categories = get_the_category();
                                if (!empty($categories)):
                                ?>
                                    <span class="text-xs text-base-content">
                                        <span>
                                            <a href="<?php echo get_category_link($categories[0]->term_id); ?>">
                                                <?php echo esc_html($categories[0]->name); ?>
                                            </a>
                                        </span>
                                    </span>
                                <?php endif; ?>
                                <span class="text-xs text-base-content/50">
                                    <span><?php echo get_localized_date(); ?></span>
                                </span>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <li class="col-span-<?php echo esc_attr($columns); ?> text-center text-base-content/50 py-8"><?php _e('No blog posts yet', 'sage'); ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'BLOG';
        $posts_per_page = !empty($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 6;
        $show_see_all = isset($instance['show_see_all']) ? $instance['show_see_all'] : true;
        $columns = !empty($instance['columns']) ? absint($instance['columns']) : 3;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e('Number of posts:', 'sage'); ?></label>
            <input class="tiny-text" 
                   id="<?php echo $this->get_field_id('posts_per_page'); ?>" 
                   name="<?php echo $this->get_field_name('posts_per_page'); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($posts_per_page); ?>" 
                   size="3">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Number of columns:', 'sage'); ?></label>
            <input class="tiny-text" 
                   id="<?php echo $this->get_field_id('columns'); ?>" 
                   name="<?php echo $this->get_field_name('columns'); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   max="6" 
                   value="<?php echo esc_attr($columns); ?>" 
                   size="3">
        </p>
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_see_all); ?> 
                   id="<?php echo $this->get_field_id('show_see_all'); ?>" 
                   name="<?php echo $this->get_field_name('show_see_all'); ?>">
            <label for="<?php echo $this->get_field_id('show_see_all'); ?>"><?php _e('Show "See all" link', 'sage'); ?></label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? absint($new_instance['posts_per_page']) : 6;
        $instance['show_see_all'] = (!empty($new_instance['show_see_all'])) ? 1 : 0;
        $instance['columns'] = (!empty($new_instance['columns'])) ? absint($new_instance['columns']) : 3;
        return $instance;
    }
}

