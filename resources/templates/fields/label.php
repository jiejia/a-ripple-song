<?php
/**
 * Field label; grouped controls get a span referenced by aria-labelledby.
 *
 * @var FieldContext $control
 * @var Field $field
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
use Jiejia\ARippleSong\Libraries\Html;

if ($field->label === '') {
    return;
}

$tag = $field->type->isList() ? 'span' : 'label';
$attributes = $field->type->isList()
    ? ['class' => 'ars-field__label', 'id' => $control->id . '-label']
    : ['class' => 'ars-field__label', 'for' => $control->id];
?>
<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed tag name. ?><?php echo Html::attributes($attributes); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>><?php echo esc_html($field->label); ?><?php if ($field->required) : ?><span class="ars-field__required" aria-hidden="true">*</span><?php endif; ?><?php if ($field->readonly) : ?><span class="ars-field__badge"><?php esc_html_e('Auto', 'a-ripple-song'); ?></span><?php endif; ?></<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed tag name. ?>>
