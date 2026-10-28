<?php
/**
 * Textarea control.
 *
 * @var FieldContext $control
 */

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
?>
<textarea<?php echo $control->attributes(['rows' => 3]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>><?php echo esc_textarea($control->scalar()); ?></textarea>
