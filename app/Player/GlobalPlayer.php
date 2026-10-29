<?php

namespace Jiejia\ARippleSong\Player;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ControllerAbstract;
use Jiejia\ARippleSong\CPTs\PodcastCPT;
use Jiejia\ARippleSong\Libraries\Template;
use Jiejia\ARippleSong\Plugin;
use Jiejia\ARippleSong\Settings\PlayerSetting;
use Jiejia\ARippleSong\Settings\PodcastSetting;

/**
 * The site-wide player, printed once in wp_footer on every front-end page.
 *
 * Its look is a skin chosen in the player settings (see PlayerSkin): a template in
 * resources/templates/player/skins/ plus a stylesheet in public/dist/skins/. A theme
 * overrides a template with {theme}/a-ripple-song/player/skins/{skin}.php. Behavior lives in
 * resources/assets/js/player/player.js.
 *
 * Themes that style the player themselves declare add_theme_support('a-ripple-song-player');
 * the plugin then loads neither the skin stylesheet nor its fonts, and leaves colors to the theme.
 */
final class GlobalPlayer
{
    public const HANDLE = Plugin::PREFIX . 'player';

    public const THEME_SUPPORT = Plugin::SLUG . '-player';

    /**
     * Number of latest episodes the player can queue without another request.
     */
    private const QUEUE_SIZE = 50;

    private PodcastCPT $podcast;

    private PlayerSetting $setting;

    /**
     * Whether assets were enqueued for this request, i.e. the player is shown.
     */
    private bool $active = false;

    public function __construct()
    {
        $this->podcast = new PodcastCPT();
        $this->setting = new PlayerSetting();
    }

    /**
     * Register front-end hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        // Before footer scripts (priority 20) so the markup exists when player.js runs.
        add_action('wp_footer', [$this, 'render'], 5);
    }

    /**
     * Enqueue the player assets and its episode data.
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        $episodes = $this->isEnabled() ? $this->episodes() : [];

        if ($episodes === []) {
            return;
        }

        $this->active = true;
        $skin = $this->setting->skin();

        // Page-level rules, kept even when the theme styles the player itself: room for the fixed bar,
        // no page scrolling behind the full-screen panel, skin elements hidden until bound, bar width.
        wp_register_style(self::HANDLE . '-base', false, [], Plugin::VERSION);
        wp_enqueue_style(self::HANDLE . '-base');
        wp_add_inline_style(self::HANDLE . '-base', 'body.has-ars-player{padding-bottom:var(--ars-player-height,5.5rem)}html.ars-player-panel-open{overflow:hidden}[ars-cloak]{display:none!important}'
            // Bar and panel widths set by player.js (setting "Player Width"); "content" containers keep their padding outside.
            . '.ars-player[data-ars-fit] [data-ars-width]{max-width:var(--ars-fit-width)!important}'
            . '.ars-player[data-ars-fit] [data-ars-width=content]{box-sizing:content-box!important}'
            // On phones the panel stays edge to edge like the prototypes; theme margins would squeeze its controls.
            . '@media (min-width:640px){.ars-player[data-ars-fit] [data-ars-panel-width]{max-width:var(--ars-fit-panel-width)!important}'
            . '.ars-player[data-ars-fit] [data-ars-panel-width=content]{box-sizing:content-box!important}}'
            // Frosted bar (setting "Player Bar Background"), tinted with the surface's own daisyUI color.
            . '.ars-player[data-ars-translucent] [data-ars-surface]{background-color:color-mix(in oklab,var(--color-base-100) 72%,transparent)!important;-webkit-backdrop-filter:blur(16px) saturate(180%);backdrop-filter:blur(16px) saturate(180%)}'
            . '.ars-player[data-ars-translucent] [data-ars-surface=base-200]{background-color:color-mix(in oklab,var(--color-base-200) 72%,transparent)!important}'
            // Waveform progress (player/parts/progress.php): wavesurfer draws, an invisible range input on top seeks.
            // !important beats the skins' form reset, which raises its specificity with :not(#\#) to beat themes.
            . '.ars-player .ars-wave{position:relative}.ars-player .ars-wave-seek{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;margin:0!important;opacity:0!important;cursor:pointer;z-index:1}'
            . '.ars-player .ars-wave:has(.ars-wave-seek:focus-visible){outline:2px solid var(--color-primary,currentColor);outline-offset:4px;border-radius:.375rem}'
            // wavesurfer's unplayed layer (shadow part), faded; the played part keeps the full color.
            . '.ars-player [data-ars-waveform] *::part(canvases){opacity:.3}'
            // Until the waveform is decoded (or when it cannot be), a faint line stands in for it.
            . '.ars-player .ars-wave:not(:has([data-ars-wave-ready]))::before{content:"";position:absolute;inset:50% 0 auto;height:2px;margin-top:-1px;border-radius:1px;background:currentColor;opacity:.2}');

        if (! current_theme_supports(self::THEME_SUPPORT)) {
            wp_enqueue_style(self::HANDLE, A_RIPPLE_SONG_PLUGIN_URL . $skin->stylesheet(), [], Plugin::assetVersion($skin->stylesheet()));

            if ($skin->fontsUrl() !== '') {
                // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts versions itself.
                wp_enqueue_style(self::HANDLE . '-fonts', $skin->fontsUrl(), [], null);
            }
        }

        wp_enqueue_script(self::HANDLE, A_RIPPLE_SONG_PLUGIN_URL . 'public/dist/player.js', [], Plugin::assetVersion('public/dist/player.js'), true);
        wp_add_inline_script(self::HANDLE, 'window.ARS_PLAYER = ' . wp_json_encode($this->config($episodes)) . ';', 'before');

        if ($this->setting->progressStyle() === 'waveform') {
            // wavesurfer.js, only for the waveform progress style; it follows the player through window.ARippleSongPlayer.
            wp_enqueue_script(self::HANDLE . '-waveform', A_RIPPLE_SONG_PLUGIN_URL . 'public/dist/waveform.js', [self::HANDLE], Plugin::assetVersion('public/dist/waveform.js'), true);
        }
    }

    /**
     * Print the player markup.
     *
     * @return void
     */
    public function render(): void
    {
        if ($this->active) {
            (new Template())->renderThemeable($this->setting->skin()->template());
        }
    }

    /**
     * Whether the player is switched on for the current request (it still needs a playable episode to show).
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        /**
         * Filters whether the global player is shown on the current page.
         *
         * @param bool $enabled Whether the player setting is on.
         */
        return (bool) apply_filters('a_ripple_song_global_player_enabled', $this->setting->isEnabled());
    }

    /**
     * Return the data player.js starts with.
     *
     * @param array<int,array<string,mixed>> $episodes Playable episodes.
     * @return array<string,mixed>
     */
    private function config(array $episodes): array
    {
        $podcast = new PodcastSetting();

        return [
            'episodes' => $episodes,
            'show' => [
                'title' => (string) $podcast->value('title'),
                'cover' => (string) $podcast->value('cover'),
            ],
            // Loaded (paused) for visitors without a saved position: the viewed episode, else the latest.
            'initial' => $this->setting->preloadsEpisode()
                ? (is_singular($this->podcast->slug()) ? get_queried_object_id() : (int) ($episodes[0]['id'] ?? 0))
                : 0,
            'width' => $this->setting->width(),
            'translucent' => $this->setting->isTranslucent(),
            // A theme that styles the player owns its colors; the player then just inherits them.
            'scheme' => current_theme_supports(self::THEME_SUPPORT) ? 'inherit' : $this->setting->colorScheme(),
            'storageKey' => self::HANDLE,
            'playsUrl' => rest_url(ControllerAbstract::NAMESPACE . '/episodes/%d/plays'),
            'i18n' => [
                'playing' => __('Playing', 'a-ripple-song'),
                'paused' => __('Paused', 'a-ripple-song'),
                'loading' => __('Loading…', 'a-ripple-song'),
                'error' => __('The audio could not be loaded.', 'a-ripple-song'),
                'play' => __('Play', 'a-ripple-song'),
                'pause' => __('Pause', 'a-ripple-song'),
                'mute' => __('Mute', 'a-ripple-song'),
                'unmute' => __('Unmute', 'a-ripple-song'),
                /* translators: %s: episode number. */
                'episode' => __('EP.%s', 'a-ripple-song'),
            ],
        ];
    }

    /**
     * Return the latest playable episodes, plus the episode being viewed.
     *
     * @return array<int,array<string,mixed>>
     */
    private function episodes(): array
    {
        $posts = get_posts([
            'post_type' => $this->podcast->slug(),
            'post_status' => 'publish',
            'numberposts' => self::QUEUE_SIZE,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        // An older episode page must be playable even when it is not in the latest list.
        $viewed = is_singular($this->podcast->slug()) ? get_queried_object() : null;

        if ($viewed instanceof \WP_Post && ! in_array($viewed->ID, wp_list_pluck($posts, 'ID'), true)) {
            array_unshift($posts, $viewed);
        }

        $fallbackCover = (string) (new PodcastSetting())->value('cover');
        $numbers = $this->episodeNumbers();

        return array_values(array_filter(
            array_map(fn (\WP_Post $post): array => $this->episode($post, $fallbackCover, $numbers[$post->ID] ?? 0), $posts),
            static fn (array $episode): bool => $episode['audio'] !== ''
        ));
    }

    /**
     * Number episodes by publish order (oldest = 1), for episodes without an explicit number.
     *
     * @return array<int,int> Episode number keyed by post ID.
     */
    private function episodeNumbers(): array
    {
        $ids = get_posts([
            'post_type' => $this->podcast->slug(),
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
            'fields' => 'ids',
        ]);

        return $ids === [] ? [] : array_combine(array_map('intval', $ids), range(1, count($ids)));
    }

    /**
     * Return the player data of one episode.
     *
     * @param \WP_Post $post Episode.
     * @param string $fallbackCover Podcast cover, used when the episode has none.
     * @param int $autoNumber Number by publish order.
     * @return array<string,mixed>
     */
    private function episode(\WP_Post $post, string $fallbackCover, int $autoNumber): array
    {
        $meta = fn (string $key): mixed => get_post_meta($post->ID, $this->podcast->fieldMetaKey($key), true);
        // Decoded because player.js sets these as text, not HTML.
        $text = static fn (string $html): string => trim(html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES, 'UTF-8'));
        $terms = get_the_terms($post, 'post_tag');

        return [
            'id' => $post->ID,
            'number' => (int) $meta('episode_number') ?: $autoNumber,
            'title' => $text(get_the_title($post)),
            'url' => (string) get_permalink($post),
            'audio' => (string) $meta('audio_file'),
            'cover' => (string) $meta('episode_image') ?: (string) get_the_post_thumbnail_url($post, 'medium') ?: $fallbackCover,
            'duration' => (int) $meta('duration'),
            'excerpt' => $text(get_the_excerpt($post)),
            'category' => is_array($terms) && $terms !== [] ? $terms[0]->name : '',
            'hosts' => $this->hosts($meta('members')),
            'plays' => $this->podcast->playCount($post->ID),
        ];
    }

    /**
     * Join the display names of the episode members, e.g. "Asen / Xiaolu".
     *
     * @param mixed $ids Stored member user IDs.
     * @return string
     */
    private function hosts(mixed $ids): string
    {
        $names = [];

        foreach (array_unique(array_map('intval', is_array($ids) ? $ids : [])) as $userId) {
            $user = $userId > 0 ? get_userdata($userId) : false;

            if ($user) {
                $names[] = $user->display_name ?: $user->user_login;
            }
        }

        return implode(' / ', $names);
    }
}
