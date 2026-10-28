<?php

namespace Jiejia\ARippleSong\Player;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\CPTs\PodcastCPT;
use Jiejia\ARippleSong\Libraries\Html;
use Jiejia\ARippleSong\Libraries\Template;
use Jiejia\ARippleSong\Plugin;
use Jiejia\ARippleSong\Settings\PlayerSetting;

/**
 * The meta bar of single episode pages: play button, favorite button and play count.
 *
 * Added to the episode content (before or after it, setting "Episode Meta Bar"), so it works in
 * block and classic themes alike. It is drawn in the selected player skin's style, with the buttons of
 * that prototype's episode page: resources/templates/episode/meta/{skin}.php, styled by the skin stylesheet
 * and bound by player.js like the skin. So it only shows while the global player is on for the episode.
 */
final class EpisodeMeta
{
    private PodcastCPT $podcast;

    private PlayerSetting $setting;

    public function __construct(private readonly GlobalPlayer $player)
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
        // After wpautop (10) and shortcodes (11), so the markup is left alone.
        add_filter('the_content', [$this, 'addToContent'], 20);
    }

    /**
     * Put the meta bar before or after the content of the episode being viewed.
     *
     * @param string $content Post content.
     * @return string
     */
    public function addToContent(string $content): string
    {
        // Only the viewed episode's own content: not excerpts (they run the_content too), widgets or other posts.
        if (! in_the_loop() || ! is_main_query() || get_the_ID() !== get_queried_object_id() || doing_filter('get_the_excerpt') || ! $this->isEnabled()) {
            return $content;
        }

        $position = $this->setting->episodeMetaPosition();
        $meta = $this->render((int) get_the_ID(), $position);

        return $position === 'after' ? $content . $meta : $meta . $content;
    }

    /**
     * Return the meta bar markup of an episode.
     *
     * @param int $postId Episode ID.
     * @param string $position Where it is placed: before or after the content.
     * @return string
     */
    public function render(int $postId, string $position = 'before'): string
    {
        $count = $this->podcast->playCount($postId);

        ob_start();
        (new Template())->renderThemeable('episode/meta/' . $this->setting->skin()->value, [
            'postId' => $postId,
            'position' => $position,
            /* translators: %s: number of plays. */
            'playsText' => sprintf(_n('%s play', '%s plays', $count, 'a-ripple-song'), number_format_i18n($count)),
            'playsAttributes' => Html::attributes([
                'data-ars-plays' => (string) $postId,
                /* translators: %s: number of plays. */
                'data-ars-plays-one' => _n('%s play', '%s plays', 1, 'a-ripple-song'),
                /* translators: %s: number of plays. */
                'data-ars-plays-other' => _n('%s play', '%s plays', 2, 'a-ripple-song'),
            ]),
        ]);

        return (string) ob_get_clean();
    }

    /**
     * Whether the meta bar shows on the current request.
     *
     * Play and favorite are handled by the global player, which only queues episodes with audio.
     * (Not wp_script_is(): block themes render the content before wp_head enqueues the player.)
     *
     * @return bool
     */
    private function isEnabled(): bool
    {
        $enabled = is_singular($this->podcast->slug())
            && $this->setting->episodeMetaPosition() !== 'hidden'
            && $this->player->isEnabled()
            && (string) get_post_meta(get_queried_object_id(), $this->podcast->fieldMetaKey('audio_file'), true) !== '';

        /**
         * Filters whether the episode meta bar is added to the current episode page.
         *
         * @param bool $enabled Whether it is shown according to the player settings.
         */
        return (bool) apply_filters(Plugin::PREFIX . 'episode_meta_enabled', $enabled);
    }
}
