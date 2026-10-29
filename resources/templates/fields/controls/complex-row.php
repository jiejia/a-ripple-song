<?php
/**
 * One repeater row.
 *
 * @var FieldContext $control Complex field the row belongs to.
 * @var Field $field
 * @var FieldRenderer $renderer
 * @var array<string,mixed> $row Row values.
 * @var string $index Row index or the __INDEX__ placeholder.
 * @var string $rowLabel Row label.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
use Jiejia\ARippleSong\Libraries\Fields\FieldRenderer;

$subtitle = $field->rowTitleField !== '' ? FieldContext::toScalar($row[$field->rowTitleField] ?? '') : '';
$position = is_numeric($index) ? ' ' . ((int) $index + 1) : '';
?>
<li class="ars-repeater__row">
    <div class="ars-repeater__row-header">
        <span class="ars-repeater__handle dashicons dashicons-menu" title="<?php esc_attr_e('Drag to reorder', 'a-ripple-song'); ?>" aria-hidden="true"></span>
        <span class="ars-repeater__row-title"><span class="ars-repeater__row-index"><?php echo esc_html($rowLabel . $position); ?></span><span class="ars-repeater__row-subtitle"><?php echo esc_html($subtitle); ?></span></span>
        <button type="button" class="ars-repeater__remove" aria-label="<?php esc_attr_e('Remove', 'a-ripple-song'); ?>" title="<?php esc_attr_e('Remove', 'a-ripple-song'); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
    </div>
    <?php $renderer->renderGrid($field->fields, $row, $control->name . '[' . $index . ']', 'ars-repeater__row-body'); ?>
</li>
