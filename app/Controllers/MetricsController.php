<?php

namespace App\Controllers;

use App\Services\MetricsService;

/**
 * REST API controller for post metric tracking.
 */
class MetricsController
{
    /**
     * Metrics service instance.
     *
     * @var MetricsService
     */
    private MetricsService $metrics;

    /**
     * Create the controller.
     *
     * @param MetricsService|null $metrics Metrics service instance.
     */
    public function __construct(?MetricsService $metrics = null)
    {
        $this->metrics = $metrics ?? new MetricsService();
    }

    /**
     * Increment the view count for a readable post.
     *
     * @param \WP_REST_Request $request REST request object.
     * @return \WP_REST_Response
     */
    public function incrementViewCount(\WP_REST_Request $request): \WP_REST_Response
    {
        $result = $this->metrics->incrementViewCount((int) $request->get_param('post_id'));

        return $this->response($result);
    }

    /**
     * Increment the play count for a readable podcast episode.
     *
     * @param \WP_REST_Request $request REST request object.
     * @return \WP_REST_Response
     */
    public function incrementPlayCount(\WP_REST_Request $request): \WP_REST_Response
    {
        $result = $this->metrics->incrementPlayCount((int) $request->get_param('post_id'));

        return $this->response($result);
    }

    /**
     * Fetch the current metrics for a list of readable posts.
     *
     * @param \WP_REST_Request $request REST request object.
     * @return \WP_REST_Response
     */
    public function getMetrics(\WP_REST_Request $request): \WP_REST_Response
    {
        $result = $this->metrics->getMetrics((array) ($request->get_param('post_ids') ?? []));

        return $this->response($result);
    }

    /**
     * Convert a service result to a REST response.
     *
     * @param array<string,mixed> $result Service result.
     * @return \WP_REST_Response
     */
    private function response(array $result): \WP_REST_Response
    {
        $status = isset($result['status']) ? (int) $result['status'] : 200;
        unset($result['success'], $result['status']);

        return new \WP_REST_Response($result, $status);
    }
}
