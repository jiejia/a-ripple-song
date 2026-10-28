<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Template;

/**
 * Renders fields into admin form markup using the templates in resources/templates/fields.
 *
 * It only turns fields plus current values into HTML, so the same renderer
 * serves post meta boxes and settings pages. Reading and saving values is done
 * by the stores (PostMetaStore / OptionStore).
 */
final class FieldRenderer
{
    /**
     * Server-side validation errors keyed by input name.
     *
     * @var array<string,string>
     */
    private readonly array $errors;

    /**
     * @param string $nameBase Form name all fields are nested under, e.g. "ars_podcast" => ars_podcast[audio_file].
     * @param array<string,string> $errors Errors from FieldValidator, keyed by "key" or "parent.rowIndex.child".
     * @param Template $template Template renderer.
     */
    public function __construct(
        private readonly string $nameBase,
        array $errors = [],
        private readonly Template $template = new Template(),
    ) {
        $byName = [];

        foreach ($errors as $path => $message) {
            // "funding.0.url" => ars_podcast_settings[funding][0][url]
            $byName[$this->nameBase . '[' . str_replace('.', '][', (string) $path) . ']'] = $message;
        }

        $this->errors = $byName;
    }

    /**
     * Render all fields, grouped into card sections by separator fields.
     *
     * @param array<int,Field> $fields Fields to render.
     * @param array<string,mixed> $values Stored values keyed by field key; missing keys fall back to defaults.
     * @return void
     */
    public function render(array $fields, array $values): void
    {
        $this->template->render('fields/form', [
            'sections' => FieldSection::split($fields),
            'values' => $values,
            'nameBase' => $this->nameBase,
            'renderer' => $this,
        ]);
    }

    /**
     * Render fields into a responsive grid.
     *
     * @param array<int,Field> $fields Fields to render.
     * @param array<string,mixed> $values Stored values keyed by field key.
     * @param string $nameBase Form name the fields are nested under.
     * @param string $extraClass Additional grid class.
     * @return void
     */
    public function renderGrid(array $fields, array $values, string $nameBase, string $extraClass = ''): void
    {
        $controls = [];

        foreach ($fields as $field) {
            // Separators are turned into sections and never take a grid cell.
            if ($field->type === FieldType::Separator) {
                continue;
            }

            $name = $nameBase . '[' . $field->key . ']';
            $value = array_key_exists($field->key, $values) ? $values[$field->key] : $field->defaultValue();

            $controls[] = new FieldContext($field, $value, $name, $this->errors[$name] ?? '');
        }

        $this->template->render('fields/grid', [
            'controls' => $controls,
            'extraClass' => $extraClass,
            'renderer' => $this,
        ]);
    }

    /**
     * Render a field template; it receives $control, $field and $renderer plus $data.
     *
     * @param string $name Template name relative to resources/templates/fields, e.g. "label".
     * @param FieldContext $control Field being rendered.
     * @param array<string,mixed> $data Extra template variables.
     * @return void
     */
    public function partial(string $name, FieldContext $control, array $data = []): void
    {
        $this->template->render('fields/' . $name, [
            ...$data,
            'control' => $control,
            'field' => $control->field,
            'renderer' => $this,
        ]);
    }

    /**
     * Render the control matching the field type.
     *
     * @param FieldContext $control Field being rendered.
     * @return void
     */
    public function control(FieldContext $control): void
    {
        $this->partial('controls/' . $control->field->type->template(), $control);
    }
}
