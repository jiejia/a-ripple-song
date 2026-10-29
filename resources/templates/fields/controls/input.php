<?php
/**
 * Single-line input (text, number, url, email), optionally with a Copy button.
 *
 * @var FieldContext $control
 * @var Field $field
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;

$attributes = $control->attributes([
    'type' => $field->type->inputType(),
    'value' => $control->scalar(),
]);
?>
<?php if (! $field->copyable) : ?>
    <input<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>>
<?php else : ?>
    <div class="ars-copy"><input<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>><button type="button" class="button ars-copy__button" data-copied="<?php esc_attr_e('Copied!', 'a-ripple-song'); ?>"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><span class="ars-copy__text"><?php esc_html_e('Copy', 'a-ripple-song'); ?></span></button></div>
<?php endif; ?>
