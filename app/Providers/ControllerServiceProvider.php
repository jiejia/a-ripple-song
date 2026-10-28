<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ControllerAbstract;
use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Controllers\PodcastPlayController;

/**
 * Registers plugin REST API controllers.
 */
class ControllerServiceProvider extends ServiceProviderAbstract
{
    /**
     * Controller classes registered by this provider.
     *
     * @var array<int,class-string<ControllerAbstract>>
     */
    private array $controllers = [
        PodcastPlayController::class,
    ];

    /**
     * Register REST routes when the REST API initializes.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register the routes of all configured controllers.
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        foreach ($this->controllers as $controllerClass) {
            (new $controllerClass())->registerRoutes();
        }
    }
}
