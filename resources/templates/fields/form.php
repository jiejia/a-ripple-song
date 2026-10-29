<?php
/**
 * Field form: fields grouped into card sections.
 *
 * @var array<int,FieldSection> $sections
 * @var array<string,mixed> $values Stored values keyed by field key.
 * @var string $nameBase Form name all fields are nested under.
 * @var FieldRenderer $renderer
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Fields\FieldRenderer;
use Jiejia\ARippleSong\Libraries\Fields\FieldSection;
?>
<div class="ars-fields">
    <?php foreach ($sections as $section) : ?>
        <section class="ars-fields__section">
            <?php if ($section->label !== '') : ?>
                <header class="ars-fields__section-header">
                    <h3 class="ars-fields__section-title"><?php echo esc_html($section->label); ?></h3>
                    <?php if ($section->description !== '') : ?>
                        <p class="ars-fields__section-description"><?php echo esc_html($section->description); ?></p>
                    <?php endif; ?>
                </header>
            <?php endif; ?>
            <div class="ars-fields__section-body">
                <?php $renderer->renderGrid($section->fields, $values, $nameBase); ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
