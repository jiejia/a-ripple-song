<?php
/**
 * Free-form markup between fields; holds no value.
 *
 * @var Field $field
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
?>
<?php if ($field->label !== '') : ?>
    <span class="ars-field__label"><?php echo esc_html($field->label); ?></span>
<?php endif; ?>
<?php echo wp_kses_post($field->content()); ?>
