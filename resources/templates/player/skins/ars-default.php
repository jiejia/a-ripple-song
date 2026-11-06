<?php
/**
 * Player skin "Default": a plain bar at the page bottom; its list button opens the play queue drawer (player/parts/queue.php).
 * Printed once in wp_footer.
 *
 * Themes can copy this file to {theme}/a-ripple-song/player/skins/ars-default.php and change
 * anything about the markup and classes. player.js only relies on these hooks,
 * and every hook may appear more than once (e.g. in the bar and in the panel):
 *
 *   [data-ars-player]          Root. JS toggles `hidden`, sets data-state="playing|paused|loading|error" and
 *                              data-ars-scheme="light|dark" (the "Player Colors" setting; not set when the theme
 *                              declares add_theme_support('a-ripple-song-player'), the player then inherits its colors).
 *                              It must sit outside the swup container so page swaps keep it.
 *   [data-ars-bar]             Element whose height reserves room at the page bottom (--ars-player-height).
 *   [data-ars-width="box|content"]  Bar container sized by the "Player Width" setting: "box" lines up its outer
 *                              edge with the theme layout, "content" its content (padding stays outside).
 *                              With "wide width, full-width background" it spans the window instead, with square
 *                              corners, no side borders and side padding that centers its content in the wide width
 *                              (keeping its own padding); [data-ars-bar] loses its padding, so the bar sits on the bottom edge.
 *   [data-ars-surface="base-100|base-200"]  Element painting the bar background (base-100 when empty); the
 *                              "Player Bar Background" setting turns it into frosted glass of that daisyUI color.
 *   [data-ars-action="…"]      Buttons: toggle, prev, next, rate, mute, panel (open / close the queue drawer),
 *                              clear (empty the queue; the next page load starts again from the latest episodes).
 *   [data-ars-seek]            <input type="range"> for the progress. The bar's progress row comes from
 *                              player/parts/progress.php, which follows the "Progress Bar & Spectrum" setting.
 *   [data-ars-volume]          <input type="range" min="0" max="1"> for the volume.
 *   [data-ars-text="…"]        Text filled by JS: title, number, status, position, duration, rate.
 *   [data-ars-src="cover"]     <img> showing the episode cover.
 *   [data-ars-href="url"]      Link to the episode page.
 *   [data-ars-show="…"]        Shown only in that state: playing, paused, loading, muted, unmuted, cover, nocover,
 *                              queue / noqueue (the queue has episodes / is empty).
 *   [data-ars-queue]           Queue list; JS clones its <template> per episode and fills
 *                              [data-ars-item="cover|title|duration"]; [data-ars-play] makes the row playable,
 *                              [data-ars-remove] removes it from the queue.
 *
 * Anywhere on the page, <button data-ars-play="123"> plays / pauses episode 123
 * (add data-ars-at="225" to start at 3:45). JS sets data-ars-state="playing|paused|idle"
 * and aria-pressed on it, also on content swapped in later by swup.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;

$iconButton = 'btn btn-ghost btn-circle btn-sm';
?>
<div class="ars-player group" data-ars-player data-state="paused" hidden role="region" aria-label="<?php esc_attr_e('Audio player', 'a-ripple-song'); ?>">

    <?php /* ---------- Bar ---------- */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-2 pb-2 sm:px-4 sm:pb-4" data-ars-bar>
        <div data-ars-width="box" data-ars-surface class="mx-auto max-w-6xl rounded-box border border-base-300 bg-base-100 text-base-content shadow-lg">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-2 @5xl:min-h-16 @5xl:flex-nowrap @5xl:gap-3 @5xl:px-3 @5xl:py-0">
            <a data-ars-href="url" class="flex min-w-0 flex-1 items-center gap-3 text-left order-1 basis-full rounded-box bg-base-content/5 p-2 @5xl:order-none @5xl:basis-auto @5xl:w-64 @5xl:flex-none @5xl:bg-transparent @5xl:p-0">
                <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-field bg-base-200">
                    <img class="size-full object-cover" alt="" data-ars-src="cover" data-ars-show="cover">
                    <span class="opacity-50" data-ars-show="nocover"><?php Icon::render('podcast', 'size-5'); ?></span>
                </span>
                <span class="min-w-0">
                    <span class="flex items-center gap-2 text-xs">
                        <span class="badge badge-xs badge-soft group-data-[state=playing]:badge-primary group-data-[state=error]:badge-error" data-ars-text="status"></span>
                        <span class="opacity-60" data-ars-text="number"></span>
                    </span>
                    <span class="block break-words text-sm font-semibold" data-ars-text="title"></span>
                </span>
            </a>

            <div class="order-5 flex shrink-0 items-center gap-0.5 sm:gap-1 @5xl:order-none">
                <button type="button" class="<?php echo esc_attr($iconButton); ?> inline-flex" data-ars-action="prev" aria-label="<?php esc_attr_e('Previous episode', 'a-ripple-song'); ?>"><?php Icon::render('skip-back', 'size-4'); ?></button>
                <button type="button" class="btn btn-primary btn-circle" data-ars-action="toggle" aria-label="<?php esc_attr_e('Play', 'a-ripple-song'); ?>">
                    <span data-ars-show="paused"><?php Icon::render('play', 'size-5 fill-current'); ?></span>
                    <span data-ars-show="playing" hidden><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
                    <span class="loading loading-spinner loading-sm" data-ars-show="loading" hidden></span>
                </button>
                <button type="button" class="<?php echo esc_attr($iconButton); ?> inline-flex" data-ars-action="next" aria-label="<?php esc_attr_e('Next episode', 'a-ripple-song'); ?>"><?php Icon::render('skip-forward', 'size-4'); ?></button>
            </div>

            <?php
            (new Template())->renderThemeable('player/parts/progress', [
                'hooks' => 'data',
                'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 text-xs tabular-nums @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
                'spectrumTop' => ['data-spectrum' => 'bars', 'class' => 'block h-8 w-full @5xl:hidden'],
                'time' => 'opacity-70',
                'timeEnd' => 'opacity-50',
                'spectrum' => ['data-spectrum' => 'bars', 'class' => 'hidden h-8 w-24 shrink-0 @5xl:block'],
            ]);
            ?>

            <div class="order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-8 @5xl:flex-none">
                <button type="button" class="<?php echo esc_attr($iconButton); ?>" data-ars-action="mute" aria-label="<?php esc_attr_e('Mute', 'a-ripple-song'); ?>">
                    <span data-ars-show="unmuted"><?php Icon::render('volume-2', 'size-4'); ?></span>
                    <span data-ars-show="muted" hidden><?php Icon::render('volume-x', 'size-4'); ?></span>
                </button>
                <input type="range" class="range range-primary range-xs hidden w-20 @5xl:block" min="0" max="1" step="0.05" value="0.8" data-ars-volume aria-label="<?php esc_attr_e('Volume', 'a-ripple-song'); ?>">
            </div>
            <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
                <button type="button" class="btn btn-ghost btn-sm w-14 shrink-0 px-0 font-semibold tabular-nums @5xl:order-7" data-ars-action="rate" aria-label="<?php esc_attr_e('Playback speed', 'a-ripple-song'); ?>"><span data-ars-text="rate">1×</span></button>
            <button type="button" class="btn btn-soft btn-square btn-sm shrink-0 @5xl:order-9" data-ars-action="panel" aria-label="<?php esc_attr_e('Queue', 'a-ripple-song'); ?>" aria-expanded="false"><?php Icon::render('list-music', 'size-5'); ?></button>
            </div>
            </div>
        </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue'); ?>
    <?php (new Template())->renderThemeable('player/parts/resume'); ?>
</div>
