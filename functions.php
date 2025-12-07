<?php

use Roots\Acorn\Application;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Initialize CMB2
|--------------------------------------------------------------------------
|
| Load CMB2 for custom metaboxes and fields.
|
*/

if (file_exists(__DIR__ . '/vendor/cmb2/cmb2/init.php')) {
    require_once __DIR__ . '/vendor/cmb2/cmb2/init.php';
}

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
    ->withProviders([
        App\Providers\ThemeServiceProvider::class,
    ])
    ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters', 'podcast-types', 'widgets', 'carbon-fields'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });

/**
 * Get localized date using Carbon library
 * 
 * @param int|null $post_id Optional post ID. Defaults to current post.
 * @param string $format Optional format. Use 'relative' for human diff (default), 'long' for full date, 'short' for abbreviated, or custom PHP date format
 * @return string Formatted date string appropriate for current locale
 */
function get_localized_date($post_id = null, $format = 'relative') {
    $timestamp = get_post_time('U', false, $post_id);
    
    // Get WordPress locale (e.g., 'zh_CN', 'en_US', 'ja')
    $wp_locale = get_locale();
    
    // Convert WordPress locale to Carbon locale format
    // WordPress uses underscore (zh_CN), Carbon uses hyphen (zh-CN)
    $carbon_locale = str_replace('_', '-', $wp_locale);
    
    // Create Carbon instance and set locale
    $date = \Carbon\Carbon::createFromTimestamp($timestamp);
    
    try {
        $date->locale($carbon_locale);
    } catch (\Exception $e) {
        // Fallback to base language if specific locale not found
        // e.g., zh-CN -> zh, en-US -> en
        $base_locale = explode('-', $carbon_locale)[0];
        try {
            $date->locale($base_locale);
        } catch (\Exception $e2) {
            // Final fallback to English
            $date->locale('en');
        }
    }
    
    // Define locale-specific formats
    $base_locale = explode('-', $carbon_locale)[0];
    
    if ($format === 'relative') {
        // Smart relative time: show "30 minutes ago", "1 day ago" for recent posts
        // But show absolute date for posts older than 7 days
        $now = \Carbon\Carbon::now();
        $diff_in_days = $now->diffInDays($date);
        
        if ($diff_in_days < 7) {
            // Recent: use relative time (e.g., "30 minutes ago", "2 days ago")
            return $date->diffForHumans();
        } else {
            // Older: use absolute date format
            if (in_array($base_locale, ['zh', 'ja'])) {
                return $date->translatedFormat('Y年n月j日');
            } elseif ($base_locale === 'ko') {
                return $date->translatedFormat('Y년 n월 j일');
            } else {
                return $date->isoFormat('ll');
            }
        }
    } elseif ($format === 'short' || $format === 'long') {
        // Use locale-specific formats for Asian languages
        if (in_array($base_locale, ['zh', 'ja'])) {
            // Chinese/Japanese: 2025年11月4日
            return $date->translatedFormat('Y年n月j日');
        } elseif ($base_locale === 'ko') {
            // Korean: 2025년 11월 4일
            return $date->translatedFormat('Y년 n월 j일');
        } else {
            // Western languages: use isoFormat for better localization
            // 'll' in isoFormat = Nov 4, 2025 (localized)
            // 'LL' in isoFormat = November 4, 2025 (localized)
            return $date->isoFormat($format === 'long' ? 'LL' : 'll');
        }
    } else {
        // Custom format provided
        return $date->translatedFormat($format);
    }
}

/**
 * Get localized comment date using Carbon library
 * 
 * @param object $comment Comment object
 * @param bool $include_time Whether to include time in the output
 * @return string Formatted date string appropriate for current locale
 */
function get_localized_comment_date($comment, $include_time = true) {
    $timestamp = strtotime($comment->comment_date);
    
    // Get WordPress locale
    $wp_locale = get_locale();
    $carbon_locale = str_replace('_', '-', $wp_locale);
    
    // Create Carbon instance and set locale
    $date = \Carbon\Carbon::createFromTimestamp($timestamp);
    
    try {
        $date->locale($carbon_locale);
    } catch (\Exception $e) {
        $base_locale = explode('-', $carbon_locale)[0];
        try {
            $date->locale($base_locale);
        } catch (\Exception $e2) {
            $date->locale('en');
        }
    }
    
    // Get base locale for format selection
    $base_locale = explode('-', $carbon_locale)[0];
    
    // Format based on locale
    if (in_array($base_locale, ['zh', 'ja'])) {
        // Chinese/Japanese
        $format = $include_time ? 'Y年n月j日 H:i' : 'Y年n月j日';
        return $date->translatedFormat($format);
    } elseif ($base_locale === 'ko') {
        // Korean
        $format = $include_time ? 'Y년 n월 j일 H:i' : 'Y년 n월 j일';
        return $date->translatedFormat($format);
    } else {
        // Western languages
        if ($include_time) {
            return $date->isoFormat('ll HH:mm');
        } else {
            return $date->isoFormat('ll');
        }
    }
}

/**
 * Get all authors/participants for a post.
 * 
 * This includes:
 * - The post author
 * - For podcasts: users listed in members and guests fields
 *
 * @param int $post_id Post ID
 * @return array Array of user IDs (unique)
 */
function get_post_all_authors($post_id) {
    $authors = [];
    
    // Get the post author
    $author_id = get_post_field('post_author', $post_id);
    if ($author_id) {
        $authors[] = (int)$author_id;
    }
    
    // If it's a podcast, also get members and guests
    $post_type = get_post_type($post_id);
    if ($post_type === 'podcast') {
        // Get members field
        $members = get_post_meta($post_id, 'members', true);
        if (!empty($members) && is_array($members)) {
            foreach ($members as $member_id) {
                $member_id = (int)$member_id;
                if ($member_id && !in_array($member_id, $authors, true)) {
                    $authors[] = $member_id;
                }
            }
        }
        
        // Get guests field
        $guests = get_post_meta($post_id, 'guests', true);
        if (!empty($guests) && is_array($guests)) {
            foreach ($guests as $guest_id) {
                $guest_id = (int)$guest_id;
                if ($guest_id && !in_array($guest_id, $authors, true)) {
                    $authors[] = $guest_id;
                }
            }
        }
    }
    
    return $authors;
}

/**
 * Get all post IDs for a user including podcasts they participated in.
 * 
 * This includes:
 * - All posts published by the user (including podcasts)
 * - Podcasts where the user is listed in members or guests fields (excluding podcasts authored by the user)
 *
 * @param int $user_id User ID
 * @return array Array of post IDs
 */
function get_user_all_post_ids($user_id) {
    $post_ids = [];
    
    // Get posts authored by the user (both 'post' and 'podcast' types)
    $authored_posts = get_posts([
        'author' => $user_id,
        'post_type' => ['post', 'podcast'],
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    ]);
    
    $post_ids = array_merge($post_ids, $authored_posts);
    
    // Get all podcasts where user is in members or guests (but not author)
    $all_podcasts = get_posts([
        'post_type' => 'podcast',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    ]);
    
    foreach ($all_podcasts as $podcast_id) {
        // Skip if already in the list (user is author)
        if (in_array($podcast_id, $post_ids)) {
            continue;
        }
        
        // Check members field
        $members = get_post_meta($podcast_id, 'members', true);
        if (!empty($members) && is_array($members)) {
            if (in_array($user_id, $members, false) || in_array((string)$user_id, $members, false)) {
                $post_ids[] = $podcast_id;
                continue;
            }
        }
        
        // Check guests field
        $guests = get_post_meta($podcast_id, 'guests', true);
        if (!empty($guests) && is_array($guests)) {
            if (in_array($user_id, $guests, false) || in_array((string)$user_id, $guests, false)) {
                $post_ids[] = $podcast_id;
            }
        }
    }
    
    return $post_ids;
}

/**
 * Calculate total post count for a user.
 * 
 * This includes:
 * - All posts published by the user (including podcasts)
 * - Podcasts where the user is listed in members or guests fields (excluding podcasts authored by the user)
 *
 * @param int $user_id User ID
 * @return int Total post count
 */
function calculate_user_post_count($user_id) {
    // Base count: all posts published by the user
    // count_user_posts() only counts 'post' type by default, so we need to count 'podcast' separately
    $regular_posts_count = count_user_posts($user_id, 'post');
    $podcast_posts_count = count_user_posts($user_id, 'podcast');
    $base_count = $regular_posts_count + $podcast_posts_count;
    
    // Query ALL published podcast posts
    $all_podcasts = get_posts([
        'post_type' => 'podcast',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    ]);
    
    $additional_podcast_count = 0;
    foreach ($all_podcasts as $podcast_id) {
        // Get the podcast author
        $podcast_author = get_post_field('post_author', $podcast_id);
        
        // Skip if this user is the author (already counted in base_count)
        if ($podcast_author == $user_id) {
            continue;
        }
        
        $found = false;
        
        // Check members field
        // CMB2 multicheck stores as a simple array of user IDs: [1, 2, 3]
        $members = get_post_meta($podcast_id, 'members', true);
        if (!empty($members) && is_array($members)) {
            // Check if user_id is in the array (both as integer and string)
            if (in_array($user_id, $members, false) || in_array((string)$user_id, $members, false)) {
                $additional_podcast_count++;
                $found = true;
            }
        }
        
        // If not found in members, check guests field
        if (!$found) {
            $guests = get_post_meta($podcast_id, 'guests', true);
            if (!empty($guests) && is_array($guests)) {
                // Check if user_id is in the array (both as integer and string)
                if (in_array($user_id, $guests, false) || in_array((string)$user_id, $guests, false)) {
                    $additional_podcast_count++;
                }
            }
        }
    }
    
    return $base_count + $additional_podcast_count;
}

/**
 * Modify author archive query to include posts where user is a member or guest
 * 
 * @param WP_Query $query
 * @return void
 */
function modify_author_archive_query($query) {
    // Only modify the main query on author archive pages
    if (!is_admin() && $query->is_main_query() && $query->is_author()) {
        // Get the author ID - try multiple methods to ensure we get it
        $author_id = $query->get('author');
        if (!$author_id) {
            $author_name = $query->get('author_name');
            if ($author_name) {
                $user = get_user_by('slug', $author_name);
                if ($user) {
                    $author_id = $user->ID;
                }
            }
        }
        
        // IMPORTANT: Store the author object in the query before we clear the author vars
        // This allows templates to access the author via get_queried_object()
        if ($author_id) {
            $author_object = get_userdata($author_id);
            if ($author_object) {
                $query->queried_object = $author_object;
                $query->queried_object_id = $author_id;
            }
        }
        
        // Debug log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Author Archive Query - Author ID: " . $author_id);
        }
        
        // Get all post IDs for this author (including podcasts they participated in)
        $post_ids = get_user_all_post_ids($author_id);
        
        // Modify query to use our post IDs
        if (!empty($post_ids)) {
            // Reset query vars to prevent conflicts
            $query->set('post__in', $post_ids);
            $query->set('author', 0); // Set to 0 instead of empty string
            $query->set('author_name', ''); // Clear author_name too
            $query->set('post_type', ['post', 'podcast']); // Include both post types
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
            
            // Important: don't let WordPress limit by author
            unset($query->query_vars['author']);
        } else {
            // No posts found
            $query->set('post__in', [0]); // Force no results
        }
    }
}
add_action('pre_get_posts', 'modify_author_archive_query');

/**
 * Get primary navigation menu items with parent-child relationship structure
 * 
 * @param string $location Menu location name, defaults to 'primary_navigation'
 * @return array Returns an array containing top-level menu items and their child menu items mapping
 */
function get_primary_navigation_menu_items($location = 'primary_navigation') {
    $menu_locations = get_nav_menu_locations();
    $menu_items = [];
    
    if (isset($menu_locations[$location])) {
        $menu_id = $menu_locations[$location];
        $menu_items = wp_get_nav_menu_items($menu_id);
    }
    
    if (!$menu_items) {
        return [];
    }
    
    // Build hierarchical menu structure (up to 3 levels)
    $menu_items_by_id = [];
    foreach ($menu_items as $item) {
        $menu_items_by_id[$item->ID] = [
            'item' => $item,
            'children' => []
        ];
    }
    
    // Build parent-child relationships recursively
    foreach ($menu_items as $item) {
        if ($item->menu_item_parent != 0 && isset($menu_items_by_id[$item->menu_item_parent])) {
            $menu_items_by_id[$item->menu_item_parent]['children'][] = &$menu_items_by_id[$item->ID];
        }
    }
    
    // Return only top-level menu items (with their nested children)
    $top_level_items = [];
    foreach ($menu_items as $item) {
        if ($item->menu_item_parent == 0) {
            $top_level_items[] = $menu_items_by_id[$item->ID];
        }
    }
    
    return $top_level_items;
}

/**
 * Check if a menu item is active (current page or has active child/grandchild)
 * 
 * @param object $item The menu item object
 * @param array $children Array of child menu items (with nested structure)
 * @param string $current_url The current page URL
 * @return bool True if the menu item is active
 */
function is_menu_item_active($item, $children = [], $current_url = '') {
    if (empty($current_url)) {
        $current_url = home_url($_SERVER['REQUEST_URI']);
    }
    
    // Check if current item is active
    $is_current = ($item->url === $current_url);
    
    // Recursively check if any descendant item is active
    $has_active_descendant = false;
    if (!empty($children)) {
        foreach ($children as $child_data) {
            $child = is_array($child_data) ? $child_data['item'] : $child_data;
            $grandchildren = is_array($child_data) && isset($child_data['children']) ? $child_data['children'] : [];
            
            if ($child->url === $current_url) {
                $has_active_descendant = true;
                break;
            }
            
            // Check grandchildren recursively
            if (!empty($grandchildren) && is_menu_item_active($child, $grandchildren, $current_url)) {
                $has_active_descendant = true;
                break;
            }
        }
    }
    
    return $is_current || $has_active_descendant;
}

/**
 * Get episode data for a podcast post
 * 
 * @param int|null $post_id Post ID (defaults to current post)
 * @return array Episode data array with id, audioUrl, title, description, publishDate (timestamp), featuredImage, link
 */
function get_episode_data($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $audio_file = get_post_meta($post_id, 'audio_file', true);
    $featured_image = get_the_post_thumbnail_url($post_id, 'medium');
    
    return [
        'id' => $post_id,
        'audioUrl' => $audio_file,
        'title' => get_the_title($post_id),
        'description' => wp_strip_all_tags(get_the_excerpt()),
        'publishDate' => get_post_time('U', false, $post_id), // Return Unix timestamp
        'featuredImage' => $featured_image,
        'link' => get_permalink($post_id)
    ];
}

/**
 * Custom comment callback with DaisyUI styling
 * 
 * @param object $comment Comment object
 * @param array $args Comment arguments
 * @param int $depth Comment depth level
 */
function sage_custom_comment($comment, $args, $depth) {
    // All comments use bg-base-100 regardless of depth
    $bg_class = 'bg-base-200/50';
    
    // Get comment type class
    $comment_type = get_comment_type($comment->comment_ID);
    ?>
    <li id="comment-<?php comment_ID(); ?>" <?php comment_class('comment-item'); ?>>
        <article class="<?php echo esc_attr($bg_class); ?> rounded-lg p-4 hover:shadow-sm transition-shadow">
            <div class="flex gap-2">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <?php if ($args['avatar_size'] != 0): ?>
                        <div class="avatar">
                            <div class="w-6 h-6 rounded-full ring ring-primary ring-offset-base-100 ring-offset-1">
                                <?php echo get_avatar($comment, $args['avatar_size']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Comment Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="font-bold text-sm">
                            <?php echo get_comment_author_link($comment); ?>
                        </span>
                        
                        <?php if ($comment->user_id === get_post()->post_author): ?>
                            <span class="badge badge-primary badge-sm"><?php _e('Author', 'sage'); ?></span>
                        <?php endif; ?>
                        
                        <span class="text-xs text-base-content/60 flex items-center gap-1">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <?php echo get_localized_comment_date($comment); ?>
                        </span>
                        
                        <?php if ($comment->comment_approved == '0'): ?>
                            <span class="badge badge-warning badge-sm"><?php _e('Pending Approval', 'sage'); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-sm text-base-content/80 mb-3 leading-relaxed">
                        <?php comment_text(); ?>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <?php 
                        comment_reply_link(array_merge($args, [
                            'add_below' => 'comment',
                            'depth' => $depth,
                            'max_depth' => $args['max_depth'],
                            'before' => '<button class="btn btn-ghost btn-sm gap-1 text-sm">',
                            'after' => '</button>',
                            'reply_text' => '<i data-lucide="reply" class="w-4 h-4"></i> ' . __('Reply', 'sage')
                        ]));
                        ?>
                        
                        <?php edit_comment_link(
                            '<i data-lucide="pencil" class="w-4 h-4"></i> ' . __('Edit', 'sage'),
                            '<button class="btn btn-ghost btn-sm gap-1 text-sm">',
                            '</button>'
                        ); ?>
                    </div>
                </div>
            </div>
        </article>
    <?php
}

/**
 * Customize comment form with DaisyUI styling
 */
add_filter('comment_form_defaults', function($defaults) {
    $defaults['class_form'] = 'space-y-4';
    $defaults['class_submit'] = 'btn btn-primary btn-sm gap-2 text-sm';
    $defaults['submit_button'] = '<button type="submit" id="%2$s" class="%3$s">%4$s <i data-lucide="send" class="w-4 h-4"></i></button>';
    $defaults['title_reply_before'] = '<h3 id="reply-title" class="text-md font-bold mb-4 hidden">';
    $defaults['title_reply_after'] = '</h3>';
    $defaults['cancel_reply_before'] = '<div class="text-sm">';
    $defaults['cancel_reply_after'] = '</div>';
    $defaults['cancel_reply_link'] = '<button type="button" class="btn btn-ghost btn-sm gap-1 text-sm"><i data-lucide="x" class="w-4 h-4"></i> %s</button>';
    $defaults['comment_notes_before'] = '<p class="comment-notes text-sm text-base-content/60">' . __('Your email address will not be published.', 'sage') . '</p>';
    $defaults['comment_notes_after'] = '';
    $defaults['logged_in_as'] = '<p class="logged-in-as text-sm text-base-content/60">' . 
        sprintf(__('Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'sage'), 
            get_edit_user_link(), 
            wp_get_current_user()->display_name, 
            wp_logout_url(apply_filters('the_permalink', get_permalink()))) . 
        '</p>';
    
    return $defaults;
});

/**
 * Customize comment form fields with DaisyUI styling
 */
add_filter('comment_form_default_fields', function($fields) {
    // Keep interactive controls at >=16px to stop Chrome/Android auto-zoom.
    $fields['author'] = '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Name', 'sage') . ' <span class="text-error">*</span></span></label><input type="text" id="author" name="author" class="input input-bordered w-full text-sm" required /></div>';
    
    $fields['email'] = '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Email', 'sage') . ' <span class="text-error">*</span></span></label><input type="email" id="email" name="email" class="input input-bordered w-full text-sm" required /></div>';
    
    $fields['url'] = '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Website', 'sage') . '</span></label><input type="url" id="url" name="url" class="input input-bordered w-full text-sm" /></div>';
    
    $fields['cookies'] = '<div class="form-control"><label class="comment-form-cookies-consent"><input type="checkbox" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" value="yes" class="checkbox" /><span class="label-text text-sm leading-relaxed">' . __('Save my name, email, and website in this browser for the next time I comment.', 'sage') . '</span></label></div>';
    
    return $fields;
});

/**
 * Customize comment textarea field with DaisyUI styling
 */
add_filter('comment_form_field_comment', function($field) {
    return '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Comment', 'sage') . ' <span class="text-error">*</span></span></label><textarea id="comment" name="comment" rows="6" class="textarea textarea-bordered w-full text-sm" required></textarea></div>';
});
