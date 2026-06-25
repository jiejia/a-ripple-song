<?php

use App\CustomPostTypes\Episode;

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
        }
    }

    return array_values(array_unique(array_filter(array_map('absint', $ids))));
}

/**
 * Return the registered podcast episode post type.
 *
 * @return string Podcast episode post type slug.
 */
function aripplesong_episode_post_type(): string
{
    return Episode::slug();
}

/**
 * Return podcast episode meta from the new prefixed key with legacy fallback.
 *
 * @param int $post_id Episode post ID.
 * @param string $key Raw episode meta key.
 * @param mixed $default Fallback value when no meta exists.
 * @return mixed Stored episode meta value.
 */
function aripplesong_get_episode_meta(int $post_id, string $key, $default = '')
{
    $post_id = absint($post_id);

    if (!$post_id) {
        return $default;
    }

    $value = get_post_meta($post_id, Episode::storedFieldKey($key), true);

    if ($value !== '' && $value !== []) {
        if ($key === 'audio_file') {
            return Episode::resolveStoredAudioFileValue($value);
        }

        return $value;
    }

    $legacy_value = get_post_meta($post_id, $key, true);

    return ($legacy_value !== '' && $legacy_value !== []) ? $legacy_value : $default;
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
        'post_type' => aripplesong_episode_post_type(),
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

    // Get posts authored by the user (both 'post' and 'podcast' types)
    $authored_posts = get_posts([
        'author' => $user_id,
        'post_type' => ['post', aripplesong_episode_post_type()],
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
    $podcast_posts_count = count_user_posts($user_id, aripplesong_episode_post_type());
    $base_count = $regular_posts_count + $podcast_posts_count;

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
            // Reset query vars to prevent conflicts
            $query->set('post__in', $post_ids);
            $query->set('author', 0); // Set to 0 instead of empty string
            $query->set('author_name', ''); // Clear author_name too
            $query->set('post_type', ['post', aripplesong_episode_post_type()]); // Include both post types
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
 * Modify tag archive query to include podcast episodes.
 *
 * @param WP_Query $query The WordPress query instance.
 * @return void
 */
function aripplesong_include_episode_post_type_in_tag_archive($query): void
{
    // Only modify the public main query on tag archive pages.
    if (is_admin() || !$query->is_main_query() || !$query->is_tag()) {
        return;
    }

    // Include regular posts and podcast episodes that use the shared post_tag taxonomy.
    $query->set('post_type', ['post', aripplesong_episode_post_type()]);
}
add_action('pre_get_posts', 'aripplesong_include_episode_post_type_in_tag_archive');

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

    $audio_file = aripplesong_get_episode_meta((int) $post_id, 'audio_file');
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
 * Return excerpt HTML while preserving paragraph boundaries for list views.
 *
 * @param int|null $post_id Post ID (defaults to current post).
 * @return string Sanitized excerpt HTML.
 */
function aripplesong_get_paragraph_excerpt($post_id = null): string
{
    $post_id = $post_id ?: get_the_ID();
    $post = get_post($post_id);

    if (!$post instanceof \WP_Post) {
        return '';
    }

    $source = trim((string) $post->post_excerpt) !== '' ? $post->post_excerpt : $post->post_content;

    if (trim(wp_strip_all_tags($source)) === '') {
        return '';
    }

    $source = strip_shortcodes($source);
    $html = has_blocks($source) ? do_blocks($source) : wpautop($source);

    return wp_kses_post($html);
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

    $query = new \WP_Query([
        'post_type' => aripplesong_episode_post_type(),
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
