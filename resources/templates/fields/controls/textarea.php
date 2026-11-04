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
<textarea<?php echo $control->attributes(['rows' => 3]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>><?php echo esc_textarea($control->scalar()); ?></textarea>
