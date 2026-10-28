<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Plugin;

/**
 * Enqueues the CSS and JS used by rendered fields.
 */
final class FieldAssets
{
    /**
     * Handle shared by the style and the script.
     */
    private const HANDLE = Plugin::PREFIX . 'fields';

    /**
     * Enqueue field styles, scripts and the media library.
     *
     * @return void
     */
    public static function enqueue(): void
    {
        wp_enqueue_media();

        wp_enqueue_style(
            self::HANDLE,
            A_RIPPLE_SONG_PLUGIN_URL . 'resources/css/fields.css',
            ['dashicons'],
            self::version('resources/css/fields.css')
        );

        wp_enqueue_script(
            self::HANDLE,
            A_RIPPLE_SONG_PLUGIN_URL . 'resources/js/fields.js',
            ['jquery', 'jquery-ui-sortable'],
            self::version('resources/js/fields.js'),
            true
        );

        wp_localize_script(self::HANDLE, 'arsFieldsL10n', [
            /* translators: %s: comma separated list of field labels. */
            'invalidNotice' => __('Please fix these fields before saving: %s', 'a-ripple-song'),
            'required' => __('This field is required.', 'a-ripple-song'),
            /* translators: 1: image width, 2: image height. */
            'currentSize' => __('Current: %1$d × %2$d px.', 'a-ripple-song'),
            'imageLoadError' => __('The image could not be loaded.', 'a-ripple-song'),
        ]);
    }

    /**
     * Use the file modification time as version so edits bust the browser cache.
     *
     * @param string $path Path relative to the plugin directory.
     * @return string
     */
    private static function version(string $path): string
    {
        $mtime = @filemtime(Plugin::DIR . $path);

        return $mtime !== false ? (string) $mtime : Plugin::VERSION;
    }
}
