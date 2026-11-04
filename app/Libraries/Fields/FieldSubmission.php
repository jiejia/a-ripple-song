<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Sanitized values and validation errors of one form submission.
 */
final readonly class FieldSubmission
{
    /**
     * @param array<string,mixed> $values Sanitized values keyed by field key.
     * @param array<string,string> $errors Validation errors, see FieldValidator::validate().
     */
    public function __construct(
        public array $values,
        public array $errors,
    ) {
    }

    /**
     * Sanitize and validate the fields posted under a form name.
     *
     * @param string $name Form name, e.g. ars_podcast_settings.
     * @param array<int,Field> $fields Fields of the form.
     * @return self
     */
    public static function fromRequest(string $name, array $fields): self
    {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- Sanitized per field below; callers verify the nonce.
        $input = isset($_POST[$name]) && is_array($_POST[$name]) ? wp_unslash($_POST[$name]) : [];

        return self::fromInput($fields, $input);
    }

    /**
     * Sanitize and validate submitted values.
     *
     * @param array<int,Field> $fields Fields of the form.
     * @param array<string,mixed> $input Unslashed submitted values keyed by field key.
     * @return self
     */
    public static function fromInput(array $fields, array $input): self
    {
        $values = (new FieldSanitizer())->sanitize($fields, $input);

        return new self($values, (new FieldValidator())->validate($fields, $values, $input));
    }

    /**
     * Whether no field failed validation.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
