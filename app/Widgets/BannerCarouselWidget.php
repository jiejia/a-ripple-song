<?php

/**
 * Banner Carousel Widget
 * Display a banner carousel.
 */
class Banner_Carousel_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'banner_carousel_widget',
            __('aripplesong - Banner Carousel', 'a-ripple-song'),
            ['description' => __('Display banner carousel with images', 'a-ripple-song')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $slides = isset($instance['slides']) ? $instance['slides'] : [];
        $carousel_id = 'banner-carousel-' . $this->id;
        
        echo \Roots\view('widgets.banner-carousel', [
            'slides' => $slides,
            'carousel_id' => $carousel_id,
        ])->render();
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $slides = isset($instance['slides']) && is_array($instance['slides']) ? $instance['slides'] : [];
        
        if (empty($slides)) {
            $slides = [
                ['image' => '', 'link' => '', 'description' => '', 'link_target' => '_self']
            ];
        }
        
        $widget_id = $this->get_field_id('slides');
        $field_prefix = $this->get_field_name('slides');
        ?>
        <div
            class="banner-carousel-widget-form"
            data-widget-id="<?php echo esc_attr($widget_id); ?>"
            data-field-prefix="<?php echo esc_attr($field_prefix); ?>"
        >
            <p>
                <strong><?php _e('Banner Slides:', 'a-ripple-song'); ?></strong>
            </p>
            <div class="banner-slides-container" id="<?php echo esc_attr($widget_id); ?>_container">
                <?php foreach ($slides as $index => $slide): ?>
                    <?php 
                    $image = isset($slide['image']) ? $slide['image'] : '';
                    $link = isset($slide['link']) ? $slide['link'] : '';
                    $description = isset($slide['description']) ? $slide['description'] : '';
                    $link_target = isset($slide['link_target']) ? $slide['link_target'] : '_self';
                    ?>
                    <div class="banner-slide-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php _e('Image URL:', 'a-ripple-song'); ?>
                            </label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" 
                                       class="widefat banner-image-url" 
                                       name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][image]" 
                                       value="<?php echo esc_attr($image); ?>" 
                                       placeholder="<?php _e('Image URL', 'a-ripple-song'); ?>" 
                                       style="flex: 1;">
                                <button type="button" 
                                        class="button banner-select-image" 
                                        style="flex-shrink: 0;">
                                    <?php _e('Select Image', 'a-ripple-song'); ?>
                                </button>
                            </div>
                            <?php if ($image): ?>
                                <div class="banner-image-preview" style="margin-top: 8px;">
                                    <img src="<?php echo esc_url($image); ?>" style="max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php _e('Link URL (optional):', 'a-ripple-song'); ?>
                            </label>
                            <input type="url" 
                                   class="widefat banner-link-url" 
                                   name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][link]" 
                                   value="<?php echo esc_attr($link); ?>" 
                                   placeholder="<?php _e('https://example.com', 'a-ripple-song'); ?>">
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php _e('Link Target:', 'a-ripple-song'); ?>
                            </label>
                            <select class="widefat banner-link-target" 
                                    name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][link_target]">
                                <option value="_self" <?php selected($link_target, '_self'); ?>>
                                    <?php _e('Current Page', 'a-ripple-song'); ?>
                                </option>
                                <option value="_blank" <?php selected($link_target, '_blank'); ?>>
                                    <?php _e('New Tab', 'a-ripple-song'); ?>
                                </option>
                            </select>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php _e('Description:', 'a-ripple-song'); ?>
                            </label>
                            <input type="text" 
                                   class="widefat banner-description" 
                                   name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][description]" 
                                   value="<?php echo esc_attr($description); ?>" 
                                   placeholder="<?php _e('Image description', 'a-ripple-song'); ?>">
                        </div>
                        <div style="text-align: right;">
                            <button type="button" class="button button-link button-link-delete banner-remove-slide" style="color: #b32d2e;">
                                <?php _e('Delete', 'a-ripple-song'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" class="banner-slides-flag" name="<?php echo esc_attr($field_prefix); ?>[__flag]" value="1">
            <p>
                <button type="button" class="button banner-add-slide" data-widget-id="<?php echo esc_attr($widget_id); ?>">
                    <?php _e('+ Add Banner', 'a-ripple-song'); ?>
                </button>
            </p>
        </div>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $slides = [];
        
        if (isset($new_instance['slides']) && is_array($new_instance['slides'])) {
            foreach ($new_instance['slides'] as $key => $slide) {
                if ($key === '__flag') {
                    continue;
                }
                
                // Only store slides with an image URL.
                if (!empty($slide['image'])) {
                    $slides[] = [
                        'image' => esc_url_raw($slide['image']),
                        'link' => !empty($slide['link']) ? esc_url_raw($slide['link']) : '',
                        'description' => isset($slide['description']) ? sanitize_text_field($slide['description']) : '',
                        'link_target' => isset($slide['link_target']) && in_array($slide['link_target'], ['_self', '_blank']) ? $slide['link_target'] : '_self'
                    ];
                }
            }
        }
        
        $instance['slides'] = $slides;
        return $instance;
    }
}
