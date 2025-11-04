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

collect(['setup', 'filters', 'podcast-types'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });

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
