<?php
/**
 * Player skin "花瓣" (ars-petal), converted from the ~/workspace/podcast/petal prototype.
 *
 * Bindings use the plugin's small Alpine-like binder (resources/assets/js/player/binder.js):
 * ars-text, ars-show, ars-bind:attr, ars-on:event, ars-for, ars-data, ars-transition:*, ars-cloak.
 * Expressions run against the player store, e.g. toggle(), fmt(position), current?.title.
 * The .seek-s sliders read their fill from the --p custom property (0–100%), bound with ars-bind:style.
 * Themes can override this file with {theme}/a-ripple-song/player/skins/ars-petal.php.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Icon;
use Jiejia\ARippleSong\Libraries\Template;
?>
<div class="ars-player" data-ars-player data-ars-skin="ars-petal" hidden>
    <?php /* ============ 底部：浮起的玻璃播放条（常驻） ============ */ ?>
    <div class="fixed inset-x-0 bottom-0 z-[9990] px-3 pb-3 md:pb-5" role="region" data-ars-bar aria-label="音频播放器">
      <div data-ars-width="box" class="player-bar mx-auto max-w-3xl rounded-[1.75rem] p-3 @5xl:p-2 @5xl:pr-3" ars-bind:style="`--p:${progress}%`">
        <?php (new Template())->renderThemeable('player/parts/mini', ['surface' => false, 'handle' => 'player-bar rounded-t-xl border-b-0 text-base-content']); ?>
        <?php /* Narrow bar: cover + title, the progress row, then list / speed, prev-play-next, mute. From @5xl on everything sits in one row. */ ?>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 @5xl:flex-nowrap @5xl:gap-4">
          <?php (new Template())->renderThemeable('player/parts/info', [
              'width' => '@5xl:w-64',
              'cover' => 'rounded-[1.1rem] bg-base-300',
              'title' => 'font-semibold hover:underline',
              'meta' => 'mute',
          ]); ?>
          <div class="order-5 flex shrink-0 items-center gap-1 @5xl:order-none @5xl:gap-2">
            <button type="button" class="btn-plain inline-flex size-8 rounded-full @5xl:size-9" ars-on:click="prev()" aria-label="上一集"><?php Icon::render('skip-back', 'size-4 @5xl:size-[1.05rem]'); ?></button>
            <button type="button" class="btn btn-primary btn-circle size-10 shrink-0 @5xl:size-12 shadow-lg shadow-primary/30" ars-on:click="toggle()" ars-bind:aria-label="playing ? '暂停' : '播放'" aria-label="播放">
              <span ars-show="!playing"><?php Icon::render('play', 'ml-0.5 size-[1.15rem] fill-current'); ?></span>
              <span ars-show="playing" ars-cloak><?php Icon::render('pause', 'size-[1.15rem] fill-current'); ?></span>
            </button>
            <button type="button" class="btn-plain inline-flex size-8 rounded-full @5xl:size-9" ars-on:click="next()" aria-label="下一集"><?php Icon::render('skip-forward', 'size-4 @5xl:size-[1.05rem]'); ?></button>
          </div>
          <?php
          (new Template())->renderThemeable('player/parts/progress', [
              'class' => 'order-3 flex w-full min-w-0 flex-wrap items-center gap-x-3 gap-y-1 text-xs tabular-nums @5xl:order-none @5xl:w-auto @5xl:flex-1 @5xl:flex-nowrap',
              'spectrumTop' => ['data-spectrum' => 'mirror', 'data-color' => 'secondary', 'data-bar' => '2', 'data-gap' => '3', 'class' => 'block h-8 w-full @5xl:hidden'],
              'time' => 'mono mute',
              'range' => 'seek-s',
              'spectrum' => ['data-spectrum' => 'mirror', 'data-color' => 'secondary', 'data-bar' => '2', 'data-gap' => '3', 'class' => 'hidden h-8 w-20 shrink-0 @5xl:block'],
              'wave' => ['data-color' => 'primary'],
          ]);
          (new Template())->renderThemeable('player/parts/volume', ['class' => 'order-4 flex flex-1 shrink-0 items-center justify-start gap-1 @5xl:order-none @5xl:flex-none', 'button' => 'btn-plain size-8 rounded-full @5xl:size-9', 'icon' => 'size-4 @5xl:size-[1.05rem]', 'range' => 'range range-primary range-xs']);
          ?>
          <div class="order-6 flex flex-1 items-center justify-end gap-1 @5xl:contents">
            <button type="button" class="chip mono !h-7 !w-14 shrink-0 justify-center !px-0 text-xs @5xl:!h-8" ars-on:click="cycleRate()" aria-label="播放速度" ars-text="`${rate}x`">1x</button>
            <button type="button" class="btn-plain size-8 shrink-0 rounded-full @5xl:size-9" ars-on:click="expanded = true" aria-label="播放队列"><?php Icon::render('list-music', 'size-[1.1rem] @5xl:size-5'); ?></button>
          </div>
        </div>
      </div>
    </div>

    <?php (new Template())->renderThemeable('player/parts/queue', [
        'title' => 'display truncate text-xl', 'meta' => 'mute', 'sub' => 'mute', 'cover' => 'rounded-[1.1rem] bg-base-300', 'trash' => 'btn-plain size-8 rounded-full',
    ]); ?>
    <?php (new Template())->renderThemeable('player/parts/resume', [
        'panel' => 'panel shadow-xl', 'heading' => 'display font-bold', 'accept' => 'btn btn-primary rounded-full', 'cancel' => 'btn-gray h-10 gap-2 rounded-full text-sm font-medium',
    ]); ?>
</div>
