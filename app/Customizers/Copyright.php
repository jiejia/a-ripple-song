<?php

namespace App\Customizers;

use App\Abstracts\CustomizerAbstract;
use App\Theme;
use WP_Customize_Manager;

/**
 * Registers footer copyright Customizer options.
 */
class Copyright extends CustomizerAbstract
{
    /**
     * Return configured footer copyright text.
     */
    public static function getCopyright(): string
    {
        return (string) get_theme_mod(Theme::fieldKey('footer_copyright'), '');
    }

    /**
     * Register the footer copyright Customizer field.
     *
     * @param WP_Customize_Manager $wpCustomize WordPress Customizer manager.
     */
    public function register(WP_Customize_Manager $wpCustomize): void
    {
        $wpCustomize->add_section(Theme::fieldKey('footer'), [
            'title' => __('Footer', 'a-ripple-song'),
            'priority' => 160,
        ]);

        $wpCustomize->add_setting(Theme::fieldKey('footer_copyright'), [
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'refresh',
        ]);

        $wpCustomize->add_control(Theme::fieldKey('footer_copyright'), [
            'section' => Theme::fieldKey('footer'),
            'label' => __('Footer Copyright', 'a-ripple-song'),
            'description' => __('Overrides the footer copyright line. Leave empty to use the default.', 'a-ripple-song'),
            'type' => 'text',
        ]);
    }
}
