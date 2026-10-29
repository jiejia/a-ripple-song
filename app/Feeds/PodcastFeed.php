<?php

namespace Jiejia\ARippleSong\Feeds;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\FeedAbstract;
use Jiejia\ARippleSong\CPTs\PodcastCPT;
use Jiejia\ARippleSong\Libraries\Template;
use Jiejia\ARippleSong\Plugin;
use Jiejia\ARippleSong\Settings\PodcastSetting;

/**
 * Podcast RSS feed at /feed/podcast/ for Apple Podcasts, Spotify and other directories.
 *
 * This class gathers channel and episode data; resources/templates/feeds/podcast.php prints the XML.
 */
class PodcastFeed extends FeedAbstract
{
    public const SLUG = 'podcast';

    /**
     * Number of latest episodes listed in the feed.
     */
    private const MAX_ITEMS = 100;

    /**
     * Field rules of show and episode covers, after Apple Podcasts' artwork specs:
     * square JPG/PNG of 1400-3000px without transparency; 512 KB is the common hosting limit.
     */
    public const ARTWORK_RULES = [
        'dimensions' => [
            'square' => true,
            'min_width' => 1400,
            'min_height' => 1400,
            'max_width' => 3000,
            'max_height' => 3000,
        ],
        'mime_types' => ['image/jpeg', 'image/png'],
        'opaque' => true,
        'max_size' => 512 * KB_IN_BYTES,
    ];

    /**
     * Option set once the rewrite rules include the feed endpoint.
     */
    private const REWRITE_FLAG = Plugin::PREFIX . 'podcast_feed_rewrite_flushed';

    /**
     * URL paths that serve the feed.
     */
    private const PATH_PATTERN = '~/(?:feed/podcast|podcast/feed)/?$~';

    /**
     * Query strings that serve the feed.
     */
    private const QUERY_PATTERN = '~(?:^|&)feed=podcast(?:&|$)~';

    private PodcastCPT $podcast;

    private PodcastSetting $setting;

    public function __construct()
    {
        $this->podcast = new PodcastCPT();
        $this->setting = new PodcastSetting();
    }

    /**
     * Return the public feed URL for the current permalink mode.
     *
     * @return string
     */
    public static function url(): string
    {
        $structure = (string) get_option('permalink_structure');

        return match (true) {
            $structure === '' => home_url('/?feed=' . self::SLUG),
            str_starts_with($structure, '/index.php/') => home_url('/index.php/feed/' . self::SLUG . '/'),
            default => home_url('/feed/' . self::SLUG . '/'),
        };
    }

    /**
     * Return the WordPress feed slug.
     *
     * @return string
     */
    public function slug(): string
    {
        return self::SLUG;
    }

    /**
     * Register the feed endpoint and routing safeguards.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        parent::registerHooks();

        add_filter('request', [$this, 'routePlainSlug']);
        add_filter('feed_content_type', [$this, 'contentType'], 10, 2);
        add_filter('redirect_canonical', [$this, 'keepFeedUrl']);
        add_action('admin_init', [$this, 'maybeFlushRewriteRules']);
    }

    /**
     * Send a plain /podcast/ URL to the episode archive instead of the feed.
     *
     * WordPress treats every feed name as a top-level path too, so /podcast/ would otherwise render RSS.
     *
     * @param array<string,mixed> $vars Parsed query vars.
     * @return array<string,mixed>
     */
    public function routePlainSlug(array $vars): array
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $isFeedUrl = preg_match(self::PATH_PATTERN, (string) wp_parse_url($uri, PHP_URL_PATH))
            || preg_match(self::QUERY_PATTERN, (string) wp_parse_url($uri, PHP_URL_QUERY));

        if (($vars['feed'] ?? '') !== self::SLUG || $isFeedUrl) {
            return $vars;
        }

        unset($vars['feed']);

        return post_type_exists($this->podcast->slug()) ? ['post_type' => $this->podcast->slug()] + $vars : $vars;
    }

    /**
     * Serve the feed as RSS; WordPress falls back to application/octet-stream for custom feeds.
     *
     * @param string $contentType Content type.
     * @param string $type Feed type.
     * @return string
     */
    public function contentType(string $contentType, string $type): string
    {
        return $type === self::SLUG ? 'application/rss+xml' : $contentType;
    }

    /**
     * Keep canonical redirects from rewriting the feed URL.
     *
     * @param string|false $redirectUrl Redirect target.
     * @return string|false
     */
    public function keepFeedUrl(string|false $redirectUrl): string|false
    {
        return is_feed(self::SLUG) ? false : $redirectUrl;
    }

    /**
     * Flush rewrite rules once so /feed/podcast/ starts working after deployment.
     *
     * @return void
     */
    public function maybeFlushRewriteRules(): void
    {
        if (get_option(self::REWRITE_FLAG) || ! current_user_can('manage_options')) {
            return;
        }

        flush_rewrite_rules(false);
        update_option(self::REWRITE_FLAG, '1', false);
    }

    /**
     * Print the feed.
     *
     * @return void
     */
    public function render(): void
    {
        nocache_headers();
        header('X-Content-Type-Options: nosniff');

        $channel = $this->channel();
        $items = [];
        $query = new \WP_Query([
            'post_type' => $this->podcast->slug(),
            'post_status' => 'publish',
            'posts_per_page' => self::MAX_ITEMS,
            'orderby' => 'date',
            'order' => 'DESC',
            'no_found_rows' => true,
        ]);

        // the_post() sets the globals the_content filters and get_the_excerpt() rely on.
        while ($query->have_posts()) {
            $query->the_post();
            $items[] = $this->item(get_post(), $channel);
        }

        wp_reset_postdata();

        $latest = $query->posts !== [] ? (int) get_post_time('U', true, $query->posts[0]) : 0;
        $channel['lastBuildDate'] = $this->date($latest ?: time());

        (new Template())->render('feeds/podcast', ['channel' => $channel, 'items' => $items]);
    }

    /**
     * Escape the CDATA terminator so text can be wrapped in a CDATA section.
     *
     * @param string $text Text to wrap.
     * @return string
     */
    public static function cdata(string $text): string
    {
        return str_replace(']]>', ']]]]><![CDATA[>', $text);
    }

    /**
     * Collect channel data from the podcast settings.
     *
     * @return array<string,mixed>
     */
    private function channel(): array
    {
        $title = $this->settingText('title');
        $subtitle = $this->settingText('subtitle');
        $author = $this->settingText('author');
        $cover = $this->encodeUrl($this->settingText('cover'));
        $funding = $this->setting->value('funding');

        return [
            'title' => $title,
            'link' => home_url('/'),
            'feedUrl' => self::url(),
            'language' => $this->language($this->settingText('language')),
            'description' => $this->channelDescription($this->settingText('description'), $title, $subtitle, $author),
            'subtitle' => $subtitle,
            'author' => $author,
            'explicit' => $this->explicit($this->settingText('explicit'), 'false'),
            'ownerName' => $this->settingText('owner_name'),
            'ownerEmail' => $this->settingText('owner_email'),
            'cover' => $cover,
            // Episodes without their own cover fall back to the show cover, then the site icon.
            'defaultImage' => $cover !== '' ? $cover : $this->encodeUrl((string) get_site_icon_url(1400)),
            'categories' => array_values(array_filter(array_map(
                static fn (string $category): array => $category !== '' ? explode('::', $category, 2) : [],
                [$this->settingText('category_primary'), $this->settingText('category_secondary')]
            ))),
            'copyright' => $this->settingText('copyright'),
            'itunesType' => $this->settingText('itunes_type'),
            'itunesTitle' => $this->settingText('itunes_title'),
            'itunesBlock' => $this->settingText('itunes_block') === 'yes',
            'itunesComplete' => $this->settingText('itunes_complete') === 'yes',
            'newFeedUrl' => $this->settingText('itunes_new_feed_url'),
            'locked' => $this->settingText('locked') === 'yes',
            'lockedOwner' => $this->settingText('locked_owner'),
            'guid' => $this->settingText('guid'),
            'appleVerify' => $this->settingText('apple_verify'),
            'generator' => $this->settingText('generator'),
            'funding' => array_values(array_filter(
                is_array($funding) ? $funding : [],
                static fn (mixed $link): bool => is_array($link) && trim((string) ($link['url'] ?? '')) !== ''
            )),
        ];
    }

    /**
     * Collect the data of one episode.
     *
     * @param \WP_Post $post Episode.
     * @param array<string,mixed> $channel Channel data, used for fallbacks.
     * @return array<string,mixed>
     */
    private function item(\WP_Post $post, array $channel): array
    {
        $meta = fn (string $key): mixed => get_post_meta($post->ID, $this->podcast->fieldMetaKey($key), true);
        $text = static fn (mixed $value): string => is_scalar($value) ? trim((string) $value) : '';

        $permalink = (string) get_permalink($post);
        $guid = $text($meta('episode_guid'));
        $audioUrl = $this->encodeUrl($text($meta('audio_file')));
        $transcript = $this->encodeUrl($text($meta('episode_transcript')));
        $chapters = $this->encodeUrl($text($meta('episode_chapters')));
        $summary = $text($meta('episode_summary'));
        $duration = (int) $meta('duration');

        return [
            'title' => get_the_title($post),
            'link' => $permalink,
            'guid' => $guid !== '' ? $guid : $permalink,
            'guidIsPermalink' => $guid === '' || $guid === $permalink,
            'pubDate' => $this->date((int) get_post_time('U', true, $post)),
            'summary' => $this->summary($summary !== '' ? $summary : get_the_excerpt($post)),
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter hook.
            'content' => (string) apply_filters('the_content', get_the_content(null, false, $post)),
            'enclosure' => $audioUrl !== '' ? ['url' => $audioUrl, 'length' => $text($meta('audio_length')), 'type' => $text($meta('audio_mime'))] : null,
            'duration' => $duration > 0 ? $duration : null,
            'explicit' => $this->explicit($text($meta('episode_explicit')), $channel['explicit']),
            'author' => $text($meta('episode_author')) ?: $channel['author'],
            'itunesTitle' => $text($meta('itunes_title')),
            'subtitle' => $text($meta('episode_subtitle')),
            'image' => $this->encodeUrl($text($meta('episode_image'))) ?: $channel['defaultImage'],
            'people' => $this->people($meta('members'), $meta('guests')),
            'transcript' => $transcript !== '' ? ['url' => $transcript, 'type' => $this->transcriptType($transcript)] : null,
            // Podcasting 2.0 requires chapters over https; localhost is allowed for testing.
            'chapters' => preg_match('~^(?:https://|http://localhost(?::\d+)?/)~i', $chapters)
                ? ['url' => $chapters, 'type' => $text($meta('episode_chapters_type')) ?: 'application/json+chapters']
                : null,
            'soundbites' => $this->soundbites($meta('episode_soundbites')),
            'episode' => max(0, (int) $meta('episode_number')),
            'season' => max(0, (int) $meta('season_number')),
            'episodeType' => $text($meta('episode_type')) ?: 'full',
            'block' => $meta('episode_block') === 'yes',
        ];
    }

    /**
     * Return a podcast setting as trimmed text.
     *
     * @param string $key Setting field key.
     * @return string
     */
    private function settingText(string $key): string
    {
        $value = $this->setting->value($key);

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /**
     * Build Podcasting 2.0 person entries: members as hosts, then guests; duplicates are dropped.
     *
     * @param mixed $members Stored member user IDs.
     * @param mixed $guests Stored guest user IDs.
     * @return array<int,array{name:string,role:string,href:string,img:string}>
     */
    private function people(mixed $members, mixed $guests): array
    {
        $people = [];

        foreach (['host' => $members, 'guest' => $guests] as $role => $ids) {
            foreach (array_unique(array_map('intval', is_array($ids) ? $ids : [])) as $userId) {
                $user = $userId > 0 ? get_userdata($userId) : false;
                $name = $user ? ($user->display_name ?: $user->user_nicename ?: $user->user_login) : '';

                if ($name === '') {
                    continue;
                }

                $people[strtolower($name) . '|' . $role] ??= [
                    'name' => $name,
                    'role' => $role,
                    'href' => (string) $user->user_url,
                    'img' => (string) get_avatar_url($userId, ['size' => 300]),
                ];
            }
        }

        return array_values($people);
    }

    /**
     * Keep soundbites with a valid start time and duration.
     *
     * @param mixed $rows Stored soundbite rows.
     * @return array<int,array{start:float,duration:float,title:string}>
     */
    private function soundbites(mixed $rows): array
    {
        $soundbites = [];

        foreach (is_array($rows) ? $rows : [] as $row) {
            if (! is_array($row) || ! is_numeric($row['start_time'] ?? null) || ! is_numeric($row['duration'] ?? null)) {
                continue;
            }

            $start = (float) $row['start_time'];
            $duration = (float) $row['duration'];

            if ($start >= 0 && $duration > 0) {
                $soundbites[] = ['start' => $start, 'duration' => $duration, 'title' => trim((string) ($row['title'] ?? ''))];
            }
        }

        return $soundbites;
    }

    /**
     * Pad a short channel description; some validators require at least 50 characters.
     *
     * @param string $description Channel description.
     * @param string $title Podcast title.
     * @param string $subtitle Podcast subtitle.
     * @param string $author Podcast author.
     * @return string
     */
    private function channelDescription(string $description, string $title, string $subtitle, string $author): string
    {
        $plain = html_entity_decode(wp_strip_all_tags($description, true), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (mb_strlen($plain, 'UTF-8') >= 50) {
            return $description;
        }

        $header = implode(' — ', array_filter([$title, $subtitle]));
        $addon = array_filter([
            $header !== '' ? $header . '.' : '',
            /* translators: %s: host/author name */
            $author !== '' ? sprintf(__('Hosted by %s.', 'a-ripple-song'), $author) : '',
            /* translators: %s: podcast feed URL */
            sprintf(__('Subscribe: %s', 'a-ripple-song'), self::url()),
        ]);

        return trim($description . ' ' . implode(' ', $addon));
    }

    /**
     * Turn an excerpt or summary into plain text without "… Continued"-style suffixes.
     *
     * @param string $text Summary text.
     * @return string
     */
    private function summary(string $text): string
    {
        $text = html_entity_decode(wp_strip_all_tags($text, true), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string) preg_replace(['/\s+/u', '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u'], [' ', ''], $text);
        $text = str_replace(['**', '__'], '', $text);
        $text = (string) preg_replace(
            '/(?:\s*(?:\[\s*)?(?:&hellip;|…|\.{3})(?:\s*\])?)?\s*(?:continued|continue\s+reading|read\s+more|继续阅读|繼續閱讀|阅读更多)\s*$/iu',
            '',
            $text
        );

        return trim($text);
    }

    /**
     * Normalize an explicit flag to the "true" / "false" Apple expects.
     *
     * @param string $value Stored value, e.g. explicit, clean, yes, no.
     * @param string $fallback Value used when the flag is unknown.
     * @return string
     */
    private function explicit(string $value, string $fallback): string
    {
        return match (strtolower($value)) {
            'yes', 'true', '1', 'explicit' => 'true',
            'no', 'false', '0', 'clean' => 'false',
            default => $fallback,
        };
    }

    /**
     * Reduce a language tag to its ISO 639-1 code ("en-US" => "en"); some validators reject region tags.
     *
     * @param string $language Language tag.
     * @return string
     */
    private function language(string $language): string
    {
        return preg_match('/^([a-z]{2})(?:[-_]|$)/i', $language, $matches) ? strtolower($matches[1]) : 'en';
    }

    /**
     * Format a timestamp as an RFC 822 date; RSS validators expect the two-digit year.
     *
     * @param int $timestamp Unix timestamp.
     * @return string
     */
    private function date(int $timestamp): string
    {
        return gmdate('D, d M y H:i:s O', $timestamp > 0 ? $timestamp : time());
    }

    /**
     * Guess the MIME type of a transcript from its extension.
     *
     * @param string $url Transcript URL.
     * @return string
     */
    private function transcriptType(string $url): string
    {
        return match (strtolower(pathinfo((string) wp_parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION))) {
            'txt' => 'text/plain',
            'vtt' => 'text/vtt',
            'srt' => 'application/srt',
            'json' => 'application/json',
            'pdf' => 'application/pdf',
            default => 'text/html',
        };
    }

    /**
     * Percent-encode non-ASCII characters in a URL path; podcast apps choke on raw UTF-8 file names.
     *
     * @param string $url URL to encode.
     * @return string
     */
    private function encodeUrl(string $url): string
    {
        $url = trim($url);
        $authority = strpos($url, '//');
        // The path runs from the first slash after "scheme://host" to the query or fragment.
        // Located by hand because parse_url() replaces some UTF-8 bytes with "_".
        $start = $authority === false ? false : strpos($url, '/', $authority + 2);

        if ($start === false || ! wp_parse_url($url, PHP_URL_HOST)) {
            return $url;
        }

        $length = strcspn($url, '?#', $start);
        $encoded = implode('/', array_map(
            static fn (string $segment): string => rawurlencode(rawurldecode($segment)),
            explode('/', substr($url, $start, $length))
        ));

        return substr_replace($url, $encoded, $start, $length);
    }
}
