<?php

namespace App\Providers;

defined('ABSPATH') || exit;

use App\Abstracts\FeedAbstract;
use App\Abstracts\ServiceProviderAbstract;
use App\Feeds\PodcastFeed;

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
