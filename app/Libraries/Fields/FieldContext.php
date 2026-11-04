<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Html;

/**
 * One field ready to render: its definition, current value, input name and error.
 *
 * Templates receive it as $control.
 */
final readonly class FieldContext
{
    /**
     * Control ID derived from the input name.
     */
    public string $id;

    /**
     * @param Field $field Field definition.
     * @param mixed $value Current value.
     * @param string $name Input name, e.g. ars_podcast[episode_soundbites][0][title].
     * @param string $error Server-side validation error.
     */
    public function __construct(
        public Field $field,
        public mixed $value,
        public string $name,
        public string $error = '',
    ) {
        $this->id = 'ars-' . trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    }

    /**
     * Cast the value to a string suitable for a single control.
     *
     * @return string
     */
    public function scalar(): string
    {
        return self::toScalar($this->value);
    }

    /**
     * Return the value as a list; non-arrays become empty.
     *
     * @return array<int|string,mixed>
     */
    public function list(): array
    {
        return is_array($this->value) ? $this->value : [];
    }

    /**
     * Return the wrapper classes of the field.
     *
     * @return string
     */
    public function classes(): string
    {
        return Html::classes([
            'ars-field',
            'ars-field--' . $this->field->type->value,
            'ars-field--w-' . $this->field->width->value,
            'is-required' => $this->field->required,
            'is-readonly' => $this->field->readonly,
            'is-invalid' => $this->error !== '',
        ]);
    }

    /**
     * Build the escaped attribute string shared by input, textarea and select controls.
     *
     * @param array<string,mixed> $extra Control specific attributes; null removes a default one.
     * @return string
     */
    public function attributes(array $extra = []): string
    {
        $field = $this->field;

        return Html::attributes(array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'class' => 'ars-input',
            'data-ars-key' => $field->key,
            'placeholder' => $field->placeholder !== '' ? $field->placeholder : null,
            'aria-describedby' => $field->help !== '' ? $this->id . '-help' : null,
            'required' => $field->required,
            'readonly' => $field->readonly,
        ], $extra, $field->attributes));
    }

    /**
     * Cast a stored value to a string suitable for a single control.
     *
     * @param mixed $value Stored value.
     * @return string
     */
    public static function toScalar(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
