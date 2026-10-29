<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Validates sanitized values against field definitions.
 */
final class FieldValidator
{
    /**
     * Return validation errors keyed by field key; an empty array means valid.
     *
     * Sub field errors of complex fields are keyed as "parent.rowIndex.child".
     *
     * @param array<int,Field> $fields Fields to validate.
     * @param array<string,mixed> $values Sanitized values keyed by field key.
     * @param array<string,mixed> $input Raw submitted values, used to tell "invalid" from "empty".
     * @return array<string,string>
     */
    public function validate(array $fields, array $values, array $input = []): array
    {
        $errors = [];

        foreach ($fields as $field) {
            if (! $field->isSavable()) {
                continue;
            }

            $value = $values[$field->key] ?? null;
            $error = $this->error($field, $value, $input[$field->key] ?? null);

            if ($error !== null) {
                $errors[$field->key] = $error;
            } elseif ($field->type === FieldType::Complex && is_array($value)) {
                $errors += $this->rowErrors($field, $value);
            }
        }

        return $errors;
    }

    /**
     * Return the first rule a value breaks, or null when it is valid.
     *
     * @param Field $field Field definition.
     * @param mixed $value Sanitized value.
     * @param mixed $raw Raw submitted value.
     * @return string|null
     */
    private function error(Field $field, mixed $value, mixed $raw): ?string
    {
        return match (true) {
            // sanitize_email() turns an invalid address into "", which would otherwise read as "required".
            $field->type === FieldType::Email && $this->isInvalidEmail($raw)
                /* translators: %s: field label. */
                => sprintf(__('%s must be a valid email address.', 'a-ripple-song'), $field->displayName()),
            $field->required && ($value === null || $value === '' || $value === [])
                /* translators: %s: field label. */
                => sprintf(__('%s is required.', 'a-ripple-song'), $field->displayName()),
            $this->violatesDimensions($field, $value) => $field->dimensions->message(),
            default => $this->fileError($field, $value),
        };
    }

    /**
     * Validate each row of a complex field.
     *
     * Raw rows are not matched to sanitized rows (empty rows were dropped), so raw checks are skipped.
     *
     * @param Field $field Complex field definition.
     * @param array<int,mixed> $rows Sanitized rows.
     * @return array<string,string>
     */
    private function rowErrors(Field $field, array $rows): array
    {
        $errors = [];

        foreach ($rows as $index => $row) {
            foreach ($this->validate($field->fields, is_array($row) ? $row : []) as $childKey => $message) {
                $errors[$field->key . '.' . $index . '.' . $childKey] = $message;
            }
        }

        return $errors;
    }

    /**
     * Whether a submitted email is non-empty but not a valid address.
     *
     * @param mixed $raw Raw submitted value.
     * @return bool
     */
    private function isInvalidEmail(mixed $raw): bool
    {
        return is_string($raw) && trim($raw) !== '' && ! is_email(trim($raw));
    }

    /**
     * Whether a media library image breaks the field's size rules.
     *
     * Only attachments can be measured here; external URLs are checked in the browser.
     *
     * @param Field $field Field definition.
     * @param mixed $value Sanitized value.
     * @return bool
     */
    private function violatesDimensions(Field $field, mixed $value): bool
    {
        if ($field->type !== FieldType::Image || $field->dimensions->isEmpty() || ! is_string($value) || $value === '') {
            return false;
        }

        $attachmentId = attachment_url_to_postid($value);
        $meta = $attachmentId > 0 ? wp_get_attachment_metadata($attachmentId) : false;

        if (! is_array($meta) || empty($meta['width']) || empty($meta['height'])) {
            return false;
        }

        return ! $field->dimensions->matches((int) $meta['width'], (int) $meta['height']);
    }

    /**
     * Return the errors of a file or image that breaks the field's type, transparency or size rules, or null.
     *
     * Facts that cannot be read (e.g. a server without Content-Length) are not reported.
     *
     * @param Field $field Field definition.
     * @param mixed $value Sanitized value.
     * @return string|null
     */
    private function fileError(Field $field, mixed $value): ?string
    {
        $isImage = $field->type === FieldType::Image;
        $hasRules = $field->maxSize > 0 || $field->mimeTypes !== [] || ($isImage && $field->opaque);

        if (! ($isImage || $field->type === FieldType::File) || ! $hasRules || ! is_string($value) || $value === '') {
            return null;
        }

        $probe = FileProbe::fromUrl($value);
        $errors = [];

        if ($field->mimeTypes !== [] && $probe->mime !== '' && ! in_array($probe->mime, $field->mimeTypes, true)) {
            /* translators: %s: current file type or size, e.g. "WEBP" or "830.5 KB". */
            $errors[] = $field->mimeTypesMessage() . ' ' . sprintf(__('Current: %s.', 'a-ripple-song'), FileProbe::label($probe->mime));
        }

        if ($isImage && $field->opaque && $probe->hasAlpha === true) {
            $errors[] = $field->opaqueMessage();
        }

        if ($field->maxSize > 0 && $probe->size !== null && $probe->size > $field->maxSize) {
            /* translators: %s: current file type or size, e.g. "WEBP" or "830.5 KB". */
            $errors[] = $field->maxSizeMessage() . ' ' . sprintf(__('Current: %s.', 'a-ripple-song'), size_format($probe->size, 1));
        }

        return $errors !== [] ? implode(' ', $errors) : null;
    }
}
