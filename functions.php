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
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'a-ripple-song'));
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

/**
 * Podcast plugin detection and post type helpers.
 *
 * The theme's built-in podcast CPT/feed/options have been removed.
 * Podcast UI (widgets/player) is available only when the companion plugin
 * `a-ripple-song-podcast` is active, which registers the `ars_episode` post type.
 */
if (!function_exists('aripplesong_is_podcast_plugin_active')) {
    function aripplesong_is_podcast_plugin_active(): bool
    {
        $prefix = 'a-ripple-song-podcast/';

        $active = (array) get_option('active_plugins', []);
        foreach ($active as $plugin) {
            if (is_string($plugin) && strpos($plugin, $prefix) === 0) {
                return true;
            }
        }

        if (function_exists('is_multisite') && is_multisite()) {
            $sitewide = (array) get_site_option('active_sitewide_plugins', []);
            foreach (array_keys($sitewide) as $plugin) {
                if (is_string($plugin) && strpos($plugin, $prefix) === 0) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('aripplesong_get_podcast_post_type')) {
    function aripplesong_get_podcast_post_type(): ?string
    {
        $post_type = 'ars_episode';

        if (function_exists('post_type_exists') && post_type_exists($post_type)) {
            return $post_type;
        }

        if (function_exists('aripplesong_is_podcast_plugin_active') && aripplesong_is_podcast_plugin_active()) {
            return $post_type;
        }

        return null;
    }
}

if (!function_exists('aripplesong_get_podcast_category_taxonomy')) {
    function aripplesong_get_podcast_category_taxonomy(): ?string
    {
        $taxonomy = 'ars_episode_category';

        if (function_exists('taxonomy_exists') && taxonomy_exists($taxonomy)) {
            return $taxonomy;
        }

        if (function_exists('aripplesong_is_podcast_plugin_active') && aripplesong_is_podcast_plugin_active()) {
            return $taxonomy;
        }

        return null;
    }
}

if (!function_exists('aripplesong_podcast_features_enabled')) {
    function aripplesong_podcast_features_enabled(): bool
    {
        return (bool) aripplesong_get_podcast_post_type();
    }
}

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

collect(['setup', 'filters', 'widgets', 'ThemeOptions/ThemeSettings', 'Metrics/Post'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'a-ripple-song'), $file)
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
    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
    if ($podcast_post_type && $post_type === $podcast_post_type) {
        $members = get_post_meta($post_id, 'members', true);
        $guests = get_post_meta($post_id, 'guests', true);

        $authors = array_merge(
            $authors,
            aripplesong_extract_multicheck_user_ids($members),
            aripplesong_extract_multicheck_user_ids($guests)
        );
    }

    $authors = array_values(array_unique(array_filter(array_map('absint', $authors))));

    return $authors;
}

/**
 * Extract user IDs from a CMB2 multicheck value.
 *
 * CMB2 multicheck typically stores selected values as an associative array of
 * "id" => "on". Some installs may store a simple numeric array instead.
 *
 * @param mixed $value
 * @return int[]
 */
function aripplesong_extract_multicheck_user_ids($value): array
{
    if (!is_array($value) || empty($value)) {
        return [];
    }

    $ids = [];

    foreach ($value as $key => $item) {
        if ($item === 'on' && is_numeric($key)) {
            $ids[] = (int) $key;
            continue;
        }

        if (is_numeric($item)) {
            $ids[] = (int) $item;
            continue;
        }

        if (is_string($item)) {
            // Carbon Fields association can store strings like "user:user:123".
            if (preg_match('~(?:^|:)user:.*:(\\d+)$~', $item, $m)) {
                $ids[] = (int) $m[1];
                continue;
            }
            if (preg_match('~(\\d+)$~', $item, $m)) {
                $ids[] = (int) $m[1];
                continue;
            }
        }

        if (is_array($item)) {
            // Carbon Fields association stores items like ['type' => 'user', 'id' => 123, ...].
            if (isset($item['id']) && is_numeric($item['id'])) {
                $ids[] = (int) $item['id'];
                continue;
            }
        }
    }

    return array_values(array_unique(array_filter(array_map('absint', $ids))));
}

/**
 * Get published podcast IDs where the user is listed as a member or guest.
 *
 * @param int $user_id
 * @return int[]
 */
function aripplesong_get_participated_podcast_ids(int $user_id): array
{
    static $cache = [];

    $user_id = absint($user_id);
    if (!$user_id) {
        return [];
    }

    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
    if (!$podcast_post_type) {
        return [];
    }

    if (isset($cache[$user_id])) {
        return $cache[$user_id];
    }

    $cache_version = (int) get_option('aripplesong_participation_cache_version', 1);
    $transient_key = 'aripplesong_participated_podcasts_v' . $cache_version . '_' . $user_id;
    $cached = get_transient($transient_key);
    if (is_array($cached)) {
        $cache[$user_id] = array_values(array_unique(array_filter(array_map('absint', $cached))));
        return $cache[$user_id];
    }

    $needle_string = '"' . $user_id . '"';
    $needle_int = 'i:' . $user_id . ';';

    $ids = get_posts([
        'post_type' => $podcast_post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'author__not_in' => [$user_id],
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'members',
                'value' => $needle_string,
                'compare' => 'LIKE',
            ],
            [
                'key' => 'guests',
                'value' => $needle_string,
                'compare' => 'LIKE',
            ],
            [
                'key' => 'members',
                'value' => $needle_int,
                'compare' => 'LIKE',
            ],
            [
                'key' => 'guests',
                'value' => $needle_int,
                'compare' => 'LIKE',
            ],
        ],
    ]);

    $cache[$user_id] = array_values(array_unique(array_filter(array_map('absint', $ids))));
    set_transient($transient_key, $cache[$user_id], HOUR_IN_SECONDS);

    return $cache[$user_id];
}

/**
 * Bump the participation cache version (invalidates user participation transients).
 */
function aripplesong_bump_participation_cache_version(): void
{
    $current = (int) get_option('aripplesong_participation_cache_version', 1);
    update_option('aripplesong_participation_cache_version', $current + 1, 'no');
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
    $user_id = absint($user_id);
    if (!$user_id) {
        return [];
    }

    $post_ids = [];
    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
    $post_types = ['post'];
    if ($podcast_post_type) {
        $post_types[] = $podcast_post_type;
    }

    // Get posts authored by the user (both 'post' and 'podcast' types)
    $authored_posts = get_posts([
        'author' => $user_id,
        'post_type' => $post_types,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    $post_ids = array_merge($post_ids, $authored_posts);

    $post_ids = array_merge($post_ids, aripplesong_get_participated_podcast_ids($user_id));

    return array_values(array_unique(array_filter(array_map('absint', $post_ids))));
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
    $user_id = absint($user_id);
    if (!$user_id) {
        return 0;
    }

    // Base count: all posts published by the user
    // count_user_posts() only counts 'post' type by default, so we need to count 'podcast' separately
    $regular_posts_count = count_user_posts($user_id, 'post');
    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
    $podcast_posts_count = $podcast_post_type ? count_user_posts($user_id, $podcast_post_type) : 0;
    $base_count = $regular_posts_count + (int) $podcast_posts_count;

    return $base_count + count(aripplesong_get_participated_podcast_ids($user_id));
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
            $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
            $post_types = ['post'];
            if ($podcast_post_type) {
                $post_types[] = $podcast_post_type;
            }

            // Reset query vars to prevent conflicts
            $query->set('post__in', $post_ids);
            $query->set('author', 0); // Set to 0 instead of empty string
            $query->set('author_name', ''); // Clear author_name too
            $query->set('post_type', $post_types); // Include both post types
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
if (!function_exists('aripplesong_get_episode_meta')) {
    function aripplesong_get_episode_meta(int $post_id, string $key, $default = '')
    {
        if ($post_id <= 0 || $key === '') {
            return $default;
        }

        if (function_exists('carbon_get_post_meta')) {
            $value = carbon_get_post_meta($post_id, $key);
            if (!is_array($value) && $value !== null && $value !== '') {
                return $value;
            }
        }

        $value = get_post_meta($post_id, $key, true);
        if ($value !== '' && $value !== null) {
            return $value;
        }

        $value = get_post_meta($post_id, '_' . ltrim($key, '_'), true);
        if ($value !== '' && $value !== null) {
            return $value;
        }

        return $default;
    }
}

function get_episode_data($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $audio_file = aripplesong_get_episode_meta($post_id, 'audio_file', '');
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
 * Get latest podcast episodes for hydrating the default player playlist.
 *
 * Returns a signature based on the ordered post IDs so clients can detect
 * whether the "latest" list has changed since the last visit.
 *
 * @param int $limit Number of episodes to return.
 * @return array{episodes: array<int, array>, signature: string}
 */
function aripplesong_get_latest_playlist_data($limit = 10) {
    $limit = absint($limit) ?: 10;

    $episodes = [];
    $ids = [];
    $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
    if (!$podcast_post_type) {
        return [
            'episodes' => [],
            'signature' => '',
        ];
    }

    $query = new \WP_Query([
        'post_type' => $podcast_post_type,
        'posts_per_page' => max($limit * 2, $limit),
        'post_status' => 'publish',
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
        'orderby' => 'date',
        'order' => 'DESC',
        'fields' => 'ids',
    ]);

    if (!empty($query->posts)) {
        foreach ($query->posts as $post_id) {
            if (count($episodes) >= $limit) {
                break;
            }

            $episode = get_episode_data($post_id);
            if (empty($episode['audioUrl'])) {
                continue;
            }

            $episodes[] = $episode;
            $ids[] = (int) $post_id;
        }
    }

    wp_reset_postdata();

    return [
        'episodes' => $episodes,
        'signature' => implode(',', $ids),
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
                            <span class="badge badge-primary badge-sm"><?php _e('Author', 'a-ripple-song'); ?></span>
                        <?php endif; ?>

                        <span class="text-xs text-base-content/60 flex items-center gap-1">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <?php echo get_localized_comment_date($comment); ?>
                        </span>

                        <?php if ($comment->comment_approved == '0'): ?>
                            <span class="badge badge-warning badge-sm"><?php _e('Pending Approval', 'a-ripple-song'); ?></span>
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
                            'reply_text' => '<i data-lucide="reply" class="w-4 h-4"></i> ' . __('Reply', 'a-ripple-song')
                        ]));
                        ?>

                        <?php edit_comment_link(
                            '<i data-lucide="pencil" class="w-4 h-4"></i> ' . __('Edit', 'a-ripple-song'),
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
    $defaults['comment_notes_before'] = '<p class="comment-notes text-sm text-base-content/60">' . __('Your email address will not be published.', 'a-ripple-song') . '</p>';
    $defaults['comment_notes_after'] = '';
    $defaults['logged_in_as'] = '<p class="logged-in-as text-sm text-base-content/60">' .
        sprintf(__('Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'a-ripple-song'),
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
    $fields['author'] = '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Name', 'a-ripple-song') . ' <span class="text-error">*</span></span></label><input type="text" id="author" name="author" class="input input-bordered w-full text-sm" required /></div>';

    $fields['email'] = '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Email', 'a-ripple-song') . ' <span class="text-error">*</span></span></label><input type="email" id="email" name="email" class="input input-bordered w-full text-sm" required /></div>';

    $fields['url'] = '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Website', 'a-ripple-song') . '</span></label><input type="url" id="url" name="url" class="input input-bordered w-full text-sm" /></div>';

    $fields['cookies'] = '<div class="form-control"><label class="comment-form-cookies-consent"><input type="checkbox" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" value="yes" class="checkbox" /><span class="label-text text-sm leading-relaxed">' . __('Save my name, email, and website in this browser for the next time I comment.', 'a-ripple-song') . '</span></label></div>';

    return $fields;
});

/**
 * Customize comment textarea field with DaisyUI styling
 */
add_filter('comment_form_field_comment', function($field) {
    return '<div class="form-control"><label class="label"><span class="label-text text-sm">' . __('Comment', 'a-ripple-song') . ' <span class="text-error">*</span></span></label><textarea id="comment" name="comment" rows="6" class="textarea textarea-bordered w-full text-sm" required></textarea></div>';
});
