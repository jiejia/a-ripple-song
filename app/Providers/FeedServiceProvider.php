<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\FeedAbstract;
use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Feeds\PodcastFeed;

/**
 * Registers plugin feed endpoints.
 */
class FeedServiceProvider extends ServiceProviderAbstract
{
    /**
     * Feed classes registered by this provider.
     *
     * @var array<int,class-string<FeedAbstract>>
     */
    private array $feeds = [
        PodcastFeed::class,
    ];

    /**
     * Register feed hooks.
     *
     * @return void
     */
    public function register(): void
    {
        foreach ($this->feeds as $feedClass) {
            (new $feedClass())->registerHooks();
        }
    }
}
