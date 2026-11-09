<?php
/**
 * Player skin "集市" (ars-bazaar), styled after the ~/workspace/podcast/bazaar prototype; its controls are the plugin's.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * The .seek-s sliders read their fill from the --p custom property (0–100%), bound with ars-bind:style.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-bazaar.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-bazaar" hidden>
    <?php /* ============ 底部：大圆角浮层播放条（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-3 pb-3 md:pb-5" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" data-ars-surface class="player-bar mx-auto max-w-6xl rounded-[1.75rem] bg-base-100 text-base-content" ars-bind:style="`--p:${progress}%`">
        <?php (new Template())->renderThemeable('player/parts/mini'); ?>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 p-3 @5xl:flex-nowrap @5xl:gap-x-4 @5xl:py-2 @5xl:pl-2 @5xl:pr-3">
          <?php (new Template())->renderThemeable('player/parts/info', [
              'width' => '@5xl:w-64',
              'cover' => 'rounded-[1.1rem] bg-base-300',
              'title' => 'font-semibold hover:underline',
              'meta' => 'mute',
          ]); ?>
          <div class="order-5 flex shrink-0 items-center gap-1 @5xl:order-none">
            <button type="button" class="btn-plain size-8 rounded-full @5xl:size-9" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-[1.05rem]'); ?></button>
            <button type="button" class="btn-black size-10 shrink-0 rounded-full @5xl:size-12" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
              <span ars-show="!playing && !loading"><?php Icon::render('play', 'ml-0.5 size-[1.1rem] fill-current'); ?></span>
              <span ars-show="playing && !loading" ars-cloak><?php Icon::render('pause', 'size-[1.1rem] fill-current'); ?></span>
              <span class="loading loading-spinner size-[1.1rem]" ars-show="loading" ars-cloak></span>
            </button>
            <button type="button" class="btn-plain size-8 rounded-full @5xl:size-9" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-[1.05rem]'); ?></button>
          </div>
          <?php
          (new Template())->renderThemeable('player/parts/progress', [
              'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 text-xs tabular-nums @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
              'spectrumTop' => ['data-spectrum' => 'mirror', 'data-color' => 'primary', 'data-bar' => '2', 'data-gap' => '3', 'class' => 'block h-8 w-full @5xl:hidden'],
              'time' => 'mute',
              'range' => 'seek-s',
              'spectrum' => ['data-spectrum' => 'mirror', 'data-color' => 'primary', 'data-bar' => '2', 'data-gap' => '3', 'class' => 'hidden h-8 w-20 shrink-0 @5xl:block'],
              'wave' => ['data-color' => 'primary'],
          ]);
          (new Template())->renderThemeable('player/parts/volume', ['rate' => 'btn-gray h-8 w-14 shrink-0 rounded-full text-xs font-medium tabular-nums', 'class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-8 @5xl:flex-none', 'button' => 'btn-plain size-8 rounded-full @5xl:size-9', 'icon' => 'size-[1.05rem]', 'range' => 'seek-s']);
          ?>
          <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
            <button type="button" class="btn-gray h-8 w-14 shrink-0 rounded-full text-xs font-medium tabular-nums @5xl:order-7 @max-5xl:!hidden" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}×`">1×</button>
            <button type="button" class="btn-plain size-8 shrink-0 rounded-full @5xl:order-9 @5xl:size-9" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-5'); ?></button>
            <?php (new Template())->renderThemeable('player/parts/collapse', ['class' => 'btn-plain size-8 shrink-0 rounded-full @5xl:size-9']); ?>
          </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'panel' => 'border-r line bg-base-100 shadow-2xl', 'divider' => 'border-t line', 'title' => 'display text-base', 'meta' => 'mute', 'clear' => 'mute hover:text-base-content', 'row' => 'rounded-2xl hover:bg-base-200', 'rowOn' => 'bg-base-200', 'cover' => 'rounded-[1.1rem] bg-base-300', 'name' => 'font-semibold', 'sub' => 'mute', 'trash' => 'btn-plain size-8 rounded-full',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'panel shadow-xl', 'icon' => 'rounded-full bg-base-200', 'heading' => 'display', 'accept' => 'btn-black h-10 gap-2 rounded-full text-sm', 'cancel' => 'btn-gray h-10 gap-2 rounded-full text-sm font-medium',
    ]); ?>
</div>
