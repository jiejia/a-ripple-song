<?php
/**
 * Multiselect rendered as a searchable checkbox list.
 *
 * @var FieldContext $control
 * @var Field $field
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\Field;
use Jiejia\ARippleSong\Libraries\Fields\FieldContext;

$options = $field->options();
$selected = array_map('strval', $control->list());
$checkedCount = count(array_intersect(array_map('strval', array_keys($options)), $selected));
/* translators: %d: number of selected options. */
$countTemplate = __('%d selected', 'a-ripple-song');
?>
<div class="ars-choices" data-ars-choices data-count-template="<?php echo esc_attr($countTemplate); ?>" role="group" aria-labelledby="<?php echo esc_attr($control->id . '-label'); ?>">
    <?php // A search box only pays off once the list gets long. ?>
    <?php if (count($options) > 7) : ?>
        <div class="ars-choices__search"><span class="dashicons dashicons-search" aria-hidden="true"></span><input type="search" class="ars-choices__search-input" placeholder="<?php esc_attr_e('Search…', 'a-ripple-song'); ?>" aria-label="<?php esc_attr_e('Search…', 'a-ripple-song'); ?>"></div>
    <?php endif; ?>
    <ul class="ars-choices__list">
        <?php foreach ($options as $key => $option) : ?>
            <?php
            $image = is_array($option) ? (string) ($option['image'] ?? '') : '';
            $meta = is_array($option) ? (string) ($option['meta'] ?? '') : '';
            ?>
            <li class="ars-choices__item"><label class="ars-choice"><input type="checkbox" name="<?php echo esc_attr($control->name); ?>[]" value="<?php echo esc_attr((string) $key); ?>" data-ars-key="<?php echo esc_attr($field->key); ?>"<?php checked(in_array((string) $key, $selected, true)); ?>><?php if ($image !== '') : ?><img class="ars-choice__image" src="<?php echo esc_url($image); ?>" alt="" loading="lazy"><?php endif; ?><span class="ars-choice__label"><?php echo esc_html(Field::optionLabel($option)); ?></span><?php if ($meta !== '') : ?><span class="ars-choice__meta"><?php echo esc_html($meta); ?></span><?php endif; ?></label></li>
        <?php endforeach; ?>
    </ul>
    <p class="ars-choices__empty"<?php echo $options === [] ? '' : ' hidden'; ?>><?php echo $options === [] ? esc_html__('No options available.', 'a-ripple-song') : esc_html__('No matches found.', 'a-ripple-song'); ?></p>
    <div class="ars-choices__footer"><span class="ars-choices__count"><?php echo esc_html(sprintf($countTemplate, $checkedCount)); ?></span></div>
</div>
