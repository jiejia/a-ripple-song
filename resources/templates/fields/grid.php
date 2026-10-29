<?php
/**
 * Responsive grid of fields.
 *
 * @var array<int,FieldContext> $controls
 * @var string $extraClass Additional grid class.
 * @var FieldRenderer $renderer
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\FieldContext;
use Jiejia\ARippleSong\Libraries\Fields\FieldRenderer;
?>
<div class="<?php echo esc_attr(trim('ars-fields__grid ' . $extraClass)); ?>">
    <?php foreach ($controls as $control) : ?>
        <?php $renderer->partial('field', $control); ?>
    <?php endforeach; ?>
</div>
