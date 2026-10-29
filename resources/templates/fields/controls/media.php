<?php
/**
 * File or image field backed by the WordPress media library.
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
use Jiejia\ARippleSong\Libraries\Fields\FieldType;
use Jiejia\ARippleSong\Libraries\Html;

$url = $control->scalar();
$isImage = $field->type === FieldType::Image;
$checkDimensions = $isImage && ! $field->dimensions->isEmpty();
$attributes = [
    'class' => Html::classes(['ars-media', 'ars-media--' . $field->type->value, 'has-value' => $url !== '']),
    'data-ars-media' => true,
    // The picker then lists only files of the allowed types.
    'data-library' => $field->mimeTypes !== [] ? wp_json_encode($field->mimeTypes) : ($isImage ? 'image' : $field->mediaType),
    'data-frame-title' => $field->label,
    'data-frame-button' => __('Use this file', 'a-ripple-song'),
    'data-autofill' => $field->autofill !== [] ? wp_json_encode($field->autofill) : null,
    'data-dimensions' => $checkDimensions ? wp_json_encode($field->dimensions) : null,
    'data-dimensions-message' => $checkDimensions ? $field->dimensions->message() : null,
    'data-max-size' => $field->maxSize > 0 ? $field->maxSize : null,
    'data-max-size-message' => $field->maxSize > 0 ? $field->maxSizeMessage() : null,
    'data-mime-types' => $field->mimeTypes !== [] ? wp_json_encode($field->mimeTypes) : null,
    'data-mime-types-message' => $field->mimeTypes !== [] ? $field->mimeTypesMessage() : null,
    'data-opaque' => $isImage && $field->opaque,
    'data-opaque-message' => $isImage && $field->opaque ? $field->opaqueMessage() : null,
];
?>
<div<?php echo Html::attributes($attributes); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>>
    <?php if ($isImage) : ?>
        <?php // The preview doubles as the picker button; read-only fields only show it. ?>
        <?php if ($field->readonly) : ?>
            <div class="ars-media__preview">
        <?php else : ?>
            <button type="button" class="ars-media__preview ars-media__select" aria-label="<?php esc_attr_e('Select Image', 'a-ripple-song'); ?>">
        <?php endif; ?>
            <img class="ars-media__image" src="<?php echo esc_url($url); ?>" alt="">
            <span class="ars-media__empty" aria-hidden="true">
                <span class="dashicons dashicons-format-image"></span>
                <?php if (! $field->readonly) : ?>
                    <span class="ars-media__empty-text"><?php esc_html_e('Select Image', 'a-ripple-song'); ?></span>
                <?php endif; ?>
            </span>
            <?php if (! $field->readonly) : ?>
                <span class="ars-media__overlay" aria-hidden="true"><span class="dashicons dashicons-update"></span><?php esc_html_e('Replace', 'a-ripple-song'); ?></span>
            <?php endif; ?>
        <?php echo $field->readonly ? '</div>' : '</button>'; ?>
    <?php endif; ?>
    <div class="ars-media__body">
        <div class="ars-media__row">
            <input<?php echo $control->attributes(['type' => 'url', 'value' => $url, 'class' => 'ars-input ars-media__input', 'placeholder' => 'https://']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>>
            <?php if (! $field->readonly) : ?>
                <?php // Image fields pick through their preview tile. ?>
                <?php if (! $isImage) : ?>
                    <button type="button" class="button ars-media__select"><span class="dashicons dashicons-upload" aria-hidden="true"></span><?php esc_html_e('Select File', 'a-ripple-song'); ?></button>
                <?php endif; ?>
                <button type="button" class="button ars-media__remove" aria-label="<?php esc_attr_e('Remove', 'a-ripple-song'); ?>" title="<?php esc_attr_e('Remove', 'a-ripple-song'); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
            <?php endif; ?>
        </div>
        <?php if ($isImage) : ?>
            <?php // File name and pixel size, filled in by fields.js once the image loads. ?>
            <p class="ars-media__meta"></p>
        <?php endif; ?>
        <?php if ($field->mediaType === 'audio') : ?>
            <audio class="ars-media__audio" controls preload="none"<?php echo $url !== '' ? ' src="' . esc_url($url) . '"' : ''; ?>></audio>
        <?php endif; ?>
        <?php $renderer->partial('help', $control); ?>
    </div>
</div>
