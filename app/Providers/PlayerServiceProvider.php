<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Player\EpisodeMeta;
use Jiejia\ARippleSong\Player\GlobalPlayer;

/**
 * Registers the front-end global player and the episode page meta bar.
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
        $player = new GlobalPlayer();
        $player->register();
        (new EpisodeMeta($player))->register();
    }
}
