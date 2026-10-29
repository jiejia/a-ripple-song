<?php
/**
 * On / off switch storing "yes" or "no"; the hidden input submits "no" while unchecked.
 *
 * @var FieldContext $control
 * @var Field $field
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
?>
<input type="hidden" name="<?php echo esc_attr($control->name); ?>" value="no">
<label class="ars-switch">
    <?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>
    <input<?php echo $control->attributes([
        'type' => 'checkbox',
        'role' => 'switch',
        'class' => 'ars-switch__input',
        'value' => 'yes',
        'checked' => $control->scalar() === 'yes',
        'placeholder' => null,
        'required' => null,
        'readonly' => null,
        'disabled' => $field->readonly,
    ]); ?>>
    <?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</label>
