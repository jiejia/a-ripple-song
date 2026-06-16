<?php

namespace App\Abstracts;

use App\Contracts\FeedInterface;

/**
 * Provides shared behavior for theme feed endpoints.
 */
abstract class FeedAbstract implements FeedInterface
{
    /**
     * Register the feed endpoint with WordPress.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerFeed'], 20);
    }

    /**
     * Register the concrete feed renderer.
     *
     * @return void
     */
    public function registerFeed(): void
    {
        add_feed($this->slug(), [$this, 'renderFeed']);
    }
}
