<?php
/**
 * Player skin "广播站" (ars-broadcast), converted from the ~/workspace/podcast/broadcast prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-broadcast.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-broadcast" hidden>
    <?php /* ============ 播放器（常驻，固定深色） ============ */ ?>
    <div data-ars-theme="dark" class="fixed inset-x-0 bottom-0 z-[9990]" role="region" data-ars-bar aria-label="音频播放器">
      <?php /* No max-width: with the style's own width the bar runs edge to edge like the prototype. */ ?>
      <div data-ars-width="box" data-ars-surface class="mx-auto border-x-2 border-t-2 border-base-content bg-base-100 text-base-content">
        <div class="flex flex-wrap items-stretch @5xl:min-h-[4.5rem] @5xl:flex-nowrap">
        <a ars-bind:href="current?.url" class="order-1 flex min-h-[4.5rem] min-w-0 basis-full items-center gap-3 border-b-2 border-base-content/25 pr-3 text-left hover:bg-base-200 @5xl:order-none @5xl:basis-auto @5xl:w-80 @5xl:flex-none @5xl:border-b-0 @5xl:border-r-2 @5xl:h-auto" ars-bind:aria-label="current?.title">
          <img ars-bind:src="current?.cover" alt="" class="w-[4.5rem] shrink-0 self-stretch border-r-2 border-base-content/25 object-cover">
          <span class="min-w-0">
            <span class="label block text-accent" ars-text="error || `EP.${current?.number} · ${loading ? 'Loading' : (playing ? 'On Air' : 'Paused')}`"></span>
            <span class="block break-words text-sm font-bold" ars-text="current?.title"></span>
          </span>
        </a>
        <div class="order-5 flex h-14 items-stretch justify-center @5xl:order-none @5xl:h-auto">
        <div class="contents">
          <button type="button" class="grid w-12 place-items-center hover:bg-base-200" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4'); ?></button>
        </div>
        <button type="button" class="grid w-[4.5rem] shrink-0 place-items-center bg-primary text-primary-content hover:bg-accent hover:text-accent-content" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
          <span ars-show="!playing"><?php Icon::render('play', 'size-7 fill-current'); ?></span>
          <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-7 fill-current'); ?></span>
        </button>
        <div class="contents">
          <button type="button" class="grid w-12 place-items-center border-r-2 border-base-content/25 hover:bg-base-200" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4'); ?></button>
        </div>
        </div>
        <?php
        (new Template())->renderThemeable('player/parts/progress', [
            'class' => 'order-3 flex w-full min-w-0 basis-full flex-wrap items-center gap-x-3 gap-y-1 border-b-2 border-base-content/25 px-4 py-2 font-mono @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:basis-auto @5xl:flex-nowrap @5xl:border-b-0 @5xl:px-5 @5xl:py-0',
            'spectrumTop' => ['data-spectrum' => 'led', 'data-bar' => '4', 'data-gap' => '2', 'data-color' => 'accent', 'class' => 'block h-10 w-full @5xl:hidden'],
            'time' => 'text-base font-semibold text-accent', 'timeEnd' => 'text-sm text-base-content/60', 'range' => 'range range-accent range-xs',
            'spectrum' => ['data-spectrum' => 'led', 'data-bar' => '4', 'data-gap' => '2', 'data-color' => 'accent', 'class' => 'hidden h-10 w-28 shrink-0 @6xl:block'],
            'wave' => ['data-color' => 'accent'], 'waveClass' => 'h-10',
        ]);
        ?>
        <?php (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex h-14 flex-1 items-stretch @5xl:h-auto @5xl:items-center shrink-0 justify-start gap-1 @5xl:order-8 @5xl:flex-none @5xl:border-l-2 @5xl:border-base-content/25', 'button' => 'grid h-full w-12 place-items-center hover:bg-base-200', 'range' => 'range range-accent range-xs mr-4']); ?>
        <div class="order-6 flex flex-1 h-14 items-stretch @5xl:h-auto justify-end gap-0 @5xl:contents">
          <button type="button" class="label w-16 hover:bg-base-200 @5xl:order-7 @5xl:border-l-2 @5xl:border-base-content/25" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
        <button type="button" class="grid w-14 place-items-center border-l-2 border-base-content/25 hover:bg-base-200 @5xl:order-9" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-4'); ?></button>
        </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'theme' => 'dark', 'panel' => 'border-r-2 border-base-content bg-base-100 text-base-content', 'divider' => 'border-t-2 border-base-content', 'title' => 'label px-5 py-4 text-lg text-accent', 'meta' => 'font-mono text-sm text-base-content/60', 'clear' => 'label hover:text-accent', 'row' => 'border-b-2 border-base-content/25 hover:bg-base-200', 'rowOn' => 'bg-primary text-primary-content hover:bg-primary', 'cover' => 'bg-base-300', 'name' => 'font-bold', 'nameOn' => '', 'sub' => 'font-mono text-xs opacity-70', 'trash' => 'grid size-9 place-items-center hover:bg-accent hover:text-accent-content',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'theme' => 'dark', 'panel' => 'border-2 border-base-content bg-base-100', 'icon' => 'text-accent', 'heading' => 'font-bold', 'accept' => 'btn rounded-none border-0 bg-primary text-primary-content', 'cancel' => 'btn btn-ghost rounded-none border-2 border-base-content/25',
    ]); ?>
</div>
