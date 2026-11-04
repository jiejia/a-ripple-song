<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Player\EpisodeMeta;
use Jiejia\ARippleSong\Player\GlobalPlayer;
use Jiejia\ARippleSong\Player\PlayerSkin;
use Jiejia\ARippleSong\Player\ThemeSkin;
use Jiejia\ARippleSong\Settings\PlayerSetting;

/**
 * Registers the front-end global player, the episode page meta bar, and the theme-matched skin.
 */
class PlayerServiceProvider extends ServiceProviderAbstract
{
    /**
     * Register player hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Skin values gained the ars- prefix; read older saved values as the renamed skin, so the
        // settings page shows the right choice and saving it stores the new value.
        add_filter('option_' . (new PlayerSetting())->slug(), static function (mixed $values): mixed {
            if (is_array($values) && isset($values['skin']) && is_string($values['skin'])) {
                $values['skin'] = PlayerSkin::fromStored($values['skin'])->value;
            }

            return $values;
        });

        (new ThemeSkin())->register();

        $player = new GlobalPlayer();
        $player->register();
        (new EpisodeMeta($player))->register();
    }
}
