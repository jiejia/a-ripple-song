<?php
/**
 * Small screens (below 768px): the button left of the queue button that collapses the bar into the mini player
 * (player/parts/mini.php, which has the button that restores it). Hidden on wider screens by the base styles in
 * GlobalPlayer.php.
 *
 * Included by the skin templates, each passing its own look:
 *   $class  Classes of the button, usually those of the skin's queue button.
 *   $icon   Classes of the icon.
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/collapse.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;

$class ??= 'btn btn-ghost btn-circle btn-sm';
$icon ??= 'size-5';
?>
<button type="button" class="<?php echo esc_attr($class); ?>" data-ars-collapse data-ars-action="mini" aria-expanded="true" aria-label="<?php esc_attr_e('Collapse player', 'a-ripple-song'); ?>"><?php Icon::render('chevron-down', $icon); ?></button>
