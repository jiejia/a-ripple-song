<?php

namespace Jiejia\ARippleSong\Contracts;

defined('ABSPATH') || exit;

/**
 * Defines the lifecycle methods for plugin service providers.
 */
interface ServiceProviderContract
{
    /**
     * Register WordPress hooks, services, or plugin features.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Boot logic that should run after all providers are registered.
     *
     * @return void
     */
    public function boot(): void;
}