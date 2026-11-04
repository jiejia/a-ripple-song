<?php

namespace Jiejia\ARippleSong\Player;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Settings\PlayerSetting;

/**
 * Matches the "Player Style" setting to the active theme.
 *
 * Skins share their slug with the theme they were made for (ars-petal, ars-coral …). When such a theme
 * is activated, or the plugin is activated while it is the active theme, the setting switches to that
 * skin. A theme without a matching skin leaves the setting alone.
 */
class ThemeSkin
{
    /**
     * Register the theme switch hook.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('after_switch_theme', [self::class, 'sync']);
    }

    /**
     * Select the skin named like the active theme (its stylesheet, i.e. the child theme when there is one).
     *
     * @return void
     */
    public static function sync(): void
    {
        $skin = PlayerSkin::tryFrom(get_stylesheet()) ?? PlayerSkin::tryFrom(get_template());
        if ($skin === null) {
            return;
        }

        $option = (new PlayerSetting())->slug();
        $values = get_option($option, []);
        $values = is_array($values) ? $values : [];
        if (($values['skin'] ?? '') === $skin->value) {
            return;
        }

        $values['skin'] = $skin->value;
        update_option($option, $values);
    }
}
