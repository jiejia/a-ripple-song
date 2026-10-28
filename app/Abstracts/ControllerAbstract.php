<?php

namespace Jiejia\ARippleSong\Abstracts;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Plugin;

/**
 * Base class for REST API controllers.
 */
abstract class ControllerAbstract
{
    /**
     * REST namespace shared by all plugin routes, e.g. /wp-json/a-ripple-song/v1/...
     */
    public const NAMESPACE = Plugin::SLUG . '/v1';

    /**
     * Register this controller's REST routes; called on rest_api_init.
     *
     * @return void
     */
    abstract public function registerRoutes(): void;
}
