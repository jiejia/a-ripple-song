<?php

/**
 * Podcast List Widget
 * 显示播客列表
 */
class Podcast_List_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'podcast_list_widget',
            __('aripplesong - 播客列表', 'sage'),
            ['description' => __('显示最新的播客列表', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'PODCAST';
        $posts_per_page = !empty($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 3;
        $show_see_all = !empty($instance['show_see_all']) ? $instance['show_see_all'] : true;
        
        // 查询播客
        $podcasts = new WP_Query([
            'post_type' => 'podcast',
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
                    <a href="<?php echo get_permalink(get_page_by_path('podcast')); ?>">See all</a>
                </span>
                <?php endif; ?>
            </div>
            <ul class="flex gap-2 mt-2">
                <li>
                    <button class="btn bg-base-200 rounded-full btn-sm">Recent</button>
                </li>
                <li>
                    <button class="btn bg-base-100 rounded-full btn-sm">Popular</button>
                </li>
                <li>
                    <button class="btn bg-base-100 rounded-full btn-sm">Random</button>
                </li>
            </ul>
            <ul class="grid grid-flow-row gap-y-4 mt-4">
                <?php if ($podcasts->have_posts()): ?>
                    <?php while ($podcasts->have_posts()): $podcasts->the_post(); ?>
                        <?php
                        $post_id = get_the_ID();
                        $audio_file = get_post_meta($post_id, 'audio_file', true);
                        $episode_data = get_episode_data($post_id);
                        ?>
                        <li x-data="{ episode: <?php echo esc_attr(wp_json_encode($episode_data)); ?> }">
                            <?php 
                                echo \Roots\view('partials.podcast-episode-card', [
                                    'post_id' => $post_id,
                                    'audio_file' => $audio_file,
                                    'episode_data' => $episode_data,
                                    'title' => get_the_title(),
                                    'show_link' => true
                                ])->render();
                            ?>
                        </li>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <li class="text-center text-base-content/50 py-8">暂无播客内容</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'PODCAST';
        $posts_per_page = !empty($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 3;
        $show_see_all = isset($instance['show_see_all']) ? $instance['show_see_all'] : true;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('标题:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e('显示数量:', 'sage'); ?></label>
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
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_see_all); ?> 
                   id="<?php echo $this->get_field_id('show_see_all'); ?>" 
                   name="<?php echo $this->get_field_name('show_see_all'); ?>">
            <label for="<?php echo $this->get_field_id('show_see_all'); ?>"><?php _e('显示"查看全部"链接', 'sage'); ?></label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? absint($new_instance['posts_per_page']) : 3;
        $instance['show_see_all'] = (!empty($new_instance['show_see_all'])) ? 1 : 0;
        return $instance;
    }
}

