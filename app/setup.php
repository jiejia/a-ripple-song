<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
// add_filter('block_editor_settings_all', function ($settings) {
//     $style = Vite::asset('resources/css/editor.css');

//     $settings['styles'][] = [
//         'css' => "@import url('{$style}')",
//     ];

//     return $settings;
// });

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
// add_filter('admin_head', function () {
//     if (! get_current_screen()?->is_block_editor()) {
//         return;
//     }

//     // editor.js no longer depends on WordPress packages, just output it directly
//     echo Vite::withEntryPoints([
//         'resources/js/editor.js',
//     ])->toHtml();
// });

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
// add_filter('theme_file_path', function ($path, $file) {
//     return $file === 'theme.json'
//         ? public_path('build/assets/theme.json')
//         : $path;
// }, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);

    register_sidebar([
        'name' => __('Home Main', 'sage'),
        'id' => 'home-main',
        'description' => __('首页主要区域，用于显示首页的各种内容模块', 'sage'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);
    register_sidebar([
        'name' => __('Sidebar Primary', 'sage'),
        'id' => 'sidebar-primary',
        'description' => __('侧边栏主要区域，用于显示侧边栏的各种内容模块', 'sage'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);
    register_sidebar([
        'name' => __('Leftbar Primary', 'sage'),
        'id' => 'leftbar-primary',
        'description' => __('左侧栏主要区域，用于显示左侧栏的各种内容模块', 'sage'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);
});

/**
 * Customize posts pagination to use DaisyUI join structure with Lucide icons.
 *
 * @return void
 */
add_filter('the_posts_pagination_args', function ($args) {
    // Set Lucide icons for prev/next buttons
    $args['prev_text'] = '<i data-lucide="chevron-left" class="w-4 h-4"></i>';
    $args['next_text'] = '<i data-lucide="chevron-right" class="w-4 h-4"></i>';
    return $args;
});

/**
 * Customize navigation markup template to use DaisyUI join structure.
 *
 * @param string $template The navigation markup template.
 * @param string $class The navigation class.
 * @return string
 */
add_filter('navigation_markup_template', function ($template, $class) {
    // Only apply to post pagination
    if ('pagination' !== $class) {
        return $template;
    }

    // Return custom template with DaisyUI join structure
    return '<nav class="navigation %1$s" role="navigation" aria-label="%4$s">
        <div class="join mt-4 text-center justify-center flex gap-1">%3$s</div>
    </nav>';
}, 10, 2);

/**
 * Customize paginate_links output to match DaisyUI join structure.
 *
 * @param string $output The pagination HTML output.
 * @param array $args The paginate_links arguments.
 * @return string
 */
function custom_paginate_links_output($output, $args) {
    global $wp_query;

    // Only apply to posts pagination (check if this is called from get_the_posts_pagination)
    if (! $wp_query || $wp_query->max_num_pages <= 1) {
        return $output;
    }

    // Prevent infinite loop by checking if we're already processing
    static $processing = false;
    if ($processing) {
        return $output;
    }
    $processing = true;

    // Get current page
    $current_page = max(1, get_query_var('paged'));
    $total_pages = $wp_query->max_num_pages;

    // Get pagination links as array to rebuild
    $links_args = array_merge($args, [
        'type' => 'array',
        'prev_text' => '<i data-lucide="chevron-left" class="w-4 h-4"></i>',
        'next_text' => '<i data-lucide="chevron-right" class="w-4 h-4"></i>',
    ]);
    
    // Temporarily remove our filter to prevent recursion
    remove_filter('paginate_links_output', __NAMESPACE__ . '\\custom_paginate_links_output');
    $links = paginate_links($links_args);
    add_filter('paginate_links_output', __NAMESPACE__ . '\\custom_paginate_links_output', 10, 2);
    
    $processing = false;

    if (empty($links)) {
        return '';
    }

    // Build DaisyUI join structure
    $html = '';

    // Previous button
    if ($current_page > 1) {
        $prev_url = get_pagenum_link($current_page - 1);
        $html .= sprintf(
            '<a href="%s" class="join-item btn btn-sm btn-square bg-base-100">%s</a>',
            esc_url($prev_url),
            '<i data-lucide="chevron-left" class="w-4 h-4"></i>'
        );
    } else {
        $html .= '<span class="join-item btn btn-sm btn-square bg-base-100 btn-disabled"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>';
    }

    // Page numbers
    foreach ($links as $link) {
        // Extract page number from link
        if (preg_match('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>(\d+)<\/a>/', $link, $matches)) {
            $url = $matches[1];
            $page_num = $matches[2];
            $html .= sprintf(
                '<a href="%s" class="join-item btn btn-sm btn-square bg-base-100" aria-label="%s">%s</a>',
                esc_url($url),
                esc_attr($page_num),
                esc_html($page_num)
            );
        } elseif (preg_match('/<span[^>]*class=["\'][^"\']*page-numbers[^"\']*current[^"\']*["\'][^>]*>(\d+)<\/span>/', $link, $matches)) {
            // Current page - use radio input
            $page_num = $matches[1];
            $html .= sprintf(
                '<input class="join-item btn btn-sm btn-square bg-base-100" type="radio" name="pagination" aria-label="%s" checked="checked" />',
                esc_attr($page_num)
            );
        } elseif (preg_match('/<span[^>]*class=["\'][^"\']*page-numbers[^"\']*dots[^"\']*["\'][^>]*>/', $link)) {
            // Dots - skip or show as disabled
            $html .= '<span class="join-item btn btn-sm btn-square bg-base-100 btn-disabled">...</span>';
        } elseif (preg_match('/<a[^>]*href=["\']([^"\']+)["\'][^>]*class=["\'][^"\']*prev[^"\']*["\'][^>]*>/', $link)) {
            // Previous link already handled above
            continue;
        } elseif (preg_match('/<a[^>]*href=["\']([^"\']+)["\'][^>]*class=["\'][^"\']*next[^"\']*["\'][^>]*>/', $link)) {
            // Next link will be handled below
            continue;
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        $next_url = get_pagenum_link($current_page + 1);
        $html .= sprintf(
            '<a href="%s" class="join-item btn btn-sm btn-square bg-base-100">%s</a>',
            esc_url($next_url),
            '<i data-lucide="chevron-right" class="w-4 h-4"></i>'
        );
    } else {
        $html .= '<span class="join-item btn btn-sm btn-square bg-base-100 btn-disabled"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>';
    }

    return $html;
}

add_filter('paginate_links_output', __NAMESPACE__ . '\\custom_paginate_links_output', 10, 2);

/**
 * Modify tag archive query to include both post and podcast types.
 *
 * By default, WordPress tag archives only query 'post' type.
 * This filter ensures that both 'post' and 'podcast' types are included.
 *
 * @param WP_Query $query The WordPress query object.
 * @return void
 */
add_action('pre_get_posts', function ($query) {
    // Only modify the main query on tag archive pages
    if (!is_admin() && $query->is_main_query() && $query->is_tag()) {
        $query->set('post_type', ['post', 'podcast']);
    }
});

/**
 * Enqueue theme assets using Vite
 * 
 * This ensures that theme assets are properly loaded in all contexts,
 * including customizer preview and widget previews.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function () {
    if (!class_exists('\Illuminate\Support\Facades\Vite')) {
        return;
    }
    
    try {
        // Check if this is a widget preview in admin
        // Widget previews render through wp_enqueue_scripts but should use editor assets
        $is_widget_preview = is_admin() || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'widgets.php') !== false);
        
        if ($is_widget_preview) {
            // Load editor assets for widget preview
            $css_url = \Illuminate\Support\Facades\Vite::asset('resources/css/editor.css');
            $js_url = \Illuminate\Support\Facades\Vite::asset('resources/js/editor.js');
            
            if ($css_url) {
                wp_enqueue_style('aripplesong-editor', $css_url, [], null);
            }
            
            if ($js_url) {
                wp_enqueue_script('aripplesong-editor', $js_url, [], null, true);
            }
        } else {
            // Load app assets for normal frontend pages
            $css_url = \Illuminate\Support\Facades\Vite::asset('resources/css/app.css');
            $js_url = \Illuminate\Support\Facades\Vite::asset('resources/js/app.js');
            
            if ($css_url) {
                wp_enqueue_style('aripplesong-app', $css_url, [], null);
            }
            
            if ($js_url) {
                wp_enqueue_script('aripplesong-app', $js_url, [], null, true);
            }
        }
    } catch (\Exception $e) {
        error_log('Failed to enqueue Vite assets: ' . $e->getMessage());
    }
}, 100);

/**
 * Add type="module" attribute to editor script tag
 *
 * @param string $tag The script tag HTML.
 * @param string $handle The script handle.
 * @param string $src The script source URL.
 * @return string
 */
add_filter('script_loader_tag', function ($tag, $handle, $src) {
    if ($handle === 'aripplesong-editor' || $handle === 'aripplesong-app') {
        $tag = str_replace('<script ', '<script type="module" ', $tag);
    }
    return $tag;
}, 10, 3);
