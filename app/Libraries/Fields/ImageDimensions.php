<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Size rules of an image field; a 0 limit means unrestricted.
 *
 * Serializes to the snake_case shape fields.js reads from data-dimensions.
 */
final readonly class ImageDimensions implements \JsonSerializable
{
    public function __construct(
        public int $minWidth = 0,
        public int $minHeight = 0,
        public int $maxWidth = 0,
        public int $maxHeight = 0,
        public bool $square = false,
    ) {
    }

    /**
     * Build rules from a definition array.
     *
     * @param array{min_width?:int,min_height?:int,max_width?:int,max_height?:int,square?:bool} $rules
     * @return self
     */
    public static function fromArray(array $rules): self
    {
        return new self(
            minWidth: (int) ($rules['min_width'] ?? 0),
            minHeight: (int) ($rules['min_height'] ?? 0),
            maxWidth: (int) ($rules['max_width'] ?? 0),
            maxHeight: (int) ($rules['max_height'] ?? 0),
            square: ! empty($rules['square']),
        );
    }

    /**
     * Whether no rule is set.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->jsonSerialize() === [];
    }

    /**
     * Whether an image size satisfies the rules.
     *
     * @param int $width Image width in pixels.
     * @param int $height Image height in pixels.
     * @return bool
     */
    public function matches(int $width, int $height): bool
    {
        return $width >= $this->minWidth
            && $height >= $this->minHeight
            && ($this->maxWidth === 0 || $width <= $this->maxWidth)
            && ($this->maxHeight === 0 || $height <= $this->maxHeight)
            && (! $this->square || $width === $height);
    }

    /**
     * Describe the rules, e.g. "Image must be square, at least 1400 × 1400 px, at most 3000 × 3000 px."
     *
     * @return string
     */
    public function message(): string
    {
        $parts = [];

        if ($this->square) {
            $parts[] = __('square', 'a-ripple-song');
        }

        if ($this->minWidth > 0 || $this->minHeight > 0) {
            /* translators: 1: minimum width, 2: minimum height. */
            $parts[] = sprintf(__('at least %1$d × %2$d px', 'a-ripple-song'), $this->minWidth, $this->minHeight);
        }

        if ($this->maxWidth > 0 || $this->maxHeight > 0) {
            /* translators: 1: maximum width, 2: maximum height. */
            $parts[] = sprintf(__('at most %1$d × %2$d px', 'a-ripple-song'), $this->maxWidth, $this->maxHeight);
        }

        /* translators: %s: comma separated list of image size rules. */
        return sprintf(__('Image must be %s.', 'a-ripple-song'), implode(', ', $parts));
    }

    /**
     * Return the rules that are set, keyed like the definition array.
     *
     * @return array<string,int|bool>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'square' => $this->square,
            'min_width' => $this->minWidth,
            'min_height' => $this->minHeight,
            'max_width' => $this->maxWidth,
            'max_height' => $this->maxHeight,
        ]);
    }
}
