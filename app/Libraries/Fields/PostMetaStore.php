<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\CPTAbstract;
use Jiejia\ARippleSong\Plugin;

/**
 * Shows a custom post type's fields in a meta box and stores them as post meta.
 *
 * Meta keys come from CPTAbstract::fieldMetaKey(), e.g. _ars_podcast_audio_file.
 * The leading underscore keeps them out of the core Custom Fields box.
 */
final class PostMetaStore extends FieldStore
{
    /**
     * @param CPTAbstract $postType Custom post type whose fields() are stored.
     */
    public function __construct(private readonly CPTAbstract $postType)
    {
    }

    /**
     * Register meta box, save and asset hooks.
     *
     * @return void
     */
    public function register(): void
    {
        $slug = $this->postType->slug();

        add_action('add_meta_boxes_' . $slug, [$this, 'addMetaBox']);
        add_action('save_post_' . $slug, [$this, 'save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Return the stored post meta key of a field.
     *
     * @param string $key Field key.
     * @return string
     */
    public function metaKey(string $key): string
    {
        return $this->postType->fieldMetaKey($key);
    }

    /**
     * Add the fields meta box to the post editor.
     *
     * @return void
     */
    public function addMetaBox(): void
    {
        if ($this->fields() === []) {
            return;
        }

        add_meta_box(
            $this->postType->slug() . '_fields',
            /* translators: %s is the singular custom post type name. */
            sprintf(__('%s Details', 'a-ripple-song'), $this->postType->singularName()),
            [$this, 'renderMetaBox'],
            $this->postType->slug(),
            'normal',
            'high'
        );
    }

    /**
     * Render the meta box content.
     *
     * @param \WP_Post $post Current post.
     * @return void
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        wp_nonce_field($this->nonceAction(), $this->nonceName());

        $this->renderFields($this->values($post->ID));
    }

    /**
     * Read stored values; fields never saved are left out so the renderer uses their defaults.
     *
     * @param int $postId Post ID.
     * @return array<string,mixed>
     */
    public function values(int $postId): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $metaKey = $this->metaKey($field->key);

            if ($field->hasValue() && metadata_exists('post', $postId, $metaKey)) {
                $values[$field->key] = get_post_meta($postId, $metaKey, true);
            }
        }

        return $values;
    }

    /**
     * Save submitted field values.
     *
     * @param int $postId Post ID.
     * @return void
     */
    public function save(int $postId): void
    {
        if (! $this->canSave($postId)) {
            return;
        }

        $submission = $this->submission();

        // The editor locks saving while required fields are empty; this guards requests that bypass it.
        if (! $submission->isValid()) {
            return;
        }

        foreach ($submission->values as $key => $value) {
            // update_post_meta() unslashes its value, so slash it back first.
            update_post_meta($postId, $this->metaKey($key), wp_slash($value));
        }

        /**
         * Fires after a custom post type's fields have been saved.
         *
         * @param int $postId Post ID.
         * @param CPTAbstract $postType Custom post type definition.
         * @param array<string,mixed> $values Saved values keyed by field key.
         */
        do_action(Plugin::PREFIX . 'post_fields_saved', $postId, $this->postType, $submission->values);
    }

    /**
     * Whether the request comes from our meta box and the user may save this post.
     *
     * @param int $postId Post ID.
     * @return bool
     */
    private function canSave(int $postId): bool
    {
        $nonce = isset($_POST[$this->nonceName()]) ? sanitize_text_field(wp_unslash($_POST[$this->nonceName()])) : '';

        return $nonce !== ''
            && wp_verify_nonce($nonce, $this->nonceAction())
            && ! (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            && current_user_can('edit_post', $postId);
    }

    /**
     * {@inheritDoc}
     */
    protected function formName(): string
    {
        return $this->postType->slug();
    }

    /**
     * {@inheritDoc}
     */
    protected function definitions(): array
    {
        return $this->postType->fields();
    }

    /**
     * Only this post type's editor screens show the meta box.
     *
     * {@inheritDoc}
     */
    protected function isFormScreen(string $hookSuffix): bool
    {
        if (! in_array($hookSuffix, ['post.php', 'post-new.php'], true)) {
            return false;
        }

        $screen = get_current_screen();

        return $screen !== null && $screen->post_type === $this->postType->slug();
    }

    /**
     * Return the nonce action of this post type's meta box.
     *
     * @return string
     */
    private function nonceAction(): string
    {
        return $this->postType->slug() . '_save_fields';
    }

    /**
     * Return the nonce field name of this post type's meta box.
     *
     * @return string
     */
    private function nonceName(): string
    {
        return $this->postType->slug() . '_fields_nonce';
    }
}
