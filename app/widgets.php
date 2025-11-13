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
            __('横幅轮播', 'sage'),
            ['description' => __('显示图片轮播横幅', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $slides = isset($instance['slides']) ? $instance['slides'] : [];
        
        if (empty($slides)) {
            // 默认幻灯片
            $slides = [
                [
                    'url' => 'https://cdn.pixabay.com/photo/2023/12/03/15/23/ai-generated-8427689_640.jpg',
                    'alt' => '温暖的棕色调风景'
                ],
                [
                    'url' => 'https://cdn.pixabay.com/photo/2024/09/24/09/47/ai-generated-9070891_640.png',
                    'alt' => '米色建筑背景'
                ]
            ];
        }
        ?>
        <div class="w-full rounded-lg bg-base-100 p-4 pb-2">
            <div class="carousel w-full rounded-lg">
                <?php foreach ($slides as $index => $slide): ?>
                    <?php 
                    $slide_id = 'slide' . ($index + 1);
                    $prev_slide = 'slide' . (($index - 1 + count($slides)) % count($slides) + 1);
                    $next_slide = 'slide' . (($index + 1) % count($slides) + 1);
                    ?>
                    <div id="<?php echo esc_attr($slide_id); ?>" class="carousel-item relative w-full rounded-lg">
                        <img
                            src="<?php echo esc_url($slide['url']); ?>"
                            class="w-full h-48 object-cover rounded-lg"
                            alt="<?php echo esc_attr($slide['alt']); ?>" />
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
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $slides = isset($instance['slides']) ? $instance['slides'] : [
            ['url' => '', 'alt' => '']
        ];
        ?>
        <div class="banner-carousel-widget-form">
            <p>
                <strong><?php _e('幻灯片图片:', 'sage'); ?></strong><br>
                <small><?php _e('每行输入一张图片的URL和描述，用竖线(|)分隔，例如: https://example.com/image.jpg|图片描述', 'sage'); ?></small>
            </p>
            <p>
                <textarea 
                    class="widefat" 
                    rows="5" 
                    id="<?php echo $this->get_field_id('slides_data'); ?>" 
                    name="<?php echo $this->get_field_name('slides_data'); ?>"><?php 
                    foreach ($slides as $slide) {
                        echo esc_textarea($slide['url'] . '|' . $slide['alt']) . "\n";
                    }
                ?></textarea>
            </p>
        </div>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $slides_data = isset($new_instance['slides_data']) ? $new_instance['slides_data'] : '';
        $lines = explode("\n", $slides_data);
        $slides = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = explode('|', $line);
            $slides[] = [
                'url' => isset($parts[0]) ? esc_url_raw(trim($parts[0])) : '',
                'alt' => isset($parts[1]) ? sanitize_text_field(trim($parts[1])) : ''
            ];
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
            __('播客列表', 'sage'),
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
                        <li>
                            <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
                                <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center" 
                                     x-data 
                                     data-episode='<?php echo esc_attr(wp_json_encode($episode_data)); ?>'>
                                    <div>
                                        <a href="<?php the_permalink(); ?>" class="block w-10 h-10 rounded-lg overflow-hidden">
                                            <?php if (has_post_thumbnail()): ?>
                                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); ?>" 
                                                     alt="<?php the_title_attribute(); ?>" 
                                                     class="w-10 h-10 rounded-md object-cover" />
                                            <?php else: ?>
                                                <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" 
                                                     alt="<?php the_title_attribute(); ?>" 
                                                     class="w-10 h-10 rounded-md" />
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="grid grid-flow-row gap-1">
                                        <h4 class="text-md font-bold">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h4>
                                        <p class="text-xs text-base-content/50">
                                            <span><?php echo get_the_date(); ?></span>
                                            <span>•</span>
                                            <span><?php echo get_post_meta(get_the_ID(), '_post_views_count', true) ?: 0; ?> views</span>
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <?php if ($audio_file): ?>
                                            <button type="button" 
                                                    @click="$store.player.addEpisode(JSON.parse($el.closest('[data-episode]').dataset.episode))"
                                                    class="cursor-pointer hover:text-primary transition-colors"
                                                    title="加入播放列表">
                                                <i data-lucide="plus-circle" class="text-xs h-4"></i>
                                            </button>
                                        <?php endif; ?>
                                        <i data-lucide="ellipsis-vertical" class="text-xs h-4 cursor-pointer"></i>
                                    </div>
                                </div>
                            </div>
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
            __('博客列表', 'sage'),
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
 * Register widgets
 */
add_action('widgets_init', function() {
    register_widget('Banner_Carousel_Widget');
    register_widget('Podcast_List_Widget');
    register_widget('Blog_List_Widget');
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
    
    // 创建 Banner Widget 实例
    $banner_widget = new Banner_Carousel_Widget();
    $banner_options = get_option($banner_widget->option_name, []);
    $banner_instance_id = count($banner_options) + 1;
    $banner_options[$banner_instance_id] = [
        'slides' => [
            [
                'url' => 'https://cdn.pixabay.com/photo/2023/12/03/15/23/ai-generated-8427689_640.jpg',
                'alt' => '温暖的棕色调风景'
            ],
            [
                'url' => 'https://cdn.pixabay.com/photo/2024/09/24/09/47/ai-generated-9070891_640.png',
                'alt' => '米色建筑背景'
            ]
        ]
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
 * Enqueue frontend styles and scripts in widgets admin page
 * 在后台 widgets 页面加载前端样式和脚本，让预览效果和前台一致
 */
add_action('admin_enqueue_scripts', function($hook) {
    // 只在 widgets 页面加载
    if ($hook !== 'widgets.php' && $hook !== 'customize.php') {
        return;
    }
    
    // 使用 Vite 加载主题样式和脚本
    if (class_exists('\Illuminate\Support\Facades\Vite')) {
        try {
            $css_url = \Illuminate\Support\Facades\Vite::asset('resources/css/app.css');
            $js_url = \Illuminate\Support\Facades\Vite::asset('resources/js/app.js');
            
            if ($css_url) {
                wp_enqueue_style('aripplesong-widgets-style', $css_url, [], null);
            }
            
            if ($js_url) {
                wp_enqueue_script('aripplesong-widgets-script', $js_url, [], null, true);
            }
        } catch (\Exception $e) {
            // 如果 Vite 资源不可用，记录错误但不中断
            error_log('Failed to load Vite assets in widgets admin: ' . $e->getMessage());
        }
    }
    
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
    
    wp_add_inline_style('aripplesong-widgets-style', $custom_css);
});

/**
 * Add data-theme attribute to widgets admin page
 * 为 widgets 后台页面添加主题属性
 */
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ($screen && ($screen->id === 'widgets' || $screen->id === 'customize')) {
        ?>
        <script>
        (function() {
            // 设置 data-theme 属性以匹配前端
            document.documentElement.setAttribute('data-theme', 'retro');
            
            // 为 widgets 容器添加必要的类
            const widgetsEditor = document.getElementById('widgets-editor');
            if (widgetsEditor) {
                widgetsEditor.setAttribute('data-theme', 'retro');
            }
            
            // 为所有 widget 容器添加主题属性
            const widgetContainers = document.querySelectorAll('.widgets-holder-wrap, .widget-inside');
            widgetContainers.forEach(function(container) {
                container.setAttribute('data-theme', 'retro');
            });
            
            // 监听 DOM 变化，为新添加的 widgets 也设置主题
            const observer = new MutationObserver(function(mutations) {
                widgetContainers.forEach(function(container) {
                    container.setAttribute('data-theme', 'retro');
                });
                
                // 重新初始化 Lucide 图标（如果已加载）
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
