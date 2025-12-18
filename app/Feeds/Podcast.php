<?php

namespace App\Feeds;

/**
 * Register /feed/podcast for Apple Podcasts / Spotify / YouTube Music + Podcasting 2.0.
 */
class Podcast
{
    /**
    * Register the podcast feed hook.
    */
    public function register(): void
    {
        add_action('init', [$this, 'registerFeed'], 20);
        add_action('pre_get_posts', [$this, 'fixPodcastArchiveQuery'], 1);
        add_action('template_redirect', [$this, 'preventPodcastSlugFromRenderingFeed'], 0);
        add_action('template_redirect', [$this, 'redirectQueryFeedToPretty'], 1);
        add_action('admin_init', [$this, 'maybeFlushRewriteRules']);
        add_filter('redirect_canonical', [$this, 'preventCanonicalRedirectForPodcastFeed'], 10, 2);
    }

    /**
     * Add the podcast feed endpoint.
     */
    public function registerFeed(): void
    {
        add_feed('podcast', [$this, 'renderFeed']);
    }

    /**
     * Fix podcast archive query being incorrectly identified as a feed.
     * This runs early in pre_get_posts to correct the query before template loading.
     */
    public function fixPodcastArchiveQuery(\WP_Query $query): void
    {
        if (!$query->is_main_query() || is_admin()) {
            return;
        }

        // Check if WordPress thinks this is a podcast feed
        if (!$query->is_feed || $query->get('feed') !== 'podcast') {
            return;
        }

        // But it's not actually a valid feed URL
        if ($this->isValidPodcastFeedUrl()) {
            return;
        }

        // Fix the query - this is actually a post type archive, not a feed
        $query->is_feed = false;
        $query->set('feed', '');

        if (post_type_exists('podcast')) {
            $query->is_post_type_archive = true;
            $query->is_archive = true;
            $query->set('post_type', 'podcast');
        }
    }

    /**
     * Check if current URL is a valid podcast feed URL.
     */
    private function isValidPodcastFeedUrl(): bool
    {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = wp_parse_url($request_uri, PHP_URL_PATH);
        $query = wp_parse_url($request_uri, PHP_URL_QUERY);

        // Allow /feed/podcast/ or /podcast/feed/
        if (is_string($path) && preg_match('~(?:feed/podcast|podcast/feed)/?$~', $path)) {
            return true;
        }

        // Allow /?feed=podcast
        if (is_string($query) && preg_match('~(?:^|&)feed=podcast(?:&|$)~', $query)) {
            return true;
        }

        return false;
    }

    /**
     * Prevent /podcast/ from being treated as the podcast feed.
     * Only /feed/podcast/ or /?feed=podcast should render the RSS feed.
     */
    public function preventPodcastSlugFromRenderingFeed(): void
    {
        if (!function_exists('is_feed') || !is_feed('podcast')) {
            return;
        }

        if ($this->isValidPodcastFeedUrl()) {
            return;
        }

        // Not a valid feed URL (e.g., /podcast/ archive page)
        // Reset all feed-related flags and query vars
        global $wp_query;
        $wp_query->is_feed = false;
        $wp_query->set('feed', '');
        unset($wp_query->query_vars['feed']);

        // Remove the feed action to prevent any RSS output
        remove_all_actions('do_feed_podcast');

        // Re-parse the request as a post type archive
        if (post_type_exists('podcast')) {
            $wp_query->is_post_type_archive = true;
            $wp_query->is_archive = true;
            $wp_query->set('post_type', 'podcast');

            // Force WordPress to load the archive template instead of feed
            add_filter('template_include', function ($template) {
                // Let WordPress find the appropriate archive template
                $new_template = get_post_type_archive_template();
                if ($new_template) {
                    return $new_template;
                }
                return get_archive_template() ?: $template;
            }, 999);
        }
    }

    /**
     * Whether rewrite rules include the custom `podcast` feed endpoint.
     */
    private function prettyPodcastFeedIsRegistered(): bool
    {
        $rules = get_option('rewrite_rules');
        if (!is_array($rules)) {
            return false;
        }

        foreach (array_keys($rules) as $regex) {
            if (strpos($regex, 'podcast') !== false && strpos($regex, 'feed') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Canonical feed URL (prefer pretty permalink, fallback to query form).
     */
    private function getCanonicalFeedUrl(): string
    {
        if ($this->prettyPodcastFeedIsRegistered()) {
            return home_url('/feed/podcast/');
        }

        return add_query_arg('feed', 'podcast', home_url('/'));
    }

    /**
     * One-time rewrite flush so `/feed/podcast/` starts working after deployment.
     */
    public function maybeFlushRewriteRules(): void
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        $flag = 'aripplesong_podcast_feed_rewrite_flushed_v1';
        if (get_option($flag)) {
            return;
        }

        flush_rewrite_rules(false);
        update_option($flag, '1', 'no');
    }

    /**
     * Prevent WordPress canonical redirects from rewriting the feed URL (e.g. `/feed/podcast/` → `/podcast/`).
     *
     * @param string|false $redirect_url
     * @param string $requested_url
     * @return string|false
     */
    public function preventCanonicalRedirectForPodcastFeed($redirect_url, string $requested_url)
    {
        if (function_exists('is_feed') && is_feed('podcast')) {
            return false;
        }

        $path = wp_parse_url($requested_url, PHP_URL_PATH);
        if (is_string($path) && preg_match('~/(?:feed/podcast|podcast/feed)/?$~', $path)) {
            return false;
        }

        $query = wp_parse_url($requested_url, PHP_URL_QUERY);
        if (is_string($query) && preg_match('~(?:^|&)feed=podcast(?:&|$)~', $query)) {
            return false;
        }

        return $redirect_url;
    }

    /**
     * Prefer the attachment URL when the media was selected from the WP Media Library (CMB2 stores `{key}_id`).
     */
    private function resolveMediaUrl(int $postId, string $urlMetaKey): string
    {
        $url = (string) get_post_meta($postId, $urlMetaKey, true);
        $attachmentId = (int) get_post_meta($postId, $urlMetaKey . '_id', true);

        if ($attachmentId > 0) {
            $attachmentUrl = wp_get_attachment_url($attachmentId);
            if (is_string($attachmentUrl) && $attachmentUrl !== '') {
                return $attachmentUrl;
            }
        }

        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            $maybeId = attachment_url_to_postid($url);
            if ($maybeId) {
                $attachmentUrl = wp_get_attachment_url($maybeId);
                if (is_string($attachmentUrl) && $attachmentUrl !== '') {
                    return $attachmentUrl;
                }
            }

            $maybePostId = url_to_postid($url);
            if ($maybePostId && get_post_type($maybePostId) === 'attachment') {
                $attachmentUrl = wp_get_attachment_url($maybePostId);
                if (is_string($attachmentUrl) && $attachmentUrl !== '') {
                    return $attachmentUrl;
                }
            }
        }

        return $url;
    }

    /**
     * Encode non-ASCII characters in the URL path for better compatibility with podcast clients/validators.
     *
     * Some validators treat unescaped Unicode in <enclosure url="..."> as an invalid URL and may misreport
     * it as a byte-range (HTTP Range) support issue.
     */
    private function encodeUrlPathForRss(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $parts = wp_parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? '';
        $host = $parts['host'];
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $user = isset($parts['user']) ? (string) $parts['user'] : '';
        $pass = isset($parts['pass']) ? (string) $parts['pass'] : '';
        $path = isset($parts['path']) ? (string) $parts['path'] : '';
        $query = isset($parts['query']) ? (string) $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? (string) $parts['fragment'] : '';

        if ($path !== '') {
            $segments = explode('/', $path);
            foreach ($segments as $i => $segment) {
                if ($segment === '') {
                    continue;
                }
                $segments[$i] = rawurlencode(rawurldecode($segment));
            }
            $path = implode('/', $segments);
        }

        $rebuilt = '';
        if ($scheme !== '') {
            $rebuilt .= $scheme . '://';
        }

        if ($user !== '') {
            $rebuilt .= $user;
            if ($pass !== '') {
                $rebuilt .= ':' . $pass;
            }
            $rebuilt .= '@';
        }

        $rebuilt .= $host;
        if ($port) {
            $rebuilt .= ':' . $port;
        }

        $rebuilt .= $path;

        if ($query !== '') {
            $rebuilt .= '?' . $query;
        }

        if ($fragment !== '') {
            $rebuilt .= '#' . $fragment;
        }

        return $rebuilt;
    }

    /**
     * Redirect `/?feed=podcast` to `/feed/podcast/` for better sharing/SEO.
     */
    public function redirectQueryFeedToPretty(): void
    {
        if (!function_exists('is_feed') || !is_feed('podcast')) {
            return;
        }

        if (!isset($_GET['feed']) || $_GET['feed'] !== 'podcast') {
            return;
        }

        if (!$this->prettyPodcastFeedIsRegistered()) {
            return;
        }

        wp_safe_redirect(home_url('/feed/podcast/'), 301);
        exit;
    }

    /**
     * Normalize <itunes:explicit> values to Apple-accepted values.
     *
     * Apple expects: "true" or "false" (modern spec).
     *
     * @param mixed $value
     */
    private function normalizeItunesExplicit($value, string $fallback = 'false'): string
    {
        $normalized = strtolower(trim((string) $value));

        // Explicit content indicators
        if (in_array($normalized, ['yes', 'true', '1', 'explicit'], true)) {
            return 'true';
        }

        // Clean/non-explicit content indicators
        if (in_array($normalized, ['no', 'false', '0', 'clean'], true)) {
            return 'false';
        }

        return 'false';
    }

    /**
     * Format seconds to HH:MM:SS.
     */
    private function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%02d:%02d', $m, $s);
    }

    /**
     * Remove WordPress/theme excerpt suffixes like "&hellip; Continued" from RSS summary fields.
     */
    private function sanitizeRssSummary(string $text): string
    {
        $text = wp_strip_all_tags($text, true);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\\s+/u', ' ', $text) ?? $text;

        $text = preg_replace(
            '/\\s*(?:\\[\\s*)?(?:&hellip;|…|\\.{3})(?:\\s*\\])?\\s*(?:continued|continue\\s+reading|read\\s+more)\\b\\s*$/iu',
            '',
            $text
        ) ?? $text;

        $text = preg_replace('/\\s*(?:continued|continue\\s+reading|read\\s+more)\\b\\s*$/iu', '', $text) ?? $text;

        return trim($text);
    }

    /**
     * Render the podcast RSS feed.
     */
    public function renderFeed(): void
    {
        // Double-check we're on a valid feed URL before rendering RSS
        if (!$this->isValidPodcastFeedUrl()) {
            // Not a valid feed URL (e.g., /podcast/ archive page) - abort and let WordPress handle it
            return;
        }

        header('Content-Type: application/rss+xml; charset=UTF-8');

        $site_url = home_url('/');
        $feed_url = $this->getCanonicalFeedUrl();
        $channel_title = carbon_get_theme_option('crb_podcast_title') ?: get_bloginfo('name');
        $channel_subtitle = carbon_get_theme_option('crb_podcast_subtitle') ?: '';
        $channel_description = carbon_get_theme_option('crb_podcast_description') ?: get_bloginfo('description');
        $channel_author = carbon_get_theme_option('crb_podcast_author') ?: get_bloginfo('name');
        $channel_owner_name = carbon_get_theme_option('crb_podcast_owner_name') ?: $channel_author;
        $channel_owner_email = carbon_get_theme_option('crb_podcast_owner_email') ?: get_bloginfo('admin_email');
        $channel_cover = $this->encodeUrlPathForRss((string) (carbon_get_theme_option('crb_podcast_cover') ?: ''));
        $channel_explicit = $this->normalizeItunesExplicit(carbon_get_theme_option('crb_podcast_explicit') ?: 'false', 'false');
        $channel_language = carbon_get_theme_option('crb_podcast_language') ?: (get_bloginfo('language') ?: 'en-US');
        $channel_category_primary = carbon_get_theme_option('crb_podcast_category_primary') ?: '';
        $channel_category_secondary = carbon_get_theme_option('crb_podcast_category_secondary') ?: '';
        $channel_copyright = carbon_get_theme_option('crb_podcast_copyright') ?: '';
        $podcast_locked = carbon_get_theme_option('crb_podcast_locked') ?: 'yes';
        $podcast_guid = carbon_get_theme_option('crb_podcast_guid') ?: $site_url;

        $query = new \WP_Query([
            'post_type' => 'podcast',
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        ?>
<rss version="2.0"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:podcast="https://podcastindex.org/namespace/1.0">
    <channel>
        <title><?php echo esc_html($channel_title); ?></title>
        <link><?php echo esc_url($site_url); ?></link>
        <atom:link href="<?php echo esc_url($feed_url); ?>" rel="self" type="application/rss+xml" />
        <language><?php echo esc_html($channel_language); ?></language>
        <description><?php echo esc_html($channel_description); ?></description>
        <?php if ($channel_subtitle) : ?>
        <itunes:subtitle><?php echo esc_html($channel_subtitle); ?></itunes:subtitle>
        <?php endif; ?>
        <itunes:author><?php echo esc_html($channel_author); ?></itunes:author>
        <itunes:summary><?php echo esc_html($channel_description); ?></itunes:summary>
        <itunes:explicit><?php echo esc_html($channel_explicit ?: 'false'); ?></itunes:explicit>
        <?php if ($channel_cover) : ?>
        <itunes:image href="<?php echo esc_url($channel_cover); ?>" />
        <?php endif; ?>
        <itunes:owner>
            <itunes:name><?php echo esc_html($channel_owner_name); ?></itunes:name>
            <itunes:email><?php echo esc_html($channel_owner_email); ?></itunes:email>
        </itunes:owner>
        <?php if ($channel_copyright) : ?>
        <copyright><?php echo esc_html($channel_copyright); ?></copyright>
        <?php endif; ?>
        <?php
        $categories = array_filter([$channel_category_primary, $channel_category_secondary]);
        foreach ($categories as $cat) {
            $parts = explode('::', $cat);
            if (count($parts) === 2) {
                printf(
                    '<itunes:category text="%1$s"><itunes:category text="%2$s" /></itunes:category>',
                    esc_attr($parts[0]),
                    esc_attr($parts[1])
                );
            } else {
                printf('<itunes:category text="%s" />', esc_attr($parts[0]));
            }
        }
        ?>
        <podcast:locked><?php echo esc_html($podcast_locked); ?></podcast:locked>
        <podcast:guid><?php echo esc_html($podcast_guid); ?></podcast:guid>
        <lastBuildDate><?php echo esc_html(gmdate('r')); ?></lastBuildDate>
        <?php
        if ($query->have_posts()) :
            while ($query->have_posts()) :
                $query->the_post();

                $post_id = get_the_ID();
                $audio_url = $this->encodeUrlPathForRss($this->resolveMediaUrl($post_id, 'audio_file'));
                $audio_length = (int) get_post_meta($post_id, 'audio_length', true);
                if (empty($audio_url) || $audio_length <= 0) {
                    $audio_attachment_id = (int) get_post_meta($post_id, 'audio_file_id', true);
                    if ($audio_attachment_id > 0) {
                        $file_path = get_attached_file($audio_attachment_id);
                        if (is_string($file_path) && $file_path !== '' && file_exists($file_path)) {
                            $audio_length = (int) filesize($file_path);
                        }
                    }

                    if (empty($audio_url) || $audio_length <= 0) {
                        // Skip invalid episodes to keep feed valid.
                        continue;
                    }
                }

                $audio_mime = get_post_meta($post_id, 'audio_mime', true) ?: 'audio/mpeg';
                $duration_seconds = (int) get_post_meta($post_id, 'duration', true);
                $duration_formatted = $duration_seconds ? $this->formatDuration($duration_seconds) : '';
                $episode_explicit = $this->normalizeItunesExplicit(get_post_meta($post_id, 'episode_explicit', true), $channel_explicit);
                $episode_type = get_post_meta($post_id, 'episode_type', true) ?: 'full';
                $episode_number = get_post_meta($post_id, 'episode_number', true);
                $season_number = get_post_meta($post_id, 'season_number', true);
                $episode_author = get_post_meta($post_id, 'episode_author', true) ?: $channel_author;
                $episode_image = $this->encodeUrlPathForRss($this->resolveMediaUrl($post_id, 'episode_image')) ?: $channel_cover;
                $episode_subtitle = get_post_meta($post_id, 'episode_subtitle', true);
                $episode_summary = get_post_meta($post_id, 'episode_summary', true);
                $episode_block = get_post_meta($post_id, 'episode_block', true) ?: 'no';
                $episode_permalink = get_permalink();
                $episode_guid = get_post_meta($post_id, 'episode_guid', true) ?: $episode_permalink;

                $item_summary = $episode_summary ?: get_the_excerpt();
                $item_summary = $this->sanitizeRssSummary((string) $item_summary);
                $content_html = apply_filters('the_content', get_the_content(null, false, $post_id));
                $content_html = str_replace(']]>', ']]]]><![CDATA[>', (string) $content_html);
                $pub_date = mysql2date('r', get_post_time('Y-m-d H:i:s', true, $post_id), false);
                ?>
        <item>
            <title><?php echo esc_html(get_the_title()); ?></title>
            <link><?php echo esc_url(get_permalink()); ?></link>
            <guid isPermaLink="<?php echo esc_attr($episode_guid === $episode_permalink ? 'true' : 'false'); ?>"><?php echo esc_html($episode_guid); ?></guid>
            <pubDate><?php echo esc_html($pub_date); ?></pubDate>
            <description><?php echo esc_html($item_summary); ?></description>
            <itunes:summary><?php echo esc_html($item_summary); ?></itunes:summary>
            <?php if ($content_html) : ?>
            <content:encoded><![CDATA[<?php echo $content_html; ?>]]></content:encoded>
            <?php endif; ?>
            <enclosure url="<?php echo esc_url($audio_url); ?>" length="<?php echo esc_attr($audio_length); ?>" type="<?php echo esc_attr($audio_mime); ?>" />
            <?php if ($duration_formatted) : ?>
            <itunes:duration><?php echo esc_html($duration_formatted); ?></itunes:duration>
            <?php endif; ?>
            <itunes:explicit><?php echo esc_html($episode_explicit ?: $channel_explicit ?: 'false'); ?></itunes:explicit>
            <itunes:author><?php echo esc_html($episode_author); ?></itunes:author>
            <?php if ($episode_subtitle) : ?>
            <itunes:subtitle><?php echo esc_html($episode_subtitle); ?></itunes:subtitle>
            <?php endif; ?>
            <?php if ($episode_image) : ?>
            <itunes:image href="<?php echo esc_url($episode_image); ?>" />
            <?php endif; ?>
            <?php if (!empty($episode_number)) : ?>
            <itunes:episode><?php echo esc_html((int) $episode_number); ?></itunes:episode>
            <?php endif; ?>
            <?php if (!empty($season_number)) : ?>
            <itunes:season><?php echo esc_html((int) $season_number); ?></itunes:season>
            <?php endif; ?>
            <itunes:episodeType><?php echo esc_html($episode_type); ?></itunes:episodeType>
            <?php if ($episode_block === 'yes') : ?>
            <itunes:block>yes</itunes:block>
            <?php endif; ?>
        </item>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
    </channel>
</rss>
        <?php
    }
}

(new Podcast())->register();
