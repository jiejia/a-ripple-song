<?php

/**
 * Tags Cloud Widget
 * Display a tag cloud.
 */
class Tags_Cloud_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'tags_cloud_widget',
            __('aripplesong - Tags Cloud', 'a-ripple-song'),
            ['description' => __('Display article tags cloud', 'a-ripple-song')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'TAGS';
        $number = !empty($instance['number']) ? absint($instance['number']) : 20;
        $orderby = !empty($instance['orderby']) ? $instance['orderby'] : 'count';
        $order = !empty($instance['order']) ? $instance['order'] : 'DESC';
        
        // Fetch tags.
        $tags = get_tags([
            'number' => $number,
            'orderby' => $orderby,
            'order' => $order,
            'hide_empty' => true
        ]);
        
        if (empty($tags)) {
            // Render placeholders when no tags exist.
            ?>
            <div class="card bg-base-100 w-full mt-4">
                <div class="card-body p-4">
                    <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
                    <div class="text-center py-8">
                        <div class="text-base-content/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <p class="text-sm font-medium"><?php _e('No tags yet', 'a-ripple-song'); ?></p>
                            <p class="text-xs mt-1"><?php _e('Tags will appear here after publishing articles with tags', 'a-ripple-song'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="card bg-base-100 w-full mt-4">
                <div class="card-body p-4">
                    <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
                    <ul class="mt-0 flex flex-wrap gap-2 text-xs text-base-content/75">
                        <?php foreach ($tags as $tag): ?>
                            <li>
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" 
                                   class="bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2 transition-colors"
                                   title="<?php echo esc_attr(sprintf(_n('%d post', '%d posts', $tag->count, 'a-ripple-song'), $tag->count)); ?>">
                                    # <?php echo esc_html($tag->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'TAGS';
        $number = !empty($instance['number']) ? absint($instance['number']) : 20;
        $orderby = !empty($instance['orderby']) ? $instance['orderby'] : 'count';
        $order = !empty($instance['order']) ? $instance['order'] : 'DESC';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'a-ripple-song'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of tags:', 'a-ripple-song'); ?></label>
            <input class="tiny-text" 
                   id="<?php echo $this->get_field_id('number'); ?>" 
                   name="<?php echo $this->get_field_name('number'); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($number); ?>" 
                   size="3">
            <small class="description"><?php _e('Maximum number of tags to display', 'a-ripple-song'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by:', 'a-ripple-song'); ?></label>
            <select class="widefat" 
                    id="<?php echo $this->get_field_id('orderby'); ?>" 
                    name="<?php echo $this->get_field_name('orderby'); ?>">
                <option value="count" <?php selected($orderby, 'count'); ?>><?php _e('Post Count', 'a-ripple-song'); ?></option>
                <option value="name" <?php selected($orderby, 'name'); ?>><?php _e('Tag Name', 'a-ripple-song'); ?></option>
                <option value="term_id" <?php selected($orderby, 'term_id'); ?>><?php _e('Tag ID', 'a-ripple-song'); ?></option>
                <option value="rand" <?php selected($orderby, 'rand'); ?>><?php _e('Random', 'a-ripple-song'); ?></option>
            </select>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Sort order:', 'a-ripple-song'); ?></label>
            <select class="widefat" 
                    id="<?php echo $this->get_field_id('order'); ?>" 
                    name="<?php echo $this->get_field_name('order'); ?>">
                <option value="DESC" <?php selected($order, 'DESC'); ?>><?php _e('Descending (High to Low/Z to A)', 'a-ripple-song'); ?></option>
                <option value="ASC" <?php selected($order, 'ASC'); ?>><?php _e('Ascending (Low to High/A to Z)', 'a-ripple-song'); ?></option>
            </select>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : 'TAGS';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 20;
        $instance['orderby'] = (!empty($new_instance['orderby'])) && in_array($new_instance['orderby'], ['count', 'name', 'term_id', 'rand']) 
            ? $new_instance['orderby'] 
            : 'count';
        $instance['order'] = (!empty($new_instance['order'])) && in_array($new_instance['order'], ['ASC', 'DESC']) 
            ? $new_instance['order'] 
            : 'DESC';
        return $instance;
    }
}
