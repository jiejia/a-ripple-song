<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Player\GlobalPlayer;

/**
 * Registers the front-end global player.
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
        (new GlobalPlayer())->register();
    }
}
