<?php

namespace App\Metas;

use App\Abstracts\MetaAbstract;

/**
 * Registers shared post view count meta for public post types.
 */
class PostViewCount extends MetaAbstract
{
    /**
     * Register WordPress hooks for view count meta.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerMetaFields']);
        add_action('save_post', [$this, 'ensureDefault'], 10, 2);
    }

    /**
     * Register view count meta for all public post types.
     *
     * @return void
     */
    public function registerMetaFields(): void
    {
        $postTypes = get_post_types(['public' => true], 'names');

        foreach ($postTypes as $postType) {
            register_post_meta($postType, self::storedFieldKey('views_count'), [
                'type' => 'integer',
                'single' => true,
                'default' => 0,
                'show_in_rest' => false,
                'auth_callback' => static function ($allowed, $metaKey, $postId): bool {
                    return current_user_can('edit_post', $postId);
                },
            ]);
        }
    }

    /**
     * Ensure a saved public post has a view count default.
     *
     * @param int $postId Post ID.
     * @param \WP_Post $post Saved post object.
     * @return void
     */
    public function ensureDefault(int $postId, \WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        $postTypeObject = get_post_type_object($post->post_type);

        if (! $postTypeObject || ! is_post_type_viewable($postTypeObject)) {
            return;
        }

        if (! metadata_exists('post', $postId, self::storedFieldKey('views_count'))) {
            update_post_meta($postId, self::storedFieldKey('views_count'), 0);
        }
    }
}
