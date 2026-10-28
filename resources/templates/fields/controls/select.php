<?php
/**
 * Single select control.
 *
 * @var FieldContext $control
 * @var Field $field
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;

$current = $control->scalar();
?>
<select<?php echo $control->attributes(['placeholder' => null, 'readonly' => null]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>>
    <?php if ($field->placeholder !== '') : ?>
        <option value=""><?php echo esc_html($field->placeholder); ?></option>
    <?php endif; ?>
    <?php foreach ($field->options() as $key => $option) : ?>
        <option value="<?php echo esc_attr((string) $key); ?>"<?php selected((string) $key, $current); ?>><?php echo esc_html(Field::optionLabel($option)); ?></option>
    <?php endforeach; ?>
</select>
