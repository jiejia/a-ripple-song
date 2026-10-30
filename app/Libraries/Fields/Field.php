<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

use Closure;

/**
 * Immutable field definition shared by post meta boxes and settings pages.
 *
 * Post types and settings pages describe fields as plain arrays, for example
 * ['type' => 'text', 'key' => 'episode_author', 'label' => 'Author', 'width' => 'half'];
 * Field::collect() turns them into typed objects once, so the rest of the
 * library never deals with missing keys.
 */
final readonly class Field
{
    /**
     * @param FieldType $type Field type; unknown types fall back to text.
     * @param string $key Value key; stored as post meta or as a key of the settings option.
     * @param string $label Label shown above the control.
     * @param string $help Help text shown below the control.
     * @param string $description Separator only: text below the section title.
     * @param mixed $default Value used when nothing is stored yet, or a Closure returning it; switch fields store "yes" or "no".
     * @param bool $required Whether an empty value fails validation.
     * @param bool $readonly Filled by code (e.g. audio metadata detection), never by the form.
     * @param FieldWidth $width Column width in the form grid.
     * @param string $placeholder Control placeholder; select fields render it as an empty option.
     * @param bool $copyable Single-line inputs: show a Copy button next to the input.
     * @param array<string,mixed> $attributes Extra control attributes; number fields honor min, max and step.
     * @param array<int|string,mixed>|Closure $options Select options: labels or ['label', 'image', 'meta'], or a Closure returning them.
     * @param string|array<int,string> $mediaType File: restricts the media library, e.g. "audio" or ['text/vtt', 'application/pdf']; unlike mime_types it is not validated.
     * @param array<string,string> $autofill File / image: target field key => attachment property (duration, filesize, mime, width, height, ...).
     * @param ImageDimensions $dimensions Image: size rules.
     * @param int $maxSize File / image: largest allowed file size in bytes; 0 means unrestricted.
     * @param array<int,string> $mimeTypes File / image: allowed MIME types, e.g. ['image/jpeg', 'image/png']; empty allows any.
     * @param bool $opaque Image: reject PNGs that have an alpha channel or a transparent color.
     * @param array<int,Field> $fields Complex: sub fields of each row.
     * @param string $rowLabel Complex: label of one row, e.g. "Soundbite".
     * @param string $rowTitleField Complex: sub field key whose value titles the row.
     * @param string $addLabel Complex: text of the add-row button.
     * @param string|Closure $content Html: markup, or a Closure returning it.
     * @param Closure(mixed,Field):mixed|null $sanitize Overrides the built-in sanitizer.
     */
    public function __construct(
        public FieldType $type = FieldType::Text,
        public string $key = '',
        public string $label = '',
        public string $help = '',
        public string $description = '',
        public mixed $default = null,
        public bool $required = false,
        public bool $readonly = false,
        public FieldWidth $width = FieldWidth::Full,
        public string $placeholder = '',
        public bool $copyable = false,
        public array $attributes = [],
        public array|Closure $options = [],
        public string|array $mediaType = '',
        public array $autofill = [],
        public ImageDimensions $dimensions = new ImageDimensions(),
        public int $maxSize = 0,
        public array $mimeTypes = [],
        public bool $opaque = false,
        public array $fields = [],
        public string $rowLabel = '',
        public string $rowTitleField = '',
        public string $addLabel = '',
        public string|Closure $content = '',
        public ?Closure $sanitize = null,
    ) {
    }

    /**
     * Build a field from a definition array.
     *
     * @param array<string,mixed> $definition Definition with snake_case keys.
     * @return self
     */
    public static function fromArray(array $definition): self
    {
        return new self(
            type: FieldType::tryFrom((string) ($definition['type'] ?? '')) ?? FieldType::Text,
            key: (string) ($definition['key'] ?? ''),
            label: (string) ($definition['label'] ?? ''),
            help: (string) ($definition['help'] ?? ''),
            description: (string) ($definition['description'] ?? ''),
            default: $definition['default'] ?? null,
            required: (bool) ($definition['required'] ?? false),
            readonly: (bool) ($definition['readonly'] ?? false),
            width: FieldWidth::tryFrom((string) ($definition['width'] ?? '')) ?? FieldWidth::Full,
            placeholder: (string) ($definition['placeholder'] ?? ''),
            copyable: (bool) ($definition['copyable'] ?? false),
            attributes: (array) ($definition['attributes'] ?? []),
            options: $definition['options'] ?? [],
            mediaType: is_array($definition['media_type'] ?? null)
                ? array_values(array_map('strval', $definition['media_type']))
                : (string) ($definition['media_type'] ?? ''),
            autofill: (array) ($definition['autofill'] ?? []),
            dimensions: ImageDimensions::fromArray((array) ($definition['dimensions'] ?? [])),
            maxSize: (int) ($definition['max_size'] ?? 0),
            mimeTypes: array_values(array_map('strval', (array) ($definition['mime_types'] ?? []))),
            opaque: (bool) ($definition['opaque'] ?? false),
            fields: self::collect((array) ($definition['fields'] ?? [])),
            rowLabel: (string) ($definition['row_label'] ?? ''),
            rowTitleField: (string) ($definition['row_title_field'] ?? ''),
            addLabel: (string) ($definition['add_label'] ?? ''),
            content: $definition['content'] ?? '',
            sanitize: $definition['sanitize'] ?? null,
        );
    }

    /**
     * Turn a list of definition arrays into fields; Field instances are kept as they are.
     *
     * @param array<int,array<string,mixed>|Field> $definitions
     * @return array<int,Field>
     */
    public static function collect(array $definitions): array
    {
        return array_map(
            static fn (array|self $definition): self => $definition instanceof self ? $definition : self::fromArray($definition),
            array_values($definitions)
        );
    }

    /**
     * Find a field by key.
     *
     * @param array<int,Field> $fields Fields to search.
     * @param string $key Field key.
     * @return self|null
     */
    public static function find(array $fields, string $key): ?self
    {
        foreach ($fields as $field) {
            if ($field->key === $key) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Return the display label of one option.
     *
     * @param mixed $option Option label or ['label' => ..., 'image' => ..., 'meta' => ...].
     * @return string
     */
    public static function optionLabel(mixed $option): string
    {
        return is_array($option) ? (string) ($option['label'] ?? '') : (string) $option;
    }

    /**
     * Whether the field holds a stored value.
     *
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->key !== '' && ! $this->type->isLayout();
    }

    /**
     * Whether the value should be taken from submitted form data.
     *
     * @return bool
     */
    public function isSavable(): bool
    {
        return $this->hasValue() && ! $this->readonly;
    }

    /**
     * Return the value used when nothing has been stored yet.
     *
     * @return mixed
     */
    public function defaultValue(): mixed
    {
        $default = $this->default instanceof Closure ? ($this->default)() : $this->default;

        return $default ?? ($this->type->isList() ? [] : '');
    }

    /**
     * Return the resolved option list of a select or multiselect field.
     *
     * @return array<int|string,mixed>
     */
    public function options(): array
    {
        $options = $this->options instanceof Closure ? ($this->options)() : $this->options;

        return is_array($options) ? $options : [];
    }

    /**
     * Return the resolved markup of an html field.
     *
     * @return string
     */
    public function content(): string
    {
        return (string) ($this->content instanceof Closure ? ($this->content)() : $this->content);
    }

    /**
     * Describe the file size limit, e.g. "File must be 512 KB or smaller."
     *
     * @return string
     */
    public function maxSizeMessage(): string
    {
        /* translators: %s: maximum file size, e.g. "512 KB". */
        return sprintf(__('File must be %s or smaller.', 'a-ripple-song'), size_format($this->maxSize));
    }

    /**
     * Describe the allowed file types, e.g. "Allowed file types: JPG, PNG."
     *
     * @return string
     */
    public function mimeTypesMessage(): string
    {
        /* translators: %s: comma separated list of file types, e.g. "JPG, PNG". */
        return sprintf(__('Allowed file types: %s.', 'a-ripple-song'), implode(', ', array_map(FileProbe::label(...), $this->mimeTypes)));
    }

    /**
     * Describe the transparency rule.
     *
     * @return string
     */
    public function opaqueMessage(): string
    {
        return __('Image must not contain transparency or an alpha channel.', 'a-ripple-song');
    }

    /**
     * Name used in messages: the label, or the key when the field has none.
     *
     * @return string
     */
    public function displayName(): string
    {
        return $this->label !== '' ? $this->label : $this->key;
    }
}
