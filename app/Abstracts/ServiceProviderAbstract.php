<?php

namespace Jiejia\ARippleSong\Abstracts;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Contracts\ServiceProviderContract;

/**
 * Base service provider for plugin feature registration.
 */
abstract class ServiceProviderAbstract implements ServiceProviderContract
{
    /**
     * Register WordPress hooks, services, or plugin features.
     *
     * @return void
     */
    public function register(): void
    {
        // Child providers may override this method when registration is needed.
    }

    /**
     * Boot logic that should run after all providers are registered.
     *
     * @return void
     */
    public function boot(): void
    {
        // Child providers may override this method when boot logic is needed.
    }
}