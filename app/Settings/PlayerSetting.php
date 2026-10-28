<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Player\PlayerSkin;

/**
 * Global player settings page, stored in the ars_player_settings option.
 */
class PlayerSetting extends SettingAbstract
{
    public string $slug = 'player_settings';

    public function title(): string
    {
        return __('Player Settings', 'a-ripple-song');
    }

    /**
     * Return player setting field definitions.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'separator',
                'label' => __('Global Player', 'a-ripple-song'),
            ],
            [
                'type' => 'switch',
                'key' => 'enabled',
                'label' => __('Show Global Player', 'a-ripple-song'),
                'width' => 'half',
                'default' => 'yes',
                'help' => __('Shows a player bar at the bottom of every front-end page. With a theme that uses swup, playback continues across pages.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'idle',
                'label' => __('Before Anything Is Played', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => [
                    'latest' => __('Show the player with an episode loaded (paused)', 'a-ripple-song'),
                    'hidden' => __('Hide the player until a play button is used', 'a-ripple-song'),
                ],
                'default' => 'latest',
                'help' => __('For visitors without a saved position. The loaded episode is the one being viewed on an episode page, otherwise the latest. "Hide" needs play buttons on the page, which the theme has to provide.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'skin',
                'label' => __('Player Style', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => PlayerSkin::options(),
                'default' => PlayerSkin::Default->value,
                'help' => __('The look of the player bar and its full-screen "Now Playing" panel. Ignored when the theme styles the player itself.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'width',
                'label' => __('Player Width', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => [
                    'wide' => __("Match the theme's wide width", 'a-ripple-song'),
                    'content' => __("Match the theme's content width", 'a-ripple-song'),
                    'skin' => __("The style's own width", 'a-ripple-song'),
                    'full' => __('Full width', 'a-ripple-song'),
                ],
                'default' => 'wide',
                'help' => __('Theme widths are measured from the theme\'s WordPress layout (block themes and themes with theme.json), so the bar lines up with the header, footer or post content. Themes without it keep the style\'s own width.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'background',
                'label' => __('Player Bar Background', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => [
                    'translucent' => __('Translucent (frosted glass)', 'a-ripple-song'),
                    'solid' => __('Solid', 'a-ripple-song'),
                ],
                'default' => 'translucent',
                'help' => __('Translucent lets the page show through the bar, blurred. The Aurora style is always frosted.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'color_scheme',
                'label' => __('Player Colors', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => [
                    'auto' => __('Follow the site theme (auto detect)', 'a-ripple-song'),
                    'system' => __("Follow the visitor's system setting", 'a-ripple-song'),
                    'light' => __('Always light', 'a-ripple-song'),
                    'dark' => __('Always dark', 'a-ripple-song'),
                ],
                'default' => 'auto',
                'help' => __('Every style has a light and a dark variant. Auto detect reads the light / dark markers and the background color of the current theme, and follows its toggle. Ignored when the theme styles the player itself.', 'a-ripple-song'),
            ],
            [
                'type' => 'separator',
                'label' => __('Episode Page', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'episode_meta',
                'label' => __('Episode Meta Bar', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => [
                    'before' => __('Before the episode content', 'a-ripple-song'),
                    'after' => __('After the episode content', 'a-ripple-song'),
                    'hidden' => __('Do not show', 'a-ripple-song'),
                ],
                'default' => 'before',
                'help' => __('Play and favorite buttons plus the play count on single episode pages, in the player style. Shown while the global player is on and the episode has audio; favorites are kept in the visitor\'s browser.', 'a-ripple-song'),
            ],
        ];
    }

    /**
     * Return the selected player skin.
     *
     * @return PlayerSkin
     */
    public function skin(): PlayerSkin
    {
        return PlayerSkin::tryFrom((string) $this->value('skin')) ?? PlayerSkin::Default;
    }

    /**
     * Return the color mode for player.js: auto (follow the site), system (follow the OS), light or dark.
     *
     * @return string
     */
    public function colorScheme(): string
    {
        $scheme = (string) $this->value('color_scheme');

        return in_array($scheme, ['auto', 'system', 'light', 'dark'], true) ? $scheme : 'auto';
    }

    /**
     * Return how wide the player bar is: wide, content, skin or full.
     *
     * @return string
     */
    public function width(): string
    {
        $width = (string) $this->value('width');

        return in_array($width, ['wide', 'content', 'skin', 'full'], true) ? $width : 'wide';
    }

    /**
     * Return where the meta bar goes on episode pages: before, after (the content) or hidden.
     *
     * @return string
     */
    public function episodeMetaPosition(): string
    {
        $position = (string) $this->value('episode_meta');

        return in_array($position, ['before', 'after', 'hidden'], true) ? $position : 'before';
    }

    /**
     * Whether the player bar background is translucent (frosted glass) rather than solid.
     *
     * @return bool
     */
    public function isTranslucent(): bool
    {
        return $this->value('background') !== 'solid';
    }

    /**
     * Whether the player shows an episode before the visitor has played anything.
     *
     * @return bool
     */
    public function preloadsEpisode(): bool
    {
        return $this->value('idle') !== 'hidden';
    }

    /**
     * Whether the global player is switched on.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->value('enabled') === 'yes';
    }
}
