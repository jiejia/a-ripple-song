<?php
/**
 * Episode player while the global player is off: WordPress's own audio player plus the play count.
 * Added to single episode pages by Player\EpisodeMeta in place of episode/meta/{skin}.php.
 *
 * Themes can override this file with {theme}/a-ripple-song/episode/audio.php. Keep data-ars-plays-url on an
 * element around the audio: resources/assets/js/episode-audio.js posts one play to it on the first play.
 *
 * @var int    $postId          Episode ID.
 * @var string $position        "before" or "after" the content.
 * @var string $audio           Player markup from wp_audio_shortcode().
 * @var string $playsUrl        REST URL that counts a play of this episode.
 * @var string $playsText       Play count text, e.g. "1,234 plays".
 * @var string $playsAttributes Escaped attributes of the play count element; episode-audio.js updates its text after a
 *                              counted play (data-ars-plays, data-ars-plays-one / -other).
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Templates run inside Template::include(), so their variables are local.
?>
<div class="ars-episode-audio ars-episode-audio--<?php echo esc_attr($position); ?>" data-ars-plays-url="<?php echo esc_url($playsUrl); ?>">
    <?php echo $audio; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built and escaped by wp_audio_shortcode(). ?>
    <p class="ars-episode-audio__plays"><span<?php echo $playsAttributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Html::attributes(). ?>><?php echo esc_html($playsText); ?></span></p>
</div>
