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
        self::enqueueStyle();
        // The media modal's cropper (image fields with "crop") draws its selection with imgAreaSelect.
        wp_enqueue_style('imgareaselect');

        wp_enqueue_script(
            self::HANDLE,
            A_RIPPLE_SONG_PLUGIN_URL . 'resources/assets/js/fields.js',
            ['jquery', 'jquery-ui-sortable', 'imgareaselect'],
            Plugin::assetVersion('resources/assets/js/fields.js'),
            true
        );

        wp_localize_script(self::HANDLE, 'arsFieldsL10n', [
            /* translators: %s: comma separated list of field labels. */
            'invalidNotice' => __('Please fix these fields before saving: %s', 'a-ripple-song'),
            'required' => __('This field is required.', 'a-ripple-song'),
            /* translators: 1: image width, 2: image height. */
            'currentSize' => __('Current: %1$d × %2$d px.', 'a-ripple-song'),
            'imageLoadError' => __('The image could not be loaded.', 'a-ripple-song'),
            /* translators: %s: current file type or size, e.g. "WEBP" or "830.5 KB". */
            'currentFile' => __('Current: %s.', 'a-ripple-song'),
            'cropTitle' => __('Choose Image', 'a-ripple-song'),
            'cropButton' => __('Select and Crop', 'a-ripple-song'),
        ]);
    }

    /**
     * Enqueue only the field styles, e.g. for a switch outside a form.
     *
     * @return void
     */
    public static function enqueueStyle(): void
    {
        wp_enqueue_style(
            self::HANDLE,
            A_RIPPLE_SONG_PLUGIN_URL . 'resources/assets/css/fields.css',
            ['dashicons'],
            Plugin::assetVersion('resources/assets/css/fields.css')
        );
    }
}
