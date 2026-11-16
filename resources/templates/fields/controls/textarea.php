<?php
/**
 * Textarea control.
 *
 * @var FieldContext $control
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
?>
<textarea<?php \Jiejia\ARippleSong\Libraries\Html::renderAttributes($control->attributeValues(['rows' => 3])); ?>><?php echo esc_textarea($control->scalar()); ?></textarea>
