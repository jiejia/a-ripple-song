<?php

namespace Jiejia\ARippleSong\Abstracts;

defined('ABSPATH') || exit;

/**
 * Base class for feed endpoints, e.g. /feed/{slug}/.
 */
abstract class FeedAbstract
{
    /**
     * Return the WordPress feed slug.
     *
     * @return string
     */
    abstract public function slug(): string;

    /**
     * Print the feed; called by do_feed_{slug}.
     *
     * @return void
     */
    abstract public function render(): void;

    /**
     * Register the hooks of this feed.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerFeed']);
    }

    /**
     * Register the feed endpoint with WordPress.
     *
     * @return void
     */
    public function registerFeed(): void
    {
        add_feed($this->slug(), [$this, 'render']);
    }
}
