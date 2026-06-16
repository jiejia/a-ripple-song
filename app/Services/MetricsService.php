<?php

namespace App\Services;

use App\CustomPostTypes\Episode;
use App\Metas\PostViewCount;

/**
 * Handles post metric reads and writes.
 */
class MetricsService
{
    /**
     * Increment the view count for a readable post.
     *
     * @param int $postId Requested post ID.
     * @return array{success:bool,status:int,message?:string,count?:int}
     */
    public function incrementViewCount(int $postId): array
    {
        $post = $postId > 0 ? get_post($postId) : null;

        if (! $post instanceof \WP_Post || ! $this->canReadMetricPost($post)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Invalid post ID.',
            ];
        }

        $count = max(0, (int) get_post_meta($postId, $this->viewCountMetaKey(), true)) + 1;
        update_post_meta($postId, $this->viewCountMetaKey(), $count);

        return [
            'success' => true,
            'status' => 200,
            'count' => $count,
        ];
    }

    /**
     * Increment the play count for a readable podcast episode.
     *
     * @param int $postId Requested post ID.
     * @return array{success:bool,status:int,message?:string,count?:int}
     */
    public function incrementPlayCount(int $postId): array
    {
        $post = $postId > 0 ? get_post($postId) : null;

        if (! $post instanceof \WP_Post || $post->post_type !== Episode::slug() || ! $this->canReadMetricPost($post)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Invalid podcast episode post.',
            ];
        }

        $count = max(0, (int) get_post_meta($postId, $this->playCountMetaKey(), true)) + 1;
        update_post_meta($postId, $this->playCountMetaKey(), $count);

        return [
            'success' => true,
            'status' => 200,
            'count' => $count,
        ];
    }

    /**
     * Fetch current metrics for readable posts.
     *
     * @param array<int,int> $postIds Requested post IDs.
     * @return array{success:bool,status:int,message?:string,counts?:array<int,array{views:int,plays:int|null,postType:string}>}
     */
    public function getMetrics(array $postIds): array
    {
        $postIds = array_values(array_filter(array_map('absint', $postIds)));

        if ($postIds === []) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'No post IDs provided.',
            ];
        }

        $data = [];

        foreach ($postIds as $postId) {
            $post = get_post($postId);

            if (! $post instanceof \WP_Post || ! $this->canReadMetricPost($post)) {
                continue;
            }

            $views = max(0, (int) get_post_meta($postId, $this->viewCountMetaKey(), true));
            $plays = $post->post_type === Episode::slug()
                ? max(0, (int) get_post_meta($postId, $this->playCountMetaKey(), true))
                : null;

            $data[$postId] = [
                'views' => $views,
                'plays' => $plays,
                'postType' => $post->post_type,
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'counts' => $data,
        ];
    }

    /**
     * Check whether a post can be read for metric tracking purposes.
     *
     * @param \WP_Post $post The target post object.
     * @return bool
     */
    private function canReadMetricPost(\WP_Post $post): bool
    {
        if ($post->post_status === 'publish') {
            return true;
        }

        return current_user_can('read_post', $post->ID);
    }

    /**
     * Return the stored view count meta key.
     *
     * @return string
     */
    private function viewCountMetaKey(): string
    {
        return PostViewCount::storedFieldKey('views_count');
    }

    /**
     * Return the stored episode play count meta key.
     *
     * @return string
     */
    private function playCountMetaKey(): string
    {
        return Episode::storedFieldKey('play_count');
    }
}
