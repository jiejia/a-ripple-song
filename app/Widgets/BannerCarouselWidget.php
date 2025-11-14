<?php

/**
 * Banner Carousel Widget
 * 显示图片轮播横幅
 */
class Banner_Carousel_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'banner_carousel_widget',
            __('aripplesong - 横幅轮播', 'sage'),
            ['description' => __('显示图片轮播横幅', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $slides = isset($instance['slides']) ? $instance['slides'] : [];
        
        if (empty($slides)) {
            // 没有横幅时显示占位提示
            ?>
            <div class="w-full rounded-lg bg-base-100 p-4 pb-2">
                <div class="w-full h-48 rounded-lg bg-base-200 flex items-center justify-center">
                    <div class="text-center text-base-content/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-medium"><?php _e('暂无横幅', 'sage'); ?></p>
                        <p class="text-xs mt-1"><?php _e('请在后台添加横幅内容', 'sage'); ?></p>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="w-full rounded-lg bg-base-100 p-4 pb-2">
                <div class="carousel w-full rounded-lg">
                    <?php foreach ($slides as $index => $slide): ?>
                        <?php 
                        $slide_id = 'slide' . ($index + 1);
                        $prev_slide = 'slide' . (($index - 1 + count($slides)) % count($slides) + 1);
                        $next_slide = 'slide' . (($index + 1) % count($slides) + 1);
                        $image_url = isset($slide['image']) ? $slide['image'] : '';
                        $link_url = isset($slide['link']) ? $slide['link'] : '';
                        $description = isset($slide['description']) ? $slide['description'] : '';
                        $link_target = isset($slide['link_target']) ? $slide['link_target'] : '_self';
                        ?>
                        <div id="<?php echo esc_attr($slide_id); ?>" class="carousel-item relative w-full rounded-lg">
                            <?php if ($link_url): ?>
                                <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" class="w-full">
                                    <img
                                        src="<?php echo esc_url($image_url); ?>"
                                        class="w-full h-48 object-cover rounded-lg"
                                        alt="<?php echo esc_attr($description); ?>" />
                                </a>
                            <?php else: ?>
                                <img
                                    src="<?php echo esc_url($image_url); ?>"
                                    class="w-full h-48 object-cover rounded-lg"
                                    alt="<?php echo esc_attr($description); ?>" />
                            <?php endif; ?>
                            <?php if (count($slides) > 1): ?>
                            <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
                                <a href="#<?php echo esc_attr($prev_slide); ?>" class="btn btn-circle btn-xs">❮</a>
                                <a href="#<?php echo esc_attr($next_slide); ?>" class="btn btn-circle btn-xs">❯</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
        
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
                <strong><?php _e('横幅幻灯片:', 'sage'); ?></strong>
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
                                <?php _e('图片地址:', 'sage'); ?>
                            </label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" 
                                       class="widefat banner-image-url" 
                                       name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][image]" 
                                       value="<?php echo esc_attr($image); ?>" 
                                       placeholder="<?php _e('图片URL', 'sage'); ?>" 
                                       style="flex: 1;">
                                <button type="button" 
                                        class="button banner-select-image" 
                                        style="flex-shrink: 0;">
                                    <?php _e('选择图片', 'sage'); ?>
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
                                <?php _e('链接地址 (可选):', 'sage'); ?>
                            </label>
                            <input type="url" 
                                   class="widefat banner-link-url" 
                                   name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][link]" 
                                   value="<?php echo esc_attr($link); ?>" 
                                   placeholder="<?php _e('https://example.com', 'sage'); ?>">
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php _e('链接打开方式:', 'sage'); ?>
                            </label>
                            <select class="widefat banner-link-target" 
                                    name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][link_target]">
                                <option value="_self" <?php selected($link_target, '_self'); ?>>
                                    <?php _e('当前页面', 'sage'); ?>
                                </option>
                                <option value="_blank" <?php selected($link_target, '_blank'); ?>>
                                    <?php _e('新标签页', 'sage'); ?>
                                </option>
                            </select>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php _e('描述:', 'sage'); ?>
                            </label>
                            <input type="text" 
                                   class="widefat banner-description" 
                                   name="<?php echo $this->get_field_name('slides'); ?>[<?php echo $index; ?>][description]" 
                                   value="<?php echo esc_attr($description); ?>" 
                                   placeholder="<?php _e('图片描述', 'sage'); ?>">
                        </div>
                        <div style="text-align: right;">
                            <button type="button" class="button button-link button-link-delete banner-remove-slide" style="color: #b32d2e;">
                                <?php _e('删除', 'sage'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" class="banner-slides-flag" name="<?php echo esc_attr($field_prefix); ?>[__flag]" value="1">
            <p>
                <button type="button" class="button banner-add-slide" data-widget-id="<?php echo esc_attr($widget_id); ?>">
                    <?php _e('+ 添加横幅', 'sage'); ?>
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
                
                // 只保存有图片地址的幻灯片
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

