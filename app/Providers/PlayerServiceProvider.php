<?php

namespace App\Providers;

defined('ABSPATH') || exit;

use App\Abstracts\ServiceProviderAbstract;
use App\Player\EpisodeMeta;
use App\Player\GlobalPlayer;

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
