<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\TaxonomyAbstract;

/**
 * Shows a custom taxonomy's fields on the add / edit term screens and stores them as term meta.
 *
 * Meta keys come from TaxonomyAbstract::fieldMetaKey(), e.g. _ars_podcast_category_image.
 */
final class TermMetaStore extends FieldStore
{
    /**
     * @param TaxonomyAbstract $taxonomy Custom taxonomy whose fields() are stored.
     */
    public function __construct(private readonly TaxonomyAbstract $taxonomy)
    {
    }

    /**
     * Register form, save and asset hooks.
     *
     * @return void
     */
    public function register(): void
    {
        $slug = $this->taxonomy->slug();

        add_action($slug . '_add_form_fields', [$this, 'renderAddForm']);
        add_action($slug . '_edit_form_fields', [$this, 'renderEditForm']);
        add_action('created_' . $slug, [$this, 'save']);
        add_action('edited_' . $slug, [$this, 'save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Render the fields below the core inputs of the "Add New" form.
     *
     * @return void
     */
    public function renderAddForm(): void
    {
        if ($this->fields() === []) {
            return;
        }

        echo '<div class="ars-term-fields">';
        wp_nonce_field($this->nonceAction(), $this->nonceName());
        $this->renderFields([]);
        echo '</div>';
    }

    /**
     * Render the fields as one row of the edit term table.
     *
     * @param \WP_Term $term Term being edited.
     * @return void
     */
    public function renderEditForm(\WP_Term $term): void
    {
        if ($this->fields() === []) {
            return;
        }

        echo '<tr class="ars-term-fields"><td colspan="2">';
        wp_nonce_field($this->nonceAction(), $this->nonceName());
        $this->renderFields($this->values($term->term_id));
        echo '</td></tr>';
    }

    /**
     * Read stored values; fields never saved are left out so the renderer uses their defaults.
     *
     * @param int $termId Term ID.
     * @return array<string,mixed>
     */
    public function values(int $termId): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $metaKey = $this->taxonomy->fieldMetaKey($field->key);

            if ($field->hasValue() && metadata_exists('term', $termId, $metaKey)) {
                $values[$field->key] = get_term_meta($termId, $metaKey, true);
            }
        }

        return $values;
    }

    /**
     * Save submitted field values.
     *
     * @param int $termId Term ID.
     * @return void
     */
    public function save(int $termId): void
    {
        if (! $this->canSave($termId)) {
            return;
        }

        $submission = $this->submission();

        foreach ($submission->values as $key => $value) {
            // The add form is sent by AJAX, past browser validation, so keep the valid values instead of dropping all.
            if (isset($submission->errors[$key])) {
                continue;
            }

            // update_term_meta() unslashes its value, so slash it back first.
            update_term_meta($termId, $this->taxonomy->fieldMetaKey($key), wp_slash($value));
        }
    }

    /**
     * Whether the request comes from our form and the user may edit this term.
     *
     * @param int $termId Term ID.
     * @return bool
     */
    private function canSave(int $termId): bool
    {
        $nonce = isset($_POST[$this->nonceName()]) ? sanitize_text_field(wp_unslash($_POST[$this->nonceName()])) : '';

        return $nonce !== ''
            && wp_verify_nonce($nonce, $this->nonceAction())
            && current_user_can('edit_term', $termId);
    }

    /**
     * {@inheritDoc}
     */
    protected function formName(): string
    {
        return $this->taxonomy->slug();
    }

    /**
     * {@inheritDoc}
     */
    protected function definitions(): array
    {
        return $this->taxonomy->fields();
    }

    /**
     * Only this taxonomy's term list (with the add form) and edit screens show the fields.
     *
     * {@inheritDoc}
     */
    protected function isFormScreen(string $hookSuffix): bool
    {
        if (! in_array($hookSuffix, ['edit-tags.php', 'term.php'], true)) {
            return false;
        }

        $screen = get_current_screen();

        return $screen !== null && $screen->taxonomy === $this->taxonomy->slug();
    }

    /**
     * Return the nonce action of this taxonomy's fields.
     *
     * @return string
     */
    private function nonceAction(): string
    {
        return $this->taxonomy->slug() . '_save_fields';
    }

    /**
     * Return the nonce field name of this taxonomy's fields.
     *
     * @return string
     */
    private function nonceName(): string
    {
        return $this->taxonomy->slug() . '_fields_nonce';
    }
}
