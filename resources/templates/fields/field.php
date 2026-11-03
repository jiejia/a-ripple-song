<?php
/**
 * One field wrapper with label, control, help and error text.
 *
 * @var FieldContext $control
 * @var Field $field
 * @var FieldRenderer $renderer
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use App\Libraries\Fields\Field;
use App\Libraries\Fields\FieldContext;
use App\Libraries\Fields\FieldRenderer;
use App\Libraries\Fields\FieldType;
?>
<div class="<?php echo esc_attr($control->classes()); ?>" data-label="<?php echo esc_attr($field->label); ?>">
    <?php
    // Html fields print their own plain label.
    if ($field->type !== FieldType::Html) {
        $renderer->partial('label', $control);
    }

    $renderer->control($control);

    if ($field->type->hasTrailingHelp()) {
        $renderer->partial('help', $control);
    }
    ?>
</div>
