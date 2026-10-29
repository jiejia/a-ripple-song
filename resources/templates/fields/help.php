<?php
/**
 * Help text and error placeholder of a field.
 *
 * @var FieldContext $control
 * @var Field $field
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
?>
<?php if ($field->help !== '') : ?>
    <p class="ars-field__help" id="<?php echo esc_attr($control->id . '-help'); ?>"><?php echo esc_html($field->help); ?></p>
<?php endif; ?>
<?php // Server-side errors are printed here; fields.js fills it for client-side checks. ?>
<p class="ars-field__error" aria-live="polite"><?php echo esc_html($control->error); ?></p>
