<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Sanitizes submitted form data according to field definitions.
 */
final class FieldSanitizer
{
    /**
     * Sanitize submitted values for every savable field.
     *
     * Missing keys are treated as empty, so an unchecked multiselect or a
     * repeater with all rows removed is saved as an empty list, and a switch as "no".
     *
     * @param array<int,Field> $fields Fields to sanitize.
     * @param array<string,mixed> $input Unslashed submitted values keyed by field key.
     * @return array<string,mixed>
     */
    public function sanitize(array $fields, array $input): array
    {
        $values = [];

        foreach ($fields as $field) {
            if ($field->isSavable()) {
                $values[$field->key] = $this->sanitizeValue($field, $input[$field->key] ?? null);
            }
        }

        return $values;
    }

    /**
     * Sanitize one submitted value.
     *
     * @param Field $field Field definition.
     * @param mixed $value Submitted value.
     * @return mixed
     */
    public function sanitizeValue(Field $field, mixed $value): mixed
    {
        if ($field->sanitize !== null) {
            return ($field->sanitize)($value, $field);
        }

        return match ($field->type) {
            FieldType::Number => $this->sanitizeNumber($field, $value),
            FieldType::Url, FieldType::File, FieldType::Image => $this->string($value, static fn (string $url): string => esc_url_raw(trim($url))),
            FieldType::Email => $this->string($value, sanitize_email(...)),
            FieldType::Textarea => $this->string($value, sanitize_textarea_field(...)),
            FieldType::Switch => $value === 'yes' ? 'yes' : 'no',
            FieldType::Select => $this->matchOptions($field, [$value])[0] ?? $field->defaultValue(),
            FieldType::Multiselect => $this->matchOptions($field, is_array($value) ? $value : []),
            FieldType::Complex => $this->sanitizeRows($field, is_array($value) ? $value : []),
            default => $this->string($value, sanitize_text_field(...)),
        };
    }

    /**
     * Run a string sanitizer; anything that is not a string becomes empty.
     *
     * @param mixed $value Submitted value.
     * @param callable(string):string $sanitizer String sanitizer.
     * @return string
     */
    private function string(mixed $value, callable $sanitizer): string
    {
        return is_string($value) ? $sanitizer($value) : '';
    }

    /**
     * Sanitize a number honoring the step, min and max attributes.
     *
     * @param Field $field Field definition.
     * @param mixed $value Submitted value.
     * @return int|float|string Empty string when no valid number was given.
     */
    private function sanitizeNumber(Field $field, mixed $value): int|float|string
    {
        if (! is_scalar($value) || ! is_numeric(trim((string) $value))) {
            return '';
        }

        $attributes = $field->attributes;
        $step = (string) ($attributes['step'] ?? '1');
        $number = (float) $value;

        if (isset($attributes['min'])) {
            $number = max($number, (float) $attributes['min']);
        }

        if (isset($attributes['max'])) {
            $number = min($number, (float) $attributes['max']);
        }

        // Integer steps store integers, fractional steps store floats.
        return str_contains($step, '.') || $step === 'any' ? $number : (int) round($number);
    }

    /**
     * Keep only submitted values that exist in the option list, preserving the option key type.
     *
     * @param Field $field Field definition.
     * @param array<int|string,mixed> $submitted Submitted values.
     * @return array<int,int|string>
     */
    private function matchOptions(Field $field, array $submitted): array
    {
        $keys = array_keys($field->options());
        $lookup = array_combine(array_map('strval', $keys), $keys);
        $matched = [];

        foreach ($submitted as $value) {
            if (is_scalar($value) && array_key_exists((string) $value, $lookup)) {
                $matched[] = $lookup[(string) $value];
            }
        }

        return array_values(array_unique($matched, SORT_REGULAR));
    }

    /**
     * Sanitize repeater rows, dropping rows where every sub field is empty.
     *
     * @param Field $field Complex field definition.
     * @param array<int|string,mixed> $rows Submitted rows.
     * @return array<int,array<string,mixed>>
     */
    private function sanitizeRows(Field $field, array $rows): array
    {
        $clean = [];

        foreach (array_filter($rows, 'is_array') as $row) {
            $values = $this->sanitize($field->fields, $row);

            if (array_filter($values, static fn (mixed $value): bool => $value !== '' && $value !== []) !== []) {
                $clean[] = $values;
            }
        }

        return $clean;
    }
}
