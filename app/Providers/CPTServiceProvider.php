<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Contracts\CPT;
use Jiejia\ARippleSong\CPTs\PodcastCPT;
use Jiejia\ARippleSong\Libraries\Fields\PostMetaStore;

/**
 * Registers plugin custom post types.
 */
class CPTServiceProvider extends ServiceProviderAbstract
{
    /**
     * Custom post type classes registered by this provider.
     *
     * @var array<int,class-string<CPT>>
     */
    private array $postTypes = [
        PodcastCPT::class,
    ];

    /**
     * Register custom post type hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Register custom post types during WordPress initialization.
        add_action('init', [$this, 'registerPostTypes']);

        $this->registerPostTypeFields();

        foreach ($this->postTypes as $postTypeClass) {
            // Allow each custom post type to attach its own supporting hooks.
            (new $postTypeClass())->registerHooks();
        }
    }

    /**
     * Register all configured custom post types.
     *
     * @return void
     */
    public function registerPostTypes(): void
    {
        foreach ($this->postTypes as $postTypeClass) {
            // Build a WordPress custom post type from the configured contract.
            $postType = new $postTypeClass();

            register_post_type($postType->slug(), $postType->args());
        }
    }

    /**
     * Register field meta boxes for all configured custom post types.
     *
     * @return void
     */
    public function registerPostTypeFields(): void
    {
        foreach ($this->postTypes as $postTypeClass) {
            // The store skips the meta box itself when the post type exposes no fields.
            (new PostMetaStore(new $postTypeClass()))->register();
        }
    }
}
