<?php
/**
 * Repeatable group of sub fields.
 *
 * @var FieldContext $control
 * @var Field $field
 * @var FieldRenderer $renderer
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
use Jiejia\ARippleSong\Libraries\Fields\FieldRenderer;

$rows = array_values(array_filter($control->list(), 'is_array'));
$rowLabel = $field->rowLabel !== '' ? $field->rowLabel : __('Item', 'a-ripple-song');
/* translators: %s: repeater row label, e.g. "Soundbite". */
$addLabel = $field->addLabel !== '' ? $field->addLabel : sprintf(__('Add %s', 'a-ripple-song'), $rowLabel);
?>
<div class="ars-repeater<?php echo $rows === [] ? ' is-empty' : ''; ?>" data-ars-repeater data-row-label="<?php echo esc_attr($rowLabel); ?>" data-title-field="<?php echo esc_attr($field->rowTitleField); ?>" role="group" aria-labelledby="<?php echo esc_attr($control->id . '-label'); ?>">
    <ol class="ars-repeater__rows">
        <?php foreach ($rows as $index => $row) : ?>
            <?php $renderer->partial('controls/complex-row', $control, ['row' => $row, 'index' => (string) $index, 'rowLabel' => $rowLabel]); ?>
        <?php endforeach; ?>
    </ol>
    <p class="ars-repeater__empty"><?php esc_html_e('Nothing added yet.', 'a-ripple-song'); ?></p>
    <?php // New rows are cloned from this template; JS replaces __INDEX__ with a unique key. ?>
    <template class="ars-repeater__template">
        <?php $renderer->partial('controls/complex-row', $control, ['row' => [], 'index' => '__INDEX__', 'rowLabel' => $rowLabel]); ?>
    </template>
    <button type="button" class="button ars-repeater__add"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php echo esc_html($addLabel); ?></button>
</div>
