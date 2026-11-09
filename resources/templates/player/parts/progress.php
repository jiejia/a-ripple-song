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
 *   $bar           Optional Closure printing the skin's own progress bar instead of the range input (ars-terminal).
 *   $spectrum      Attributes of the spectrum canvas (data-spectrum, data-bar, data-color, class …).
 *   $spectrumTop   Optional attributes of a second spectrum canvas printed above the row (full width), for narrow bars
 *                  that hide the one beside the progress bar; hide it again with a class when the bar is wide.
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
$class ??= 'hidden min-w-0 flex-1 items-center gap-3 text-xs tabular-nums @2xl:flex';
$time ??= 'opacity-70';
$timeEnd ??= $time;
$range ??= 'range range-primary range-xs';
$bar ??= null;
$spectrum ??= ['data-spectrum' => 'bars', 'class' => 'hidden h-8 w-24 shrink-0 xl:block'];
$spectrumTop ??= null;
$spectrumWrap ??= '';
$wave ??= [];
$waveClass ??= 'h-8';

$binder = $hooks !== 'data';
// While the audio loads (root [data-state=loading]) the spectrum gives way to a daisyUI loading ring, same box and breakpoints.
// Class names built below, spelled out so Tailwind finds them: [.ars-player[data-state=loading]_&]:flex [.ars-player[data-state=loading]_&]:!hidden
// @2xl:[.ars-player[data-state=loading]_&]:flex @3xl:[.ars-player[data-state=loading]_&]:flex @4xl:[.ars-player[data-state=loading]_&]:flex @5xl:[.ars-player[data-state=loading]_&]:flex @6xl:[.ars-player[data-state=loading]_&]:flex @[70rem]:[.ars-player[data-state=loading]_&]:flex @xl:[.ars-player[data-state=loading]_&]:flex
// @2xl:!hidden @3xl:!hidden @4xl:!hidden @5xl:!hidden @6xl:!hidden
$loadingRing = static function (string $class, string $label = ''): string {
    $tokens = preg_split('/\s+/', trim($class)) ?: [];
    $size = array_filter($tokens, static fn (string $t): bool => (bool) preg_match('/^(?:h|w|size|min-w|flex)-\S+$|^shrink-0$/', $t));
    $show = 'hidden [.ars-player[data-state=loading]_&]:flex';
    foreach ($tokens as $t) {
        if (preg_match('/^(@[^:]+):(?:block|flex)$/', $t, $m)) {
            $show = 'hidden ' . $m[1] . ':[.ars-player[data-state=loading]_&]:flex'; // spectrum appears from this width on
        } elseif (preg_match('/^(@\w+):hidden$/', $t, $m)) {
            $show .= ' ' . $m[1] . ':!hidden'; // narrow-bar spectrum, hidden again from this width on
        }
    }
    $text = $label !== '' ? '<span class="truncate text-sm opacity-70">' . esc_html($label) . '</span>' : '';

    return '<span class="' . esc_attr($show . ' items-center justify-center gap-2 ' . implode(' ', $size)) . '" aria-hidden="true"><span class="loading loading-ring loading-md shrink-0"></span>' . $text . '</span>';
};
$seek = ($binder
    ? ['min' => '0', 'step' => '1', 'ars-bind:max' => 'duration', 'ars-bind:value' => 'position', 'ars-on:input' => 'scrub($event.target.value)', 'ars-on:change' => 'commit($event.target.value)']
    : ['min' => '0', 'max' => '0', 'step' => '1', 'value' => '0', 'data-ars-seek' => true])
    + ['aria-label' => __('Playback position', 'a-ripple-song')];
// Elapsed time is written in the episode's format (HH:MM:SS once it runs an hour or more, else MM:SS).
$text = static fn (string $which, string $expr): array => $binder ? ['ars-text' => $expr] : ['data-ars-text' => $which];
// The elapsed time keeps one width while it counts or is dragged: an invisible "00:00" (or "00:00:00") sizes the box.
// It sits at the box's end, against the progress bar, since fonts without tabular figures (DM Sans) leave the box wider than most times.
$timeBox = 'shrink-0 whitespace-nowrap tabular-nums';
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Html::attributes() escapes.
?>
<div class="<?php echo esc_attr($class); ?>">
  <?php if ($style === 'spectrum' && $spectrumTop !== null) : ?>
    <canvas<?php echo Html::attributes(['class' => trim(($spectrumTop['class'] ?? '') . ' [.ars-player[data-state=loading]_&]:!hidden'), 'aria-hidden' => 'true'] + $spectrumTop); ?>></canvas>
    <?php echo $loadingRing($spectrumTop['class'] ?? '', __('Loading audio', 'a-ripple-song')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts. ?>
  <?php endif; ?>
  <span class="<?php echo esc_attr(trim($time . ' ' . $timeBox)); ?>"><span class="inline-grid">
    <span<?php echo Html::attributes(['class' => 'invisible col-start-1 row-start-1', 'aria-hidden' => 'true'] + $text('positionSizer', 'fmt(0, duration)')); ?>>00:00</span>
    <span<?php echo Html::attributes(['class' => 'col-start-1 row-start-1 text-end'] + $text('position', 'fmt(position, duration)')); ?>>00:00</span>
  </span></span>
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
  <span<?php echo Html::attributes(['class' => trim($timeEnd . ' ' . $timeBox)] + $text('duration', 'fmt(duration)')); ?>>00:00</span>
  <?php if ($style === 'spectrum') : ?>
    <?php if ($spectrumWrap !== '') : ?>
      <div class="<?php echo esc_attr($spectrumWrap . ' [.ars-player[data-state=loading]_&]:!hidden'); ?>" aria-hidden="true"><canvas<?php echo Html::attributes(['class' => 'block size-full'] + $spectrum); ?>></canvas></div>
      <?php echo $loadingRing($spectrumWrap); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts. ?>
    <?php else : ?>
      <canvas<?php echo Html::attributes(['class' => trim(($spectrum['class'] ?? '') . ' [.ars-player[data-state=loading]_&]:!hidden'), 'aria-hidden' => 'true'] + $spectrum); ?>></canvas>
      <?php echo $loadingRing($spectrum['class'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts. ?>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php
// phpcs:enable
