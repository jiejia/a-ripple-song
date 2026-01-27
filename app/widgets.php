<?php

/**
 * Widgets Loader
 * 加载和注册所有自定义 Widgets
 */

// Podcast widgets are only available when the companion plugin is active.
$podcast_enabled = function_exists('aripplesong_podcast_features_enabled') && aripplesong_podcast_features_enabled();

// 自动加载所有 Widget 类文件
$widget_files = [
    __DIR__ . '/Widgets/BannerCarouselWidget.php',
    __DIR__ . '/Widgets/BlogListWidget.php',
    __DIR__ . '/Widgets/TagsCloudWidget.php',
    __DIR__ . '/Widgets/AuthorsWidget.php',
    __DIR__ . '/Widgets/FooterLinksWidget.php',
];

if ($podcast_enabled) {
    $widget_files[] = __DIR__ . '/Widgets/PodcastListWidget.php';
    $widget_files[] = __DIR__ . '/Widgets/SubscribeLinksWidget.php';
}

foreach ($widget_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

/**
 * Register all widgets
 */
add_action('widgets_init', function() {
    register_widget('Banner_Carousel_Widget');
    register_widget('Blog_List_Widget');
    register_widget('Tags_Cloud_Widget');
    register_widget('Authors_Widget');
    register_widget('Footer_Links_Widget');

    if (function_exists('aripplesong_podcast_features_enabled') && aripplesong_podcast_features_enabled()) {
        if (class_exists('Podcast_List_Widget')) {
            register_widget('Podcast_List_Widget');
        }
        if (class_exists('Subscribe_Links_Widget')) {
            register_widget('Subscribe_Links_Widget');
        }
    }
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
    if (isset($sidebars_widgets[\App\Theme::SIDEBAR_HOME_MAIN]) && !empty($sidebars_widgets[\App\Theme::SIDEBAR_HOME_MAIN])) {
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

    $podcast_instance_id = null;
    if (function_exists('aripplesong_podcast_features_enabled') && aripplesong_podcast_features_enabled() && class_exists('Podcast_List_Widget')) {
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
    }
    
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
    $home_widgets = [
        'banner_carousel_widget-' . $banner_instance_id,
        'blog_list_widget-' . $blog_instance_id
    ];

    if (!empty($podcast_instance_id)) {
        array_splice($home_widgets, 1, 0, ['podcast_list_widget-' . $podcast_instance_id]);
    }

    $sidebars_widgets[\App\Theme::SIDEBAR_HOME_MAIN] = $home_widgets;
    
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
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">" . esc_js(__('Image URL:', 'a-ripple-song')) . "</label>' +
                        '<div style=\"display: flex; gap: 5px;\">' +
                            '<input type=\"text\" class=\"widefat banner-image-url\" name=\"' + fieldNamePrefix + '[' + slideCount + '][image]\" placeholder=\"" . esc_js(__('Image URL', 'a-ripple-song')) . "\" style=\"flex: 1;\">' +
                            '<button type=\"button\" class=\"button banner-select-image\" style=\"flex-shrink: 0;\">" . esc_js(__('Select Image', 'a-ripple-song')) . "</button>' +
                        '</div>' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">" . esc_js(__('Link URL (optional):', 'a-ripple-song')) . "</label>' +
                        '<input type=\"url\" class=\"widefat banner-link-url\" name=\"' + fieldNamePrefix + '[' + slideCount + '][link]\" placeholder=\"https://example.com\">' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">" . esc_js(__('Link Target:', 'a-ripple-song')) . "</label>' +
                        '<select class=\"widefat banner-link-target\" name=\"' + fieldNamePrefix + '[' + slideCount + '][link_target]\">' +
                            '<option value=\"_self\">" . esc_js(__('Current Page', 'a-ripple-song')) . "</option>' +
                            '<option value=\"_blank\">" . esc_js(__('New Tab', 'a-ripple-song')) . "</option>' +
                        '</select>' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">" . esc_js(__('Description:', 'a-ripple-song')) . "</label>' +
                        '<input type=\"text\" class=\"widefat banner-description\" name=\"' + fieldNamePrefix + '[' + slideCount + '][description]\" placeholder=\"" . esc_js(__('Image description', 'a-ripple-song')) . "\">' +
                    '</div>' +
                    '<div style=\"text-align: right;\">' +
                        '<button type=\"button\" class=\"button button-link button-link-delete banner-remove-slide\" style=\"color: #b32d2e;\">" . esc_js(__('Delete', 'a-ripple-song')) . "</button>' +
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
                        title: '" . esc_js(__('Select Banner Image', 'a-ripple-song')) . "',
                        button: {
                            text: '" . esc_js(__('Use This Image', 'a-ripple-song')) . "'
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
    
    // 添加 Footer Links Widget 的 JavaScript
    $footer_links_widget_js = "
    (function($) {
        'use strict';
        
        var footerLinksHandlersInitialized = false;
        
        function initFooterLinksWidget() {
            if (footerLinksHandlersInitialized) {
                return;
            }
            
            footerLinksHandlersInitialized = true;
            
            // 处理添加链接按钮
            $(document).on('click', '.footer-add-link', function(e) {
                e.preventDefault();
                var button = $(this);
                var widgetId = button.data('widget-id');
                var container = $('#' + widgetId + '_container');
                var linkCount = container.find('.footer-link-item').length;
                var widgetForm = button.closest('.footer-links-widget-form');
                var fieldNamePrefix = widgetForm.data('field-prefix');
                
                if (!fieldNamePrefix) {
                    return;
                }
                
                var linkHtml = '<div class=\"footer-link-item\" style=\"margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;\">' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">" . esc_js(__('Text:', 'a-ripple-song')) . "</label>' +
                        '<input type=\"text\" class=\"widefat footer-link-text\" name=\"' + fieldNamePrefix + '[' + linkCount + '][text]\" placeholder=\"" . esc_js(__('Display text', 'a-ripple-song')) . "\">' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label style=\"display: block; margin-bottom: 4px; font-weight: 600;\">" . esc_js(__('URL (optional - leave empty for plain text):', 'a-ripple-song')) . "</label>' +
                        '<input type=\"url\" class=\"widefat footer-link-url\" name=\"' + fieldNamePrefix + '[' + linkCount + '][url]\" placeholder=\"https://example.com\">' +
                    '</div>' +
                    '<div style=\"margin-bottom: 8px;\">' +
                        '<label>' +
                            '<input type=\"checkbox\" class=\"footer-link-new-tab\" name=\"' + fieldNamePrefix + '[' + linkCount + '][new_tab]\" value=\"1\"> " . esc_js(__('Open in new tab', 'a-ripple-song')) . "' +
                        '</label>' +
                    '</div>' +
                    '<div style=\"text-align: right;\">' +
                        '<button type=\"button\" class=\"button button-link button-link-delete footer-remove-link\" style=\"color: #b32d2e;\">" . esc_js(__('Delete', 'a-ripple-song')) . "</button>' +
                    '</div>' +
                '</div>';
                
                container.append(linkHtml);
                
                var flagInput = widgetForm.find('.footer-links-flag');
                if (flagInput.length) {
                    flagInput.trigger('change');
                }
            });
            
            // 处理删除链接按钮
            $(document).on('click', '.footer-remove-link', function(e) {
                e.preventDefault();
                var linkItem = $(this).closest('.footer-link-item');
                var container = linkItem.closest('.footer-links-container');
                var widgetForm = linkItem.closest('.footer-links-widget-form');
                
                // 如果只剩一个，清空内容而不删除
                if (container.find('.footer-link-item').length <= 1) {
                    linkItem.find('input[type=\"text\"], input[type=\"url\"]').val('');
                    linkItem.find('input[type=\"checkbox\"]').prop('checked', false);
                } else {
                    linkItem.remove();
                }
                
                var flagInput = widgetForm.find('.footer-links-flag');
                if (flagInput.length) {
                    flagInput.trigger('change');
                }
            });
        }
        
        $(document).ready(function() {
            initFooterLinksWidget();
        });
        
        $(document).on('widget-added widget-updated', function(e, widget) {
            initFooterLinksWidget();
        });
        
    })(jQuery);
    ";
    
    wp_add_inline_script('jquery', $footer_links_widget_js);
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
