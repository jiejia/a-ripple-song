<?php

/**
 * Tags Cloud Widget
 * 显示标签云
 */
class Tags_Cloud_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'tags_cloud_widget',
            __('aripplesong - 标签云', 'sage'),
            ['description' => __('显示文章标签云', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'TAGS';
        $number = !empty($instance['number']) ? absint($instance['number']) : 20;
        $orderby = !empty($instance['orderby']) ? $instance['orderby'] : 'count';
        $order = !empty($instance['order']) ? $instance['order'] : 'DESC';
        
        // 获取标签
        $tags = get_tags([
            'number' => $number,
            'orderby' => $orderby,
            'order' => $order,
            'hide_empty' => true
        ]);
        
        if (empty($tags)) {
            // 没有标签时显示占位提示
            ?>
            <div class="card bg-base-100 w-full mt-4">
                <div class="card-body p-4">
                    <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
                    <div class="text-center py-8">
                        <div class="text-base-content/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <p class="text-sm font-medium"><?php _e('暂无标签', 'sage'); ?></p>
                            <p class="text-xs mt-1"><?php _e('发布文章并添加标签后将显示在这里', 'sage'); ?></p>
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
                                <a href="<?php echo get_tag_link($tag->term_id); ?>" 
                                   class="bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2 transition-colors"
                                   title="<?php printf(__('%d 篇文章', 'sage'), $tag->count); ?>">
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
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('标题:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('显示数量:', 'sage'); ?></label>
            <input class="tiny-text" 
                   id="<?php echo $this->get_field_id('number'); ?>" 
                   name="<?php echo $this->get_field_name('number'); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($number); ?>" 
                   size="3">
            <small class="description"><?php _e('最多显示多少个标签', 'sage'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('排序依据:', 'sage'); ?></label>
            <select class="widefat" 
                    id="<?php echo $this->get_field_id('orderby'); ?>" 
                    name="<?php echo $this->get_field_name('orderby'); ?>">
                <option value="count" <?php selected($orderby, 'count'); ?>><?php _e('文章数量', 'sage'); ?></option>
                <option value="name" <?php selected($orderby, 'name'); ?>><?php _e('标签名称', 'sage'); ?></option>
                <option value="term_id" <?php selected($orderby, 'term_id'); ?>><?php _e('标签ID', 'sage'); ?></option>
                <option value="rand" <?php selected($orderby, 'rand'); ?>><?php _e('随机', 'sage'); ?></option>
            </select>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('排序顺序:', 'sage'); ?></label>
            <select class="widefat" 
                    id="<?php echo $this->get_field_id('order'); ?>" 
                    name="<?php echo $this->get_field_name('order'); ?>">
                <option value="DESC" <?php selected($order, 'DESC'); ?>><?php _e('降序 (多到少/Z到A)', 'sage'); ?></option>
                <option value="ASC" <?php selected($order, 'ASC'); ?>><?php _e('升序 (少到多/A到Z)', 'sage'); ?></option>
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

