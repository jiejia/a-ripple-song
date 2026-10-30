<?php

namespace Jiejia\ARippleSong\CPTs;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\CPTAbstract;
use Jiejia\ARippleSong\Libraries\Fields\FieldAssets;
use Jiejia\ARippleSong\Plugin;

/**
 * "Featured" switch column in a post type's list, saved through admin-ajax.
 *
 * Writes the same meta as the post type's "featured" switch field, without touching the post itself.
 */
final class FeaturedColumn
{
    private const COLUMN = 'ars_featured';

    private const ACTION = Plugin::PREFIX . 'toggle_featured';

    /**
     * @param CPTAbstract $postType Post type whose list gets the column.
     */
    public function __construct(private readonly CPTAbstract $postType)
    {
    }

    /**
     * Register column, Ajax and asset hooks.
     *
     * @return void
     */
    public function register(): void
    {
        $slug = $this->postType->slug();

        add_filter('manage_' . $slug . '_posts_columns', [$this, 'addColumn']);
        add_action('manage_' . $slug . '_posts_custom_column', [$this, 'renderColumn'], 10, 2);
        add_action('wp_ajax_' . self::ACTION, [$this, 'toggle']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Add the column after the title.
     *
     * @param array<string,string> $columns Column labels keyed by column ID.
     * @return array<string,string>
     */
    public function addColumn(array $columns): array
    {
        $result = [];

        foreach ($columns as $id => $label) {
            $result[$id] = $label;

            if ($id === 'title') {
                $result[self::COLUMN] = __('Featured', 'a-ripple-song');
            }
        }

        return $result + [self::COLUMN => __('Featured', 'a-ripple-song')];
    }

    /**
     * Print the switch of one post.
     *
     * @param string $column Column ID.
     * @param int $postId Post ID.
     * @return void
     */
    public function renderColumn(string $column, int $postId): void
    {
        if ($column !== self::COLUMN) {
            return;
        }

        printf(
            '<span class="ars-fields ars-featured"><input type="checkbox" role="switch" class="ars-switch__input" data-post-id="%d" aria-label="%s"%s%s></span>',
            (int) $postId,
            /* translators: %s: post title. */
            esc_attr(sprintf(__('Featured: %s', 'a-ripple-song'), get_the_title($postId))),
            checked($this->isFeatured($postId), true, false),
            disabled(current_user_can('edit_post', $postId), false, false)
        );
    }

    /**
     * Save a switch change.
     *
     * @return void
     */
    public function toggle(): void
    {
        check_ajax_referer(self::ACTION);

        $postId = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        if ($postId === 0 || get_post_type($postId) !== $this->postType->slug() || ! current_user_can('edit_post', $postId)) {
            wp_send_json_error(null, 403);
        }

        $featured = isset($_POST['featured']) && sanitize_key(wp_unslash($_POST['featured'])) === 'yes';

        update_post_meta($postId, $this->postType->fieldMetaKey('featured'), $featured ? 'yes' : 'no');

        wp_send_json_success(['featured' => $this->isFeatured($postId)]);
    }

    /**
     * Enqueue the switch styles and script on this post type's list.
     *
     * @param string $hookSuffix Current admin page.
     * @return void
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        $screen = get_current_screen();

        if ($hookSuffix !== 'edit.php' || $screen === null || $screen->post_type !== $this->postType->slug()) {
            return;
        }

        FieldAssets::enqueueStyle();

        wp_enqueue_script(
            self::ACTION,
            A_RIPPLE_SONG_PLUGIN_URL . 'resources/assets/js/featured-column.js',
            ['jquery', 'wp-a11y'],
            Plugin::assetVersion('resources/assets/js/featured-column.js'),
            true
        );

        wp_localize_script(self::ACTION, 'arsFeaturedColumn', [
            'action' => self::ACTION,
            'nonce' => wp_create_nonce(self::ACTION),
            'on' => __('Featured.', 'a-ripple-song'),
            'off' => __('No longer featured.', 'a-ripple-song'),
            'error' => __('The change could not be saved.', 'a-ripple-song'),
        ]);
    }

    /**
     * Whether a post is featured.
     *
     * @param int $postId Post ID.
     * @return bool
     */
    private function isFeatured(int $postId): bool
    {
        return get_post_meta($postId, $this->postType->fieldMetaKey('featured'), true) === 'yes';
    }
}
