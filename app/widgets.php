<?php

/**
 * Custom Widgets
 */

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

/**
 * Blog List Widget
 * 显示博客文章列表
 */
class Blog_List_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'blog_list_widget',
            __('aripplesong - 博客列表', 'sage'),
            ['description' => __('显示最新的博客文章列表', 'sage')]
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
                                    <span><?php echo get_the_date(); ?></span>
                                </span>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else: ?>
                    <li class="col-span-<?php echo esc_attr($columns); ?> text-center text-base-content/50 py-8">暂无博客文章</li>
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
            <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('列数:', 'sage'); ?></label>
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
            <label for="<?php echo $this->get_field_id('show_see_all'); ?>"><?php _e('显示"查看全部"链接', 'sage'); ?></label>
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

/**
 * Subscribe Links Widget
 * 显示订阅链接按钮
 */
class Subscribe_Links_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'subscribe_links_widget',
            __('aripplesong - 订阅链接', 'sage'),
            ['description' => __('显示播客订阅平台链接', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'SUBSCRIBE';
        $apple_podcast_url = !empty($instance['apple_podcast_url']) ? $instance['apple_podcast_url'] : '';
        $spotify_url = !empty($instance['spotify_url']) ? $instance['spotify_url'] : '';
        $youtube_music_url = !empty($instance['youtube_music_url']) ? $instance['youtube_music_url'] : '';
        
        // 检查是否至少有一个链接
        $has_links = !empty($apple_podcast_url) || !empty($spotify_url) || !empty($youtube_music_url);
        
        if (!$has_links) {
            // 没有配置任何链接时不显示
            echo $args['after_widget'];
            return;
        }
        ?>
        <div class="card bg-base-100 w-full mt-4">
            <div class="card-body p-4">
                <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
                
                <?php if (!empty($apple_podcast_url)): ?>
                <a href="<?php echo esc_url($apple_podcast_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="btn bg-gradient-to-r from-gray-600 via-gray-800 to-black btn-sm text-white border-black transition-all duration-500 ease-in-out hover:from-black hover:via-gray-800 hover:to-gray-600">
                    <i data-lucide="podcast" class="w-4 h-4"></i>
                    Apple Podcast
                </a>
                <?php endif; ?>
                
                <?php if (!empty($spotify_url)): ?>
                <a href="<?php echo esc_url($spotify_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="btn bg-gradient-to-r from-green-400 via-green-500 to-[#03C755] btn-sm text-white border-[#00b544] transition-all duration-500 ease-in-out hover:from-[#03C755] hover:via-green-500 hover:to-green-400">
                    <i data-lucide="music" class="w-4 h-4"></i>
                    Spotify
                </a>
                <?php endif; ?>
                
                <?php if (!empty($youtube_music_url)): ?>
                <a href="<?php echo esc_url($youtube_music_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="btn bg-gradient-to-r from-yellow-300 via-yellow-400 to-[#FEE502] btn-sm text-[#181600] border-[#f1d800] transition-all duration-500 ease-in-out hover:from-[#FEE502] hover:via-yellow-400 hover:to-yellow-300">
                    <i data-lucide="youtube" class="w-4 h-4"></i>
                    Youtube Music
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'SUBSCRIBE';
        $apple_podcast_url = !empty($instance['apple_podcast_url']) ? $instance['apple_podcast_url'] : '';
        $spotify_url = !empty($instance['spotify_url']) ? $instance['spotify_url'] : '';
        $youtube_music_url = !empty($instance['youtube_music_url']) ? $instance['youtube_music_url'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('标题:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>"
                   placeholder="SUBSCRIBE">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('apple_podcast_url'); ?>">
                <?php _e('Apple Podcast 链接:', 'sage'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('apple_podcast_url'); ?>" 
                   name="<?php echo $this->get_field_name('apple_podcast_url'); ?>" 
                   type="url" 
                   value="<?php echo esc_attr($apple_podcast_url); ?>"
                   placeholder="https://podcasts.apple.com/...">
            <small class="description"><?php _e('留空则不显示该按钮', 'sage'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('spotify_url'); ?>">
                <?php _e('Spotify 链接:', 'sage'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('spotify_url'); ?>" 
                   name="<?php echo $this->get_field_name('spotify_url'); ?>" 
                   type="url" 
                   value="<?php echo esc_attr($spotify_url); ?>"
                   placeholder="https://open.spotify.com/...">
            <small class="description"><?php _e('留空则不显示该按钮', 'sage'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('youtube_music_url'); ?>">
                <?php _e('Youtube Music 链接:', 'sage'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('youtube_music_url'); ?>" 
                   name="<?php echo $this->get_field_name('youtube_music_url'); ?>" 
                   type="url" 
                   value="<?php echo esc_attr($youtube_music_url); ?>"
                   placeholder="https://music.youtube.com/...">
            <small class="description"><?php _e('留空则不显示该按钮', 'sage'); ?></small>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : 'SUBSCRIBE';
        $instance['apple_podcast_url'] = (!empty($new_instance['apple_podcast_url'])) ? esc_url_raw($new_instance['apple_podcast_url']) : '';
        $instance['spotify_url'] = (!empty($new_instance['spotify_url'])) ? esc_url_raw($new_instance['spotify_url']) : '';
        $instance['youtube_music_url'] = (!empty($new_instance['youtube_music_url'])) ? esc_url_raw($new_instance['youtube_music_url']) : '';
        return $instance;
    }
}

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

/**
 * Authors Widget
 * 显示作者列表（成员和访客）
 */
class Authors_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'authors_widget',
            __('aripplesong - 作者列表', 'sage'),
            ['description' => __('显示成员和访客作者列表', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $members_title = !empty($instance['members_title']) ? $instance['members_title'] : 'Members';
        $guests_title = !empty($instance['guests_title']) ? $instance['guests_title'] : 'Guests';
        $show_members = isset($instance['show_members']) ? $instance['show_members'] : true;
        $show_guests = isset($instance['show_guests']) ? $instance['show_guests'] : true;
        
        // 获取成员（管理员、编辑、作者）
        $members = get_users([
            'role__in' => ['administrator', 'editor', 'author'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);
        
        // 获取访客（投稿者）
        $contributors = get_users([
            'role' => 'contributor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);
        ?>
        <div class="bg-base-100 rounded-lg p-4">
            <?php if ($show_members && !empty($members)): ?>
            <h3 class="text-sm font-bold text-base-content/50"><?php echo esc_html($members_title); ?></h3>
            <div class="grid grid-flow-row gap-2 mt-4">
                <?php foreach($members as $user): ?>
                    <?php
                    $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
                    $post_count = function_exists('calculate_user_post_count') 
                        ? calculate_user_post_count($user->ID) 
                        : count_user_posts($user->ID, 'post') + count_user_posts($user->ID, 'podcast');
                    ?>
                    <a href="<?php echo get_author_posts_url($user->ID); ?>" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
                        <div class="avatar">
                            <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
                                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>" />
                            </div>
                        </div>
                        <span class="text-xs"><?php echo esc_html($user->display_name); ?></span>
                        <span class="text-xs text-base-content/50"><?php echo esc_html($post_count); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($show_guests && !empty($contributors)): ?>
            <h3 class="text-sm font-bold text-base-content/50 <?php echo ($show_members && !empty($members)) ? 'mt-4' : ''; ?>"><?php echo esc_html($guests_title); ?></h3>
            <div class="grid grid-flow-row gap-2 mt-4">
                <?php foreach($contributors as $user): ?>
                    <?php
                    $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
                    $post_count = function_exists('calculate_user_post_count') 
                        ? calculate_user_post_count($user->ID) 
                        : count_user_posts($user->ID, 'post') + count_user_posts($user->ID, 'podcast');
                    ?>
                    <a href="<?php echo get_author_posts_url($user->ID); ?>" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
                        <div class="avatar">
                            <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
                                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>" />
                            </div>
                        </div>
                        <span class="text-xs"><?php echo esc_html($user->display_name); ?></span>
                        <span class="text-xs text-base-content/50"><?php echo esc_html($post_count); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if ((!$show_members || empty($members)) && (!$show_guests || empty($contributors))): ?>
            <div class="text-center py-8">
                <div class="text-base-content/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p class="text-sm font-medium"><?php _e('暂无作者', 'sage'); ?></p>
                    <p class="text-xs mt-1"><?php _e('添加用户后将显示在这里', 'sage'); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $members_title = !empty($instance['members_title']) ? $instance['members_title'] : 'Members';
        $guests_title = !empty($instance['guests_title']) ? $instance['guests_title'] : 'Guests';
        $show_members = isset($instance['show_members']) ? $instance['show_members'] : true;
        $show_guests = isset($instance['show_guests']) ? $instance['show_guests'] : true;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('members_title'); ?>"><?php _e('成员标题:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('members_title'); ?>" 
                   name="<?php echo $this->get_field_name('members_title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($members_title); ?>">
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_members); ?> 
                   id="<?php echo $this->get_field_id('show_members'); ?>" 
                   name="<?php echo $this->get_field_name('show_members'); ?>">
            <label for="<?php echo $this->get_field_id('show_members'); ?>"><?php _e('显示成员（管理员、编辑、作者）', 'sage'); ?></label>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('guests_title'); ?>"><?php _e('访客标题:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('guests_title'); ?>" 
                   name="<?php echo $this->get_field_name('guests_title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($guests_title); ?>">
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_guests); ?> 
                   id="<?php echo $this->get_field_id('show_guests'); ?>" 
                   name="<?php echo $this->get_field_name('show_guests'); ?>">
            <label for="<?php echo $this->get_field_id('show_guests'); ?>"><?php _e('显示访客（投稿者）', 'sage'); ?></label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['members_title'] = (!empty($new_instance['members_title'])) ? sanitize_text_field($new_instance['members_title']) : 'Members';
        $instance['guests_title'] = (!empty($new_instance['guests_title'])) ? sanitize_text_field($new_instance['guests_title']) : 'Guests';
        $instance['show_members'] = (!empty($new_instance['show_members'])) ? 1 : 0;
        $instance['show_guests'] = (!empty($new_instance['show_guests'])) ? 1 : 0;
        return $instance;
    }
}

/**
 * Register widgets
 */
add_action('widgets_init', function() {
    register_widget('Banner_Carousel_Widget');
    register_widget('Podcast_List_Widget');
    register_widget('Blog_List_Widget');
    register_widget('Subscribe_Links_Widget');
    register_widget('Tags_Cloud_Widget');
    register_widget('Authors_Widget');
});

/**
 * Set default widgets for home-main sidebar
 */
function set_default_home_widgets() {
    // 检查是否已经设置过默认 widgets
    $default_widgets_set = get_option('aripplesong_home_widgets_set', false);
    
    if ($default_widgets_set) {
        return; // 已经设置过，不再重复设置
    }
    
    // 获取当前所有侧边栏的 widgets
    $sidebars_widgets = get_option('sidebars_widgets', []);
    
    // 检查 home-main 是否已经有 widgets
    if (isset($sidebars_widgets['home-main']) && !empty($sidebars_widgets['home-main'])) {
        return; // 已经有 widgets 了，不覆盖
    }
    
    // 创建 Banner Widget 实例（空横幅，显示占位提示）
    $banner_widget = new Banner_Carousel_Widget();
    $banner_options = get_option($banner_widget->option_name, []);
    $banner_instance_id = count($banner_options) + 1;
    $banner_options[$banner_instance_id] = [
        'slides' => [] // 空数组，将显示占位提示
    ];
    update_option($banner_widget->option_name, $banner_options);
    
    // 创建 Podcast Widget 实例
    $podcast_widget = new Podcast_List_Widget();
    $podcast_options = get_option($podcast_widget->option_name, []);
    $podcast_instance_id = count($podcast_options) + 1;
    $podcast_options[$podcast_instance_id] = [
        'title' => 'PODCAST',
        'posts_per_page' => 3,
        'show_see_all' => 1
    ];
    update_option($podcast_widget->option_name, $podcast_options);
    
    // 创建 Blog Widget 实例
    $blog_widget = new Blog_List_Widget();
    $blog_options = get_option($blog_widget->option_name, []);
    $blog_instance_id = count($blog_options) + 1;
    $blog_options[$blog_instance_id] = [
        'title' => 'BLOG',
        'posts_per_page' => 6,
        'show_see_all' => 1,
        'columns' => 3
    ];
    update_option($blog_widget->option_name, $blog_options);
    
    // 将 widgets 添加到 home-main 侧边栏
    $sidebars_widgets['home-main'] = [
        'banner_carousel_widget-' . $banner_instance_id,
        'podcast_list_widget-' . $podcast_instance_id,
        'blog_list_widget-' . $blog_instance_id
    ];
    
    // 更新侧边栏 widgets
    update_option('sidebars_widgets', $sidebars_widgets);
    
    // 标记为已设置
    update_option('aripplesong_home_widgets_set', true);
}

// 在主题激活时设置默认 widgets
add_action('after_switch_theme', 'set_default_home_widgets');

// 如果 home-main 侧边栏为空，也尝试设置默认 widgets（首次安装时）
add_action('widgets_init', function() {
    // 延迟执行，确保所有 widgets 都已注册
    if (!did_action('after_switch_theme')) {
        set_default_home_widgets();
    }
}, 100);

/**
 * Add custom CSS for widgets admin page
 * 为后台 widgets 页面添加自定义样式，确保 DaisyUI 主题变量可用
 */
add_action('admin_enqueue_scripts', function($hook) {
    // 只在 widgets 页面加载
    if ($hook !== 'widgets.php' && $hook !== 'customize.php') {
        return;
    }
    
    // 加载 WordPress 媒体上传器
    wp_enqueue_media();
    
    // 添加内联样式以确保主题在后台也能正常工作
    $custom_css = '
        /* 确保 widgets 容器支持 DaisyUI 主题 */
        #widgets-editor,
        .widgets-holder-wrap,
        .widget-inside {
            --fallback-b1: oklch(0.9686 0.0124 105.4518);
            --fallback-bc: oklch(0.278078 0.029596 256.848);
            --fallback-b2: oklch(0.9529 0.0116 104.8327);
            --fallback-b3: oklch(0.9333 0.0108 103.7534);
        }
        
        /* 确保 widget 内容区域样式正确 */
        .widget .widget-inside .widget-content {
            max-width: 100%;
            overflow-x: auto;
        }
        
        /* 让 widget 预览区域有正确的背景 */
        .widget-content {
            background: oklch(0.9686 0.0124 105.4518);
        }
    ';
    
    wp_add_inline_style('wp-admin', $custom_css);
    
    // 添加 Banner Widget 的 JavaScript
    $banner_widget_js = "
    (function($) {
        'use strict';
        
        var bannerWidgetHandlersInitialized = false;
        
        // 初始化函数
        function initBannerWidget() {
            if (bannerWidgetHandlersInitialized) {
                return;
            }
            
            bannerWidgetHandlersInitialized = true;
            
            // 处理添加横幅按钮
            $(document).on('click', '.banner-add-slide', function(e) {
                e.preventDefault();
                var button = $(this);
                var widgetId = button.data('widget-id');
                var container = $('#' + widgetId + '_container');
                var slideCount = container.find('.banner-slide-item').length;
                var widgetForm = button.closest('.banner-carousel-widget-form');
                var fieldNamePrefix = widgetForm.data('field-prefix');
                
                if (!fieldNamePrefix) {
                    // 如果无法获取前缀，则不继续执行
                    return;
                }
                
                var slideHtml = '<div class=\"banner-slide-item\" style=\"margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;\">' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">图片地址:</label>' +
                        '<div style=\"display: flex; gap: 5px;\">' +
                            '<input type=\"text\" class=\"widefat banner-image-url\" name=\"' + fieldNamePrefix + '[' + slideCount + '][image]\" placeholder=\"图片URL\" style=\"flex: 1;\">' +
                            '<button type=\"button\" class=\"button banner-select-image\" style=\"flex-shrink: 0;\">选择图片</button>' +
                        '</div>' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">链接地址 (可选):</label>' +
                        '<input type=\"url\" class=\"widefat banner-link-url\" name=\"' + fieldNamePrefix + '[' + slideCount + '][link]\" placeholder=\"https://example.com\">' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">链接打开方式:</label>' +
                        '<select class=\"widefat banner-link-target\" name=\"' + fieldNamePrefix + '[' + slideCount + '][link_target]\">' +
                            '<option value=\"_self\">当前页面</option>' +
                            '<option value=\"_blank\">新标签页</option>' +
                        '</select>' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">描述:</label>' +
                        '<input type=\"text\" class=\"widefat banner-description\" name=\"' + fieldNamePrefix + '[' + slideCount + '][description]\" placeholder=\"图片描述\">' +
                    '</div>' +
                    '<div style=\"text-align: right;\">' +
                        '<button type=\"button\" class=\"button button-link button-link-delete banner-remove-slide\" style=\"color: #b32d2e;\">删除</button>' +
                    '</div>' +
                '</div>';
                
                container.append(slideHtml);
                
                var flagInput = widgetForm.find('.banner-slides-flag');
                if (flagInput.length) {
                    flagInput.trigger('change');
                }
            });
            
            // 处理删除横幅按钮
            $(document).on('click', '.banner-remove-slide', function(e) {
                e.preventDefault();
                var slideItem = $(this).closest('.banner-slide-item');
                var container = slideItem.closest('.banner-slides-container');
                var widgetForm = slideItem.closest('.banner-carousel-widget-form');
                
                // 如果只剩一个，清空内容而不删除
                if (container.find('.banner-slide-item').length <= 1) {
                    slideItem.find('input').val('');
                    slideItem.find('select').val('_self');
                    slideItem.find('.banner-image-preview').remove();
                } else {
                    slideItem.remove();
                }
                
                var flagInput = widgetForm.find('.banner-slides-flag');
                if (flagInput.length) {
                    flagInput.trigger('change');
                }
            });
            
            // 处理选择图片按钮
            $(document).on('click', '.banner-select-image', function(e) {
                e.preventDefault();
                var button = $(this);
                var slideItem = button.closest('.banner-slide-item');
                var imageInput = slideItem.find('.banner-image-url');
                
                // 创建媒体上传器
                var mediaUploader;
                
                // 如果已经存在实例，重用它
                if (typeof wp.media.frames.bannerImageUploader === 'undefined') {
                    mediaUploader = wp.media({
                        title: '选择横幅图片',
                        button: {
                            text: '使用此图片'
                        },
                        multiple: false
                    });
                    wp.media.frames.bannerImageUploader = mediaUploader;
                } else {
                    mediaUploader = wp.media.frames.bannerImageUploader;
                }
                
                // 清除之前的事件监听器，避免重复触发
                mediaUploader.off('select');
                
                // 当选择图片时
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    imageInput.val(attachment.url);
                    
                    // 更新或添加预览
                    var preview = slideItem.find('.banner-image-preview');
                    if (preview.length) {
                        preview.find('img').attr('src', attachment.url);
                    } else {
                        imageInput.closest('div').after(
                            '<div class=\"banner-image-preview\" style=\"margin-top: 8px;\">' +
                                '<img src=\"' + attachment.url + '\" style=\"max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;\">' +
                            '</div>'
                        );
                    }
                    
                    // 触发 change 事件以便 widget 知道有变化
                    imageInput.trigger('change');
                });
                
                mediaUploader.open();
            });
            
            // 处理图片 URL 输入变化
            $(document).on('input', '.banner-image-url', function() {
                var input = $(this);
                var slideItem = input.closest('.banner-slide-item');
                var preview = slideItem.find('.banner-image-preview');
                var url = input.val().trim();
                
                if (url) {
                    if (preview.length) {
                        preview.find('img').attr('src', url);
                    } else {
                        input.closest('div').after(
                            '<div class=\"banner-image-preview\" style=\"margin-top: 8px;\">' +
                                '<img src=\"' + url + '\" style=\"max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;\">' +
                            '</div>'
                        );
                    }
                } else {
                    preview.remove();
                }
            });
        }
        
        // 页面加载时初始化
        $(document).ready(function() {
            initBannerWidget();
        });
        
        // 当 widget 被添加或更新时重新初始化
        $(document).on('widget-added widget-updated', function(e, widget) {
            initBannerWidget();
        });
        
    })(jQuery);
    ";
    
    wp_add_inline_script('jquery', $banner_widget_js);
});

/**
 * Add theme setup script to widgets preview and admin pages
 * 为 widgets 预览和后台页面添加主题设置脚本
 */
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ($screen && ($screen->id === 'widgets' || $screen->id === 'customize')) {
        ?>
        <script>
        (function() {
            // 设置主题属性的函数
            function setupTheme(doc) {
                if (doc && doc.documentElement) {
                    doc.documentElement.setAttribute('data-theme', 'retro');
                }
                if (doc && doc.body) {
                    doc.body.classList.add('bg-base-200');
                }
            }
            
            // 为主文档设置主题
            setupTheme(document);
            
            // 为 widgets 容器添加主题属性
            function setupWidgetContainers() {
                const widgetsEditor = document.getElementById('widgets-editor');
                if (widgetsEditor) {
                    widgetsEditor.setAttribute('data-theme', 'retro');
                }
                
                const widgetContainers = document.querySelectorAll('.widgets-holder-wrap, .widget-inside');
                widgetContainers.forEach(function(container) {
                    container.setAttribute('data-theme', 'retro');
                });
            }
            
            // 为 iframe 设置主题
            function setupIframeTheme() {
                const iframes = document.querySelectorAll('iframe');
                iframes.forEach(function(iframe) {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow?.document;
                        if (iframeDoc) {
                            setupTheme(iframeDoc);
                        }
                    } catch (e) {
                        // 跨域 iframe 无法访问，忽略
                    }
                });
            }
            
            // 初始设置
            setupWidgetContainers();
            setupIframeTheme();
            
            // 监听 DOM 变化
            const observer = new MutationObserver(function() {
                setupWidgetContainers();
                setupIframeTheme();
                
                // 重新初始化 Lucide 图标
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        })();
        </script>
        <?php
    }
});

/**
 * Add theme setup script to customize preview iframe
 * 为自定义器预览 iframe 添加主题设置脚本
 */
add_action('wp_head', function() {
    if (is_customize_preview()) {
        ?>
        <script>
        (function() {
            if (document.documentElement) {
                document.documentElement.setAttribute('data-theme', 'retro');
            }
            if (document.body) {
                document.body.classList.add('bg-base-200');
            }
        })();
        </script>
        <?php
    }
}, 999);


