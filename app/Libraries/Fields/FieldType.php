<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Supported field types and the behavior that differs between them.
 */
enum FieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Url = 'url';
    case Email = 'email';
    case Textarea = 'textarea';
    case Select = 'select';
    case Switch = 'switch';
    case Multiselect = 'multiselect';
    case File = 'file';
    case Image = 'image';
    case Complex = 'complex';
    case Separator = 'separator';
    case Html = 'html';

    /**
     * Whether the type only decorates the form and never holds a value.
     *
     * @return bool
     */
    public function isLayout(): bool
    {
        return $this === self::Separator || $this === self::Html;
    }

    /**
     * Whether the value is a list; such fields are labelled as a group instead of a single control.
     *
     * @return bool
     */
    public function isList(): bool
    {
        return $this === self::Multiselect || $this === self::Complex;
    }

    /**
     * Whether the value is a media library URL.
     *
     * @return bool
     */
    public function isMedia(): bool
    {
        return $this === self::File || $this === self::Image;
    }

    /**
     * Whether help text goes below the control; media fields print it beside the preview, html fields have none.
     *
     * @return bool
     */
    public function hasTrailingHelp(): bool
    {
        return ! $this->isMedia() && $this !== self::Html;
    }

    /**
     * Return the template under resources/templates/fields/controls that renders the control.
     *
     * @return string
     */
    public function template(): string
    {
        return match ($this) {
            self::Textarea => 'textarea',
            self::Select => 'select',
            self::Switch => 'switch',
            self::Multiselect => 'multiselect',
            self::File, self::Image => 'media',
            self::Complex => 'complex',
            self::Html => 'html',
            default => 'input',
        };
    }

    /**
     * Return the <input type> of single-line controls.
     *
     * @return string
     */
    public function inputType(): string
    {
        return match ($this) {
            self::Number, self::Url, self::Email => $this->value,
            default => 'text',
        };
    }
}
