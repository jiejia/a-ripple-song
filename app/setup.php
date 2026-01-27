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
        'primary_navigation' => __('Primary Navigation', 'a-ripple-song'),
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
	     * Add default posts and comments RSS feed links to head.
	     *
	     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#automatic-feed-links
	     */
	    add_theme_support('automatic-feed-links');

	    /**
	     * Enable post thumbnail support.
	     *
	     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	     */
	    add_theme_support('post-thumbnails');

	    /**
	     * Block editor supports (recommended for classic themes).
	     */
	    add_theme_support('wp-block-styles');
	    add_theme_support('align-wide');
	    add_theme_support('editor-styles');
	    add_editor_style('editor-style.css');

	    /**
	     * Customizer supports (recommended when using logos/headers/backgrounds).
	     */
	    add_theme_support('custom-logo', [
	        'height'      => 100,
	        'width'       => 400,
	        'flex-height' => true,
	        'flex-width'  => true,
	    ]);
	    add_theme_support('custom-header', [
	        'width'       => 2000,
	        'height'      => 1200,
	        'flex-width'  => true,
	        'flex-height' => true,
	    ]);
	    add_theme_support('custom-background');

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
 * Disable WordPress emoji detection scripts/styles.
 *
 * WordPress core's emoji feature detection uses Web Storage (e.g. sessionStorage)
 * which can throw "Access to storage is not allowed from this context" in some
 * restricted browser contexts. Disabling emoji support avoids that error.
 *
 * @return void
 */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');

    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');

    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    add_filter('emoji_svg_url', '__return_false');
});

/**
 * Register a small set of block styles and patterns (recommended for classic themes).
 */
add_action('init', function () {
    if (function_exists('register_block_style')) {
        register_block_style('core/button', [
            'name' => 'ars-pill',
            'label' => __('Pill', 'a-ripple-song'),
            'inline_style' => '.is-style-ars-pill .wp-block-button__link{border-radius:9999px; padding-left:1.25em; padding-right:1.25em;}',
        ]);
    }

    if (function_exists('register_block_pattern')) {
        register_block_pattern('a-ripple-song/simple-cta', [
            'title' => __('Simple Call to Action', 'a-ripple-song'),
            'categories' => ['text', 'buttons'],
            'content' => '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"><!-- wp:heading {"level":3} --><h3>' . esc_html__('Listen to the latest episode', 'a-ripple-song') . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__('Subscribe and never miss an update.', 'a-ripple-song') . '</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-ars-pill"} --><div class="wp-block-button is-style-ars-pill"><a class="wp-block-button__link wp-element-button" href="' . esc_url(home_url('/')) . '">' . esc_html__('Explore episodes', 'a-ripple-song') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->',
        ]);
    }
}, 15);

/**
 * Align podcast episode URLs to /episodes/ instead of /podcasts/.
 */
add_filter('register_post_type_args', function ($args, $post_type) {
    if ($post_type !== 'ars_episode') {
        return $args;
    }

    $rewrite = is_array($args['rewrite'] ?? null) ? $args['rewrite'] : [];
    $rewrite['slug'] = 'episodes';
    $args['rewrite'] = $rewrite;
    $args['has_archive'] = 'episodes';

    return $args;
}, 10, 2);

add_action('init', function () {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $flag = 'aripplesong_episodes_rewrite_flushed';
    if (get_option($flag)) {
        return;
    }

    flush_rewrite_rules(false);
    update_option($flag, '1', 'no');
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

    // register_sidebar([
    //     'name' => __('Primary', 'a-ripple-song'),
    //     'id' => 'sidebar',
    // ] + $config);

    // register_sidebar([
    //     'name' => __('Footer', 'a-ripple-song'),
    //     'id' => 'sidebar-footer',
    // ] + $config);

    register_sidebar([
        'name' => '[' . Theme::PREFIX . '] ' . __('Footer Links', 'a-ripple-song'),
        'id' => Theme::SIDEBAR_FOOTER_LINKS,
        'description' => __('Footer links area for displaying link columns', 'a-ripple-song'),
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
    ]);

    register_sidebar([
        'name' => '[' . Theme::PREFIX . '] ' . __('Home Main', 'a-ripple-song'),
        'id' => Theme::SIDEBAR_HOME_MAIN,
        'description' => __('Main area of the homepage for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);
    register_sidebar([
        'name' => '[' . Theme::PREFIX . '] ' . __('Sidebar Primary', 'a-ripple-song'),
        'id' => Theme::SIDEBAR_PRIMARY,
        'description' => __('Primary sidebar area for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);
    register_sidebar([
        'name' => '[' . Theme::PREFIX . '] ' . __('Leftbar Primary', 'a-ripple-song'),
        'id' => Theme::SIDEBAR_LEFTBAR,
        'description' => __('Primary left sidebar area for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);
});

/**
 * Prevent WordPress from auto-mapping default widgets into the theme primary sidebar on first activation.
 *
 * We move any mapped widgets to "Inactive Widgets" instead of deleting them to avoid data loss.
 */
add_action('after_switch_theme', function () {
    aripplesong_maybe_empty_primary_sidebar_on_first_activation();
}, 5);

/**
 * Move any auto-assigned widgets from theme sidebars to inactive widgets on first activation.
 *
 * WordPress automatically maps widgets from the previous theme to similarly named
 * sidebars. This function moves those widgets to the inactive area to give users
 * a clean start with the demo import.
 *
 * @return void
 */
function aripplesong_maybe_empty_primary_sidebar_on_first_activation() {
    // TODO: Implement sidebar cleanup on first activation
}

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
 * Modify tag archive query to include both post and episode types.
 *
 * By default, WordPress tag archives only query 'post' type.
 */
add_action('pre_get_posts', function ($query) {
    // Only modify the main query on tag archive pages
    if (!is_admin() && $query->is_main_query() && $query->is_tag()) {
        $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
        $post_types = ['post'];
        if ($podcast_post_type) {
            $post_types[] = $podcast_post_type;
        }
        $query->set('post_type', $post_types);
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
	    if (is_singular() && comments_open() && get_option('thread_comments')) {
	        wp_enqueue_script('comment-reply');
	    }

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
                wp_enqueue_script('aripplesong-app', $js_url, ['wp-i18n'], null, true);
                // Set script translations for JavaScript i18n
                wp_set_script_translations('aripplesong-app', 'a-ripple-song', get_template_directory() . '/resources/lang');

                $light_theme = function_exists('\carbon_get_theme_option') ? \carbon_get_theme_option('crb_light_theme') : null;
                $dark_theme = function_exists('\carbon_get_theme_option') ? \carbon_get_theme_option('crb_dark_theme') : null;
                $light_themes = function_exists('\App\ThemeOptions\crb_get_daisyui_light_themes') ? array_keys(\App\ThemeOptions\crb_get_daisyui_light_themes()) : [];
                $dark_themes = function_exists('\App\ThemeOptions\crb_get_daisyui_dark_themes') ? array_keys(\App\ThemeOptions\crb_get_daisyui_dark_themes()) : [];
                $palette_slugs = array_unique(array_merge($light_themes, $dark_themes));
                $palette_map = function_exists('\App\ThemeOptions\crb_get_daisyui_theme_palette') ? \App\ThemeOptions\crb_get_daisyui_theme_palette($palette_slugs) : [];
                $current_post_id = is_singular() ? get_queried_object_id() : 0;
                $current_post_type = $current_post_id ? get_post_type($current_post_id) : '';
                $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : '';
                $podcast_enabled = !empty($podcast_post_type);
                $latest_playlist_data = function_exists('aripplesong_get_latest_playlist_data') ? \aripplesong_get_latest_playlist_data(10) : [
                    'episodes' => [],
                    'signature' => '',
                ];

                // Localize script with REST API URL and theme options
                wp_localize_script('aripplesong-app', 'aripplesongData', [
                    'restUrl' => esc_url_raw(rest_url()),
                    'restNonce' => wp_create_nonce('wp_rest'),
                    'siteUrl' => esc_url_raw(home_url('/')),
                    'podcastEnabled' => $podcast_enabled,
                    'podcastPostType' => $podcast_post_type ?: '',
                    'latestPlaylistSignature' => $latest_playlist_data['signature'] ?? '',
                    'latestPlaylistEpisodes' => $latest_playlist_data['episodes'] ?? [],
                    'ajax' => [
                        'url' => esc_url_raw(admin_url('admin-ajax.php')),
                        'nonce' => wp_create_nonce('aripplesong-ajax'),
                        'postId' => $current_post_id,
                        'postType' => $current_post_type,
                    ],
                    'theme' => [
                        'lightTheme' => $light_theme ?: 'retro',
                        'darkTheme' => $dark_theme ?: 'dim',
                        'lightThemes' => $light_themes,
                        'darkThemes' => $dark_themes,
                        'palette' => $palette_map,
                    ],
                ]);
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


add_action('after_setup_theme', function () {
    load_theme_textdomain('a-ripple-song', get_template_directory() . '/resources/lang');
}, 1); // Priority 1: Load translations before Carbon Fields (priority 10) to ensure admin labels are translated

/**
 * Expose podcast episode meta to the REST API.
 */
add_action('rest_api_init', function () {
    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
    if (!$podcast_post_type) {
        return;
    }

    register_rest_field($podcast_post_type, 'audio_file', [
        'get_callback' => function ($object) {
            $post_id = is_array($object) ? (int) ($object['id'] ?? 0) : 0;
            if ($post_id <= 0) {
                return '';
            }

            $value = function_exists('aripplesong_get_episode_meta')
                ? \aripplesong_get_episode_meta($post_id, 'audio_file', '')
                : get_post_meta($post_id, 'audio_file', true);
            return is_string($value) ? $value : '';
        },
        'schema' => [
            'description' => __('Audio file URL', 'a-ripple-song'),
            'type' => 'string',
        ],
    ]);

    register_rest_field($podcast_post_type, 'duration', [
        'get_callback' => function ($object) {
            $post_id = is_array($object) ? (int) ($object['id'] ?? 0) : 0;
            if ($post_id <= 0) {
                return 0;
            }

            $value = function_exists('aripplesong_get_episode_meta')
                ? \aripplesong_get_episode_meta($post_id, 'duration', 0)
                : get_post_meta($post_id, 'duration', true);
            return (int) $value;
        },
        'schema' => [
            'description' => __('Audio duration (seconds)', 'a-ripple-song'),
            'type' => 'integer',
        ],
    ]);

    register_rest_field($podcast_post_type, 'episode_transcript', [
        'get_callback' => function ($object) {
            $post_id = is_array($object) ? (int) ($object['id'] ?? 0) : 0;
            if ($post_id <= 0) {
                return '';
            }

            $value = function_exists('aripplesong_get_episode_meta')
                ? \aripplesong_get_episode_meta($post_id, 'episode_transcript', '')
                : get_post_meta($post_id, 'episode_transcript', true);
            return is_string($value) ? $value : '';
        },
        'schema' => [
            'description' => __('Episode transcript URL', 'a-ripple-song'),
            'type' => 'string',
        ],
    ]);
});

/**
 * Invalidate cached podcast participation results when podcasts change.
 */
add_action('save_post', function ($post_id, $post, $update) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
    if (!$podcast_post_type || get_post_type($post_id) !== $podcast_post_type) {
        return;
    }

    if (function_exists('aripplesong_bump_participation_cache_version')) {
        \aripplesong_bump_participation_cache_version();
    }
}, 10, 3);

add_action('deleted_post', function ($post_id) {
    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? \aripplesong_get_podcast_post_type() : null;
    if (!$podcast_post_type || get_post_type($post_id) !== $podcast_post_type) {
        return;
    }

    if (function_exists('aripplesong_bump_participation_cache_version')) {
        \aripplesong_bump_participation_cache_version();
    }
});
