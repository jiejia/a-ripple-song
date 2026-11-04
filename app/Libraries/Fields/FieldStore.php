<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Base class of the places fields are stored (post meta, options).
 *
 * Subclasses say where their form lives and how values are persisted; this
 * class supplies the shared steps: building fields, enqueueing assets,
 * rendering and reading a submission.
 */
abstract class FieldStore
{
    /**
     * Fields built from definitions() on first use.
     *
     * @var array<int,Field>|null
     */
    private ?array $fields = null;

    /**
     * Register the WordPress hooks of this store.
     *
     * @return void
     */
    abstract public function register(): void;

    /**
     * Return the form name all fields are nested under; also the POST key.
     *
     * @return string
     */
    abstract protected function formName(): string;

    /**
     * Return the raw field definitions.
     *
     * @return array<int,array<string,mixed>|Field>
     */
    abstract protected function definitions(): array;

    /**
     * Whether the current admin screen shows this store's form.
     *
     * @param string $hookSuffix Current admin page.
     * @return bool
     */
    abstract protected function isFormScreen(string $hookSuffix): bool;

    /**
     * Return the fields of this store.
     *
     * @return array<int,Field>
     */
    public function fields(): array
    {
        return $this->fields ??= Field::collect($this->definitions());
    }

    /**
     * Enqueue field assets on the screens that show the form.
     *
     * @param string $hookSuffix Current admin page.
     * @return void
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        if ($this->isFormScreen($hookSuffix)) {
            FieldAssets::enqueue();
        }
    }

    /**
     * Render the fields.
     *
     * @param array<string,mixed> $values Values keyed by field key; missing keys fall back to defaults.
     * @param array<string,string> $errors Validation errors.
     * @return void
     */
    protected function renderFields(array $values, array $errors = []): void
    {
        (new FieldRenderer($this->formName(), $errors))->render($this->fields(), $values);
    }

    /**
     * Sanitize and validate the submitted form.
     *
     * @return FieldSubmission
     */
    protected function submission(): FieldSubmission
    {
        return FieldSubmission::fromRequest($this->formName(), $this->fields());
    }
}
