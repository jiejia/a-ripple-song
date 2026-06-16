<?php

namespace App\Providers;

use App\Contracts\FeedInterface;
use App\Feeds\Podcast;
use Illuminate\Support\ServiceProvider;

/**
 * Registers theme feed endpoints.
 */
class FeedServiceProvider extends ServiceProvider
{
    /**
     * Feed classes registered by this provider.
     *
     * @var array<int,class-string<FeedInterface>>
     */
    private array $feeds = [Podcast::class];

    /**
     * Register feed hooks.
     *
     * @return void
     */
    public function register(): void
    {
        foreach ($this->feeds as $feedClass) {
            // Let each feed attach its endpoint and supporting routing hooks.
            (new $feedClass())->registerHooks();
        }
    }
}
