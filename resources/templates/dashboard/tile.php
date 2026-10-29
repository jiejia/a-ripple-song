<?php
/**
 * One statistic tile.
 *
 * @var string $label Label, sentence case.
 * @var string $value Formatted value.
 * @var string $icon Dashicon name without the "dashicons-" prefix.
 * @var string $meta Optional secondary line.
 * @var array{text:string,direction:string}|null $delta Optional change; direction is up, down or flat.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

$meta ??= '';
$delta ??= null;
?>
<div class="ars-dash-tile">
    <div class="ars-dash-tile__label">
        <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
        <?php echo esc_html($label); ?>
    </div>
    <div class="ars-dash-tile__value"><?php echo esc_html($value); ?></div>
    <?php if ($delta !== null || $meta !== '') : ?>
        <div class="ars-dash-tile__meta">
            <?php if ($delta !== null) : ?>
                <span class="ars-dash-delta ars-dash-delta--<?php echo esc_attr($delta['direction']); ?>">
                    <span class="dashicons dashicons-arrow-<?php echo esc_attr($delta['direction'] === 'down' ? 'down' : ($delta['direction'] === 'up' ? 'up' : 'right')); ?>-alt" aria-hidden="true"></span>
                    <?php echo esc_html($delta['text']); ?>
                </span>
            <?php endif; ?>
            <?php echo esc_html($meta); ?>
        </div>
    <?php endif; ?>
</div>
