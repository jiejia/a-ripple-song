<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * A card of the field form; separator fields start a new one.
 */
final readonly class FieldSection
{
    /**
     * @param string $label Section title, empty for the untitled leading section.
     * @param string $description Text below the title.
     * @param array<int,Field> $fields Fields in this section.
     */
    public function __construct(
        public string $label = '',
        public string $description = '',
        public array $fields = [],
    ) {
    }

    /**
     * Split fields into sections, each separator starting a new one.
     *
     * Fields placed before the first separator form an untitled section; it is
     * dropped when empty, while titled sections are kept even without fields.
     *
     * @param array<int,Field> $fields Fields to split.
     * @return array<int,self>
     */
    public static function split(array $fields): array
    {
        $groups = [[null, []]];

        foreach ($fields as $field) {
            if ($field->type === FieldType::Separator) {
                $groups[] = [$field, []];
            } else {
                $groups[array_key_last($groups)][1][] = $field;
            }
        }

        $sections = [];

        foreach ($groups as [$separator, $grouped]) {
            if ($separator !== null || $grouped !== []) {
                $sections[] = new self($separator->label ?? '', $separator->description ?? '', $grouped);
            }
        }

        return $sections;
    }
}
