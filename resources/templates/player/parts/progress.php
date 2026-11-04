<?php
/**
 * Progress area of the player bars: elapsed time, the progress bar, total time, in one row, in every skin.
 * What the bar looks like follows the "Progress Bar & Spectrum" setting:
 *
 *   spectrum  range input plus the skin's live spectrum canvas on the right
 *   bar       range input only
 *   waveform  static waveform of the episode drawn by wavesurfer.js (resources/assets/js/player/waveform.js),
 *             with an invisible range input over it for seeking
 *
 * Included by the skin templates, each passing its own look:
 *   $class         Classes of the row (layout, visibility, text size).
 *   $time          Classes of the elapsed time; $timeEnd of the total time (defaults to $time).
 *   $range         Classes of the range input.
 *   $bar           Optional Closure printing the skin's own progress bar instead of the range input (terminal).
 *   $spectrum      Attributes of the spectrum canvas (data-spectrum, data-bar, data-color, class …).
 *   $spectrumWrap  Optional classes of an element around the spectrum canvas, which then fills it (cassette's VU face).
 *   $wave          Attributes of the waveform element (data-bar, data-gap, data-color); $waveClass its height.
 *   $hooks         "binder" (ars-* expressions, default) or "data" (the default skin's data-ars-* hooks).
 *
 * Themes can override this file with {theme}/a-ripple-song/player/parts/progress.php.
 * Every skin stylesheet scans this file, so its utility classes exist in all of them.
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.

use Jiejia\ARippleSong\Libraries\Html;
use Jiejia\ARippleSong\Settings\PlayerSetting;

$style ??= (new PlayerSetting())->progressStyle();
$hooks ??= 'binder';
$class ??= 'hidden min-w-0 flex-1 items-center gap-3 text-xs tabular-nums md:flex';
$time ??= 'opacity-70';
$timeEnd ??= $time;
$range ??= 'range range-primary range-xs';
$bar ??= null;
$spectrum ??= ['data-spectrum' => 'bars', 'class' => 'hidden h-8 w-24 shrink-0 xl:block'];
$spectrumWrap ??= '';
$wave ??= [];
$waveClass ??= 'h-8';

$binder = $hooks !== 'data';
$seek = ($binder
    ? ['min' => '0', 'step' => '1', 'ars-bind:max' => 'duration', 'ars-bind:value' => 'position', 'ars-on:input' => 'scrub($event.target.value)', 'ars-on:change' => 'commit($event.target.value)']
    : ['min' => '0', 'max' => '0', 'step' => '1', 'value' => '0', 'data-ars-seek' => true])
    + ['aria-label' => __('Playback position', 'a-ripple-song')];
$text = static fn (string $which): array => $binder ? ['ars-text' => "fmt({$which})"] : ['data-ars-text' => $which];
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Html::attributes() escapes.
?>
<div class="<?php echo esc_attr($class); ?>">
  <span<?php echo Html::attributes(['class' => $time] + $text('position')); ?>>00:00</span>
  <?php if ($style === 'waveform') : ?>
    <div class="ars-wave min-w-0 flex-1 <?php echo esc_attr($waveClass); ?>">
      <div<?php echo Html::attributes(['data-ars-waveform' => true] + $wave + ['data-bar' => '2', 'data-gap' => '2', 'class' => 'size-full', 'aria-hidden' => 'true']); ?>></div>
      <input type="range"<?php echo Html::attributes(['class' => 'ars-wave-seek'] + $seek); ?>>
    </div>
  <?php elseif ($bar instanceof Closure) : ?>
    <?php $bar(); ?>
  <?php else : ?>
    <input type="range"<?php echo Html::attributes(['class' => $range . ' min-w-0 flex-1'] + $seek); ?>>
  <?php endif; ?>
  <span<?php echo Html::attributes(['class' => $timeEnd] + $text('duration')); ?>>00:00</span>
  <?php if ($style === 'spectrum') : ?>
    <?php if ($spectrumWrap !== '') : ?>
      <div class="<?php echo esc_attr($spectrumWrap); ?>" aria-hidden="true"><canvas<?php echo Html::attributes(['class' => 'block size-full'] + $spectrum); ?>></canvas></div>
    <?php else : ?>
      <canvas<?php echo Html::attributes($spectrum + ['aria-hidden' => 'true']); ?>></canvas>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php
// phpcs:enable
