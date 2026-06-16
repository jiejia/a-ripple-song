<?php

namespace App\Contracts;

/**
 * Describes a theme feed endpoint.
 */
interface FeedInterface
{
    /**
     * Return the WordPress feed slug.
     *
     * @return string
     */
    public function slug(): string;

    /**
     * Register WordPress hooks needed by this feed.
     *
     * @return void
     */
    public function registerHooks(): void;

    /**
     * Render the feed response.
     *
     * @return void
     */
    public function renderFeed(): void;
}
