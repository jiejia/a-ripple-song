<?php

/**
 * Footer Links Widget
 * 页脚链接列表组件，支持动态添加链接或纯文本行
 */
class Footer_Links_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'footer_links_widget',
            __('aripplesong - Footer Links', 'a-ripple-song'),
            ['description' => __('Display a list of links or text items in the footer', 'a-ripple-song')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $items = !empty($instance['items']) ? $instance['items'] : [];
        
        // Filter out empty items
        $items = array_filter($items, function($item) {
            return !empty($item['text']);
        });
        
        echo \Roots\view('widgets.footer-links', [
            'title' => $title,
            'items' => $items,
        ])->render();
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $items = !empty($instance['items']) ? $instance['items'] : [];
        
        // Ensure at least one empty item for new widgets
        if (empty($items)) {
            $items = [['text' => '', 'url' => '', 'new_tab' => false]];
        }
        
        $widget_id = $this->get_field_id('items');
        $field_name = $this->get_field_name('items');
        ?>
        <div class="footer-links-widget-form" data-field-prefix="<?php echo esc_attr($field_name); ?>">
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'a-ripple-song'); ?></label>
                <input class="widefat" 
                       id="<?php echo $this->get_field_id('title'); ?>" 
                       name="<?php echo $this->get_field_name('title'); ?>" 
                       type="text" 
                       value="<?php echo esc_attr($title); ?>"
                       placeholder="<?php _e('e.g., Contact, Navigate, Support', 'a-ripple-song'); ?>">
            </p>
            
            <p style="margin-bottom: 8px;"><strong><?php _e('Items:', 'a-ripple-song'); ?></strong></p>
            
            <div id="<?php echo $widget_id; ?>_container" class="footer-links-container" style="margin-bottom: 10px;">
                <?php foreach ($items as $index => $item): ?>
                    <div class="footer-link-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;"><?php _e('Text:', 'a-ripple-song'); ?></label>
                            <input type="text" 
                                   class="widefat footer-link-text" 
                                   name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][text]" 
                                   value="<?php echo esc_attr($item['text'] ?? ''); ?>"
                                   placeholder="<?php _e('Display text', 'a-ripple-song'); ?>">
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;"><?php _e('URL (optional - leave empty for plain text):', 'a-ripple-song'); ?></label>
                            <input type="url" 
                                   class="widefat footer-link-url" 
                                   name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][url]" 
                                   value="<?php echo esc_attr($item['url'] ?? ''); ?>"
                                   placeholder="https://example.com">
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label>
                                <input type="checkbox" 
                                       class="footer-link-new-tab" 
                                       name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][new_tab]" 
                                       value="1"
                                       <?php checked(!empty($item['new_tab'])); ?>>
                                <?php _e('Open in new tab', 'a-ripple-song'); ?>
                            </label>
                        </div>
                        <div style="text-align: right;">
                            <button type="button" class="button button-link button-link-delete footer-remove-link" style="color: #b32d2e;"><?php _e('Delete', 'a-ripple-song'); ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <input type="hidden" class="footer-links-flag" name="<?php echo $this->get_field_name('_flag'); ?>" value="1">
            
            <p>
                <button type="button" 
                        class="button footer-add-link" 
                        data-widget-id="<?php echo $widget_id; ?>"><?php _e('+ Add Item', 'a-ripple-song'); ?></button>
            </p>
        </div>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        
        $instance['items'] = [];
        if (!empty($new_instance['items']) && is_array($new_instance['items'])) {
            foreach ($new_instance['items'] as $item) {
                if (!empty($item['text'])) {
                    $instance['items'][] = [
                        'text' => sanitize_text_field($item['text']),
                        'url' => !empty($item['url']) ? esc_url_raw($item['url']) : '',
                        'new_tab' => !empty($item['new_tab'])
                    ];
                }
            }
        }
        
        return $instance;
    }
}

