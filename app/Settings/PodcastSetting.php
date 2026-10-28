<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
/**
 * Carbon Fields podcast settings page.
 */
class PodcastSetting extends SettingAbstract
{
    public function isTopLevelMenu(): bool
    {
        return false;
    }

    public function slug(): string
    {
        return 'podcast_settings';
    }

    public function title(): string
    {
        return __('Podcast Settings', 'a-ripple-song');
    }

    public function fields(): array
    {
        return [];
    }
}
