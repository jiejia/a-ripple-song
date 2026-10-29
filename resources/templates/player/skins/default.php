<?php
/**
 * Player skin "Default": a plain bar at the page bottom plus a full-screen "Now Playing" panel.
 * Printed once in wp_footer.
 *
 * Themes can copy this file to {theme}/a-ripple-song/player/skins/default.php and change
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
 *   [data-ars-panel-width="box|content"]  The same for the full-screen panel's container; it follows the theme's
 *                              wide width (also when the bar uses the content width, which is too narrow for a panel).
 *                              Clicks beside it (backdrop, margins) close the panel.
 *   [data-ars-surface="base-100|base-200"]  Element painting the bar background (base-100 when empty); the
 *                              "Player Bar Background" setting turns it into frosted glass of that daisyUI color.
 *   [data-ars-panel]           Full-screen panel; a <dialog> (opened modal) or any element (toggled `hidden`).
 *                              Esc and links inside it close it.
 *   [data-ars-action="…"]      Buttons: toggle, prev, next, back, forward, rate, mute, panel (open / close the panel),
 *                              clear (empty the queue; the next page load starts again from the latest episodes).
 *                              back / forward skip the seconds in data-ars-seconds.
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
$bigButton = 'btn btn-ghost btn-circle';
?>
<div class="ars-player group" data-ars-player data-state="paused" hidden role="region" aria-label="<?php esc_attr_e('Audio player', 'a-ripple-song'); ?>">

    <?php /* ---------- Bar ---------- */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-2 pb-2 sm:px-4 sm:pb-4" data-ars-bar>
        <div data-ars-width="box" data-ars-surface class="mx-auto flex h-16 max-w-6xl items-center gap-2 rounded-box border border-base-300 bg-base-100 px-2 text-base-content shadow-lg sm:gap-3 sm:px-3">
            <button type="button" class="flex min-w-0 flex-1 items-center gap-3 text-left md:w-64 md:flex-none" data-ars-action="panel" aria-label="<?php esc_attr_e('Open Now Playing', 'a-ripple-song'); ?>">
                <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-field bg-base-200">
                    <img class="size-full object-cover" alt="" data-ars-src="cover" data-ars-show="cover">
                    <span class="opacity-50" data-ars-show="nocover"><?php Icon::render('podcast', 'size-5'); ?></span>
                </span>
                <span class="min-w-0">
                    <span class="flex items-center gap-2 text-xs">
                        <span class="badge badge-xs badge-soft group-data-[state=playing]:badge-primary group-data-[state=error]:badge-error" data-ars-text="status"></span>
                        <span class="opacity-60" data-ars-text="number"></span>
                    </span>
                    <span class="block truncate text-sm font-semibold" data-ars-text="title"></span>
                </span>
            </button>

            <div class="flex shrink-0 items-center gap-0.5 sm:gap-1">
                <button type="button" class="<?php echo esc_attr($iconButton); ?> hidden sm:inline-flex" data-ars-action="prev" aria-label="<?php esc_attr_e('Previous episode', 'a-ripple-song'); ?>"><?php Icon::render('skip-back', 'size-4'); ?></button>
                <button type="button" class="<?php echo esc_attr($iconButton); ?> hidden sm:inline-flex" data-ars-action="back" data-ars-seconds="15" aria-label="<?php esc_attr_e('Back 15 seconds', 'a-ripple-song'); ?>"><?php Icon::render('rotate-ccw', 'size-4'); ?></button>
                <button type="button" class="btn btn-primary btn-circle" data-ars-action="toggle" aria-label="<?php esc_attr_e('Play', 'a-ripple-song'); ?>">
                    <span data-ars-show="paused"><?php Icon::render('play', 'size-5 fill-current'); ?></span>
                    <span data-ars-show="playing" hidden><?php Icon::render('pause', 'size-5 fill-current'); ?></span>
                    <span class="loading loading-spinner loading-sm" data-ars-show="loading" hidden></span>
                </button>
                <button type="button" class="<?php echo esc_attr($iconButton); ?> hidden sm:inline-flex" data-ars-action="forward" data-ars-seconds="30" aria-label="<?php esc_attr_e('Forward 30 seconds', 'a-ripple-song'); ?>"><?php Icon::render('rotate-cw', 'size-4'); ?></button>
                <button type="button" class="<?php echo esc_attr($iconButton); ?> hidden sm:inline-flex" data-ars-action="next" aria-label="<?php esc_attr_e('Next episode', 'a-ripple-song'); ?>"><?php Icon::render('skip-forward', 'size-4'); ?></button>
            </div>

            <?php
            (new Template())->renderThemeable('player/parts/progress', [
                'hooks' => 'data',
                'time' => 'opacity-70',
                'timeEnd' => 'opacity-50',
                'spectrum' => ['data-spectrum' => 'bars', 'class' => 'hidden h-8 w-24 shrink-0 xl:block'],
            ]);
            ?>

            <div class="hidden shrink-0 items-center gap-1 lg:flex">
                <button type="button" class="btn btn-ghost btn-sm font-semibold tabular-nums" data-ars-action="rate" aria-label="<?php esc_attr_e('Playback speed', 'a-ripple-song'); ?>"><span data-ars-text="rate">1×</span></button>
                <button type="button" class="<?php echo esc_attr($iconButton); ?>" data-ars-action="mute" aria-label="<?php esc_attr_e('Mute', 'a-ripple-song'); ?>">
                    <span data-ars-show="unmuted"><?php Icon::render('volume-2', 'size-4'); ?></span>
                    <span data-ars-show="muted" hidden><?php Icon::render('volume-x', 'size-4'); ?></span>
                </button>
                <input type="range" class="range range-primary range-xs w-20" min="0" max="1" step="0.05" value="0.8" data-ars-volume aria-label="<?php esc_attr_e('Volume', 'a-ripple-song'); ?>">
            </div>

            <button type="button" class="btn btn-soft btn-square btn-sm shrink-0" data-ars-action="panel" aria-label="<?php esc_attr_e('Open Now Playing and queue', 'a-ripple-song'); ?>" aria-expanded="false"><?php Icon::render('chevron-up', 'size-5'); ?></button>
        </div>
    </div>

    <?php /* ---------- Full-screen "Now Playing" panel ---------- */ ?>
    <dialog class="m-0 size-full max-h-none max-w-none overflow-y-auto bg-base-200 p-0 text-base-content opacity-100 transition-[opacity,translate] duration-200 backdrop:bg-transparent starting:open:translate-y-6 starting:open:opacity-0 sm:p-4" data-ars-panel aria-label="<?php esc_attr_e('Now Playing', 'a-ripple-song'); ?>">
        <div data-ars-panel-width="box" class="mx-auto min-h-full max-w-6xl bg-base-100 p-4 sm:rounded-box sm:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold tracking-widest uppercase opacity-60"><?php esc_html_e('Now Playing', 'a-ripple-song'); ?></p>
                <button type="button" class="btn btn-soft btn-square" data-ars-action="panel" aria-label="<?php esc_attr_e('Close Now Playing', 'a-ripple-song'); ?>"><?php Icon::render('chevron-down', 'size-5'); ?></button>
            </div>

            <div class="mt-6 grid grid-cols-1 items-start gap-8 lg:grid-cols-2 lg:gap-12">
                <div class="mx-auto flex w-full max-w-md min-w-0 flex-col items-center text-center">
                    <div class="grid aspect-square w-full max-w-72 place-items-center overflow-hidden rounded-box bg-base-200 shadow-lg sm:max-w-80">
                        <img class="size-full object-cover" alt="" data-ars-src="cover" data-ars-show="cover">
                        <span class="opacity-40" data-ars-show="nocover"><?php Icon::render('podcast', 'size-16'); ?></span>
                    </div>

                    <div class="mt-6 flex items-center justify-center gap-2 text-sm">
                        <span class="badge badge-sm badge-soft group-data-[state=playing]:badge-primary group-data-[state=error]:badge-error" data-ars-text="status"></span>
                        <span class="opacity-60" data-ars-text="number"></span>
                    </div>
                    <a class="mt-2 block text-2xl leading-snug font-semibold hover:underline" data-ars-href="url" data-ars-text="title"></a>

                    <input type="range" class="range range-primary range-sm mt-6 w-full" min="0" max="0" step="1" value="0" data-ars-seek aria-label="<?php esc_attr_e('Playback position', 'a-ripple-song'); ?>">
                    <div class="mt-2 flex w-full justify-between text-xs tabular-nums opacity-60">
                        <span data-ars-text="position">00:00</span>
                        <span data-ars-text="duration">00:00</span>
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-2 sm:gap-4">
                        <button type="button" class="<?php echo esc_attr($bigButton); ?>" data-ars-action="prev" aria-label="<?php esc_attr_e('Previous episode', 'a-ripple-song'); ?>"><?php Icon::render('skip-back', 'size-5'); ?></button>
                        <button type="button" class="<?php echo esc_attr($bigButton); ?>" data-ars-action="back" data-ars-seconds="15" aria-label="<?php esc_attr_e('Back 15 seconds', 'a-ripple-song'); ?>"><?php Icon::render('rotate-ccw', 'size-5'); ?></button>
                        <button type="button" class="btn btn-primary btn-circle size-16" data-ars-action="toggle" aria-label="<?php esc_attr_e('Play', 'a-ripple-song'); ?>">
                            <span data-ars-show="paused"><?php Icon::render('play', 'size-7 fill-current'); ?></span>
                            <span data-ars-show="playing" hidden><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
                            <span class="loading loading-spinner loading-md" data-ars-show="loading" hidden></span>
                        </button>
                        <button type="button" class="<?php echo esc_attr($bigButton); ?>" data-ars-action="forward" data-ars-seconds="30" aria-label="<?php esc_attr_e('Forward 30 seconds', 'a-ripple-song'); ?>"><?php Icon::render('rotate-cw', 'size-5'); ?></button>
                        <button type="button" class="<?php echo esc_attr($bigButton); ?>" data-ars-action="next" aria-label="<?php esc_attr_e('Next episode', 'a-ripple-song'); ?>"><?php Icon::render('skip-forward', 'size-5'); ?></button>
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-3">
                        <button type="button" class="btn btn-soft btn-sm font-semibold tabular-nums" data-ars-action="rate" aria-label="<?php esc_attr_e('Playback speed', 'a-ripple-song'); ?>"><span data-ars-text="rate">1×</span></button>
                        <button type="button" class="<?php echo esc_attr($iconButton); ?>" data-ars-action="mute" aria-label="<?php esc_attr_e('Mute', 'a-ripple-song'); ?>">
                            <span data-ars-show="unmuted"><?php Icon::render('volume-2', 'size-4'); ?></span>
                            <span data-ars-show="muted" hidden><?php Icon::render('volume-x', 'size-4'); ?></span>
                        </button>
                        <input type="range" class="range range-primary range-xs w-32" min="0" max="1" step="0.05" value="0.8" data-ars-volume aria-label="<?php esc_attr_e('Volume', 'a-ripple-song'); ?>">
                    </div>
                </div>

                <section class="min-w-0 rounded-box bg-base-200 p-3 sm:p-4 lg:max-h-[calc(100dvh-10rem)] lg:overflow-y-auto">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="flex items-center gap-2 px-2 text-xs font-semibold tracking-widest uppercase opacity-60"><?php Icon::render('list-music', 'size-4'); ?><?php esc_html_e('Queue', 'a-ripple-song'); ?></h2>
                        <button type="button" class="btn btn-ghost btn-xs" data-ars-action="clear" data-ars-show="queue"><?php esc_html_e('Clear', 'a-ripple-song'); ?></button>
                    </div>
                    <p class="px-2 py-6 text-center text-sm opacity-60" data-ars-show="noqueue" hidden><?php esc_html_e('The queue is empty.', 'a-ripple-song'); ?></p>
                    <ul class="mt-3 space-y-1" data-ars-queue>
                        <template>
                            <li class="flex items-center gap-1">
                                <button type="button" class="flex min-w-0 flex-1 items-center gap-3 rounded-field px-2 py-2 text-left hover:bg-base-100 data-[ars-state=paused]:bg-base-100 data-[ars-state=paused]:text-primary data-[ars-state=playing]:bg-base-100 data-[ars-state=playing]:text-primary" data-ars-play>
                                    <img class="size-10 shrink-0 rounded-field bg-base-300 object-cover" alt="" data-ars-item="cover">
                                    <span class="min-w-0 flex-1 truncate text-sm font-medium" data-ars-item="title"></span>
                                    <span class="shrink-0 text-xs tabular-nums opacity-60" data-ars-item="duration"></span>
                                </button>
                                <button type="button" class="<?php echo esc_attr($iconButton); ?> shrink-0 opacity-60 hover:opacity-100" data-ars-remove aria-label="<?php esc_attr_e('Remove from queue', 'a-ripple-song'); ?>" title="<?php esc_attr_e('Remove from queue', 'a-ripple-song'); ?>"><?php Icon::render('x', 'size-4'); ?></button>
                            </li>
                        </template>
                    </ul>
                </section>
            </div>
        </div>
    </dialog>
</div>
