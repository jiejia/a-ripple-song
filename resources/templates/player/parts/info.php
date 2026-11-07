<?php
/**
 * Episode info of the player bars, the same in every skin: the cover on the left; on the right the title
 * (two lines at most) above a small meta line "publish date / EP.X". The cover is as tall as those three lines:
 * 2 × 1.25rem (title) + 1rem (meta) = 3.5rem. A one-line title is centered vertically next to it.
 * The cover and the title link to the episode, and both show the full title on hover.
 *
 * Included by the skin templates, each passing its own look:
 *   $class      Classes of the wrapper (order, size, narrow-bar background).
 *   $width      Width of the wrapper from @5xl on; ignored when the skin passes its own $class.
 *   $cover      Classes of the cover (radius, background, image filter).
 *   $coverBind  Optional ars-bind:class expression of the cover (binder hooks only).
 *   $title      Classes of the title.
 *   $meta       Classes of the meta line, which shows the error instead when there is one.
 *   $hooks      "binder" (ars-* expressions, default) or "data" (the default skin's data-ars-* hooks).
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/info.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Html;
use Jiejia\ARippleSong\Libraries\Icon;

$hooks ??= 'binder';
$width ??= '@5xl:w-72';
$class ??= 'order-1 flex-1 basis-full rounded-2xl bg-base-content/5 p-2 @5xl:order-none @5xl:flex-none @5xl:basis-auto @5xl:bg-transparent @5xl:p-0 ' . $width;
$cover ??= 'rounded-xl bg-base-300';
$coverBind ??= '';
$title ??= 'font-semibold hover:text-primary';
$meta ??= 'opacity-70';

$binder = $hooks !== 'data';
$link = $binder
    ? ['ars-bind:href' => 'current?.url', 'ars-bind:title' => 'current?.title', 'ars-bind:aria-label' => 'current?.title']
    : ['data-ars-href' => 'url'];
$coverHooks = $binder && $coverBind !== '' ? ['ars-bind:class' => $coverBind] : [];
$image = $binder
    ? ['ars-bind:src' => 'current?.cover', 'ars-bind:alt' => 'current?.title']
    : ['data-ars-src' => 'cover', 'data-ars-show' => 'cover'];
$titleText = $binder ? ['ars-text' => 'current?.title'] : ['data-ars-text' => 'title'];
$metaText = $binder
    ? ['ars-text' => "error || [current?.date, current?.number && 'EP.' + current.number].filter(Boolean).join(' / ')", 'ars-bind:class' => "error && '!text-error'"]
    : ['data-ars-text' => 'meta'];
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Html::attributes() escapes.
?>
<div class="flex min-w-0 items-center gap-3 text-left <?php echo esc_attr($class); ?>">
  <a<?php echo Html::attributes(['class' => trim('relative grid size-14 shrink-0 box-border place-items-center overflow-hidden ' . $cover)] + $link + $coverHooks); ?>>
    <img<?php echo Html::attributes(['class' => 'size-full object-cover', 'alt' => ''] + $image); ?>>
    <?php if (! $binder) : ?>
      <span class="opacity-50" data-ars-show="nocover"><?php Icon::render('podcast', 'size-5'); ?></span>
    <?php endif; ?>
    <span class="absolute inset-0 hidden place-items-center bg-black/40 text-white [.ars-player[data-state=loading]_&]:grid" aria-hidden="true"><span class="loading loading-spinner loading-sm"></span></span>
  </a>
  <span class="min-w-0 flex-1">
    <a<?php echo Html::attributes(['class' => trim('line-clamp-2 break-words text-sm leading-5 ' . $title)] + $link + $titleText); ?>></a>
    <span<?php echo Html::attributes(['class' => trim('block truncate text-xs leading-4 ' . $meta)] + $metaText); ?>></span>
  </span>
</div>
<?php
// phpcs:enable
