<?php

namespace App\Providers;

use App\Controllers\MetricsController;
use App\Theme;
use Illuminate\Support\ServiceProvider;

/**
 * Registers theme REST API routes.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register REST API route hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
    }

    /**
     * Register all theme REST API routes.
     *
     * @return void
     */
    public function registerRestRoutes(): void
    {
        $metricsController = new MetricsController();

        $this->post('/metrics/views', [$metricsController, 'incrementViewCount'], $this->postIdArgs());
        $this->post('/metrics/plays', [$metricsController, 'incrementPlayCount'], $this->postIdArgs());
        $this->get('/metrics', [$metricsController, 'getMetrics'], $this->postIdsArgs());
    }

    /**
     * Register a readable REST route.
     *
     * @param string $route REST route path.
     * @param callable $callback Route callback.
     * @param array<string,mixed> $args Route arguments.
     * @return void
     */
    private function get(string $route, callable $callback, array $args = []): void
    {
        $this->route(\WP_REST_Server::READABLE, $route, $callback, $args);
    }

    /**
     * Register a creatable REST route.
     *
     * @param string $route REST route path.
     * @param callable $callback Route callback.
     * @param array<string,mixed> $args Route arguments.
     * @return void
     */
    private function post(string $route, callable $callback, array $args = []): void
    {
        $this->route(\WP_REST_Server::CREATABLE, $route, $callback, $args);
    }

    /**
     * Register a REST route with shared defaults.
     *
     * @param string $method REST method.
     * @param string $route REST route path.
     * @param callable $callback Route callback.
     * @param array<string,mixed> $args Route arguments.
     * @return void
     */
    private function route(string $method, string $route, callable $callback, array $args = []): void
    {
        register_rest_route($this->namespace(), $route, [
            'methods' => $method,
            'callback' => $callback,
            'permission_callback' => '__return_true',
            'args' => $args,
        ]);
    }

    /**
     * Return the shared single post ID route arguments.
     *
     * @return array<string,array<string,mixed>>
     */
    private function postIdArgs(): array
    {
        return [
            'post_id' => [
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'minimum' => 1,
            ],
        ];
    }

    /**
     * Return the multiple post IDs route arguments.
     *
     * @return array<string,array<string,mixed>>
     */
    private function postIdsArgs(): array
    {
        return [
            'post_ids' => [
                'required' => true,
                'type' => 'array',
                'items' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
            ],
        ];
    }

    /**
     * Return the REST API namespace.
     *
     * @return string
     */
    private function namespace(): string
    {
        return Theme::PREFIX . '/v1';
    }
}
