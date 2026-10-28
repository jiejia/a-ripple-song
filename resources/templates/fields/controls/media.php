<?php
/**
 * File or image field backed by the WordPress media library.
 *
 * @var FieldContext $control
 * @var Field $field
 * @var FieldRenderer $renderer
 */

defined('ABSPATH') || exit;

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
    'data-library' => $isImage ? 'image' : $field->mediaType,
    'data-frame-title' => $field->label,
    'data-frame-button' => __('Use this file', 'a-ripple-song'),
    'data-autofill' => $field->autofill !== [] ? wp_json_encode($field->autofill) : null,
    'data-dimensions' => $checkDimensions ? wp_json_encode($field->dimensions) : null,
    'data-dimensions-message' => $checkDimensions ? $field->dimensions->message() : null,
];
?>
<div<?php echo Html::attributes($attributes); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>>
    <?php if ($isImage) : ?>
        <div class="ars-media__preview">
            <img class="ars-media__image" src="<?php echo esc_url($url); ?>" alt="">
            <span class="ars-media__placeholder dashicons dashicons-format-image" aria-hidden="true"></span>
        </div>
    <?php endif; ?>
    <div class="ars-media__body">
        <div class="ars-media__row">
            <input<?php echo $control->attributes(['type' => 'url', 'value' => $url, 'class' => 'ars-input ars-media__input', 'placeholder' => 'https://']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by FieldContext::attributes(). ?>>
            <?php if (! $field->readonly) : ?>
                <button type="button" class="button ars-media__select"><span class="dashicons dashicons-upload" aria-hidden="true"></span><?php echo $isImage ? esc_html__('Select Image', 'a-ripple-song') : esc_html__('Select File', 'a-ripple-song'); ?></button>
                <button type="button" class="button-link button-link-delete ars-media__remove"><?php esc_html_e('Remove', 'a-ripple-song'); ?></button>
            <?php endif; ?>
        </div>
        <?php if ($field->mediaType === 'audio') : ?>
            <audio class="ars-media__audio" controls preload="none"<?php echo $url !== '' ? ' src="' . esc_url($url) . '"' : ''; ?>></audio>
        <?php endif; ?>
        <?php $renderer->partial('help', $control); ?>
    </div>
</div>
