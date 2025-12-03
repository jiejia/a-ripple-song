<?php

/**
 * Custom Scripts Customizer Module.
 * 
 * Allows adding custom JavaScript code through the WordPress Customizer.
 * Supports complete script tags including external scripts like Google Analytics.
 */

namespace App\Customizer;

class CustomScripts
{
    /**
     * Section ID for the customizer.
     */
    const SECTION_ID = 'aripplesong_custom_scripts';

    /**
     * Setting IDs.
     */
    const SETTING_HEADER_JS = 'aripplesong_header_js';
    const SETTING_FOOTER_JS = 'aripplesong_footer_js';

    /**
     * Register customizer settings and controls.
     *
     * @param \WP_Customize_Manager $wp_customize
     * @return void
     */
    public function register($wp_customize)
    {
        // Add Custom Scripts section
        $wp_customize->add_section(self::SECTION_ID, [
            'title'       => __('Custom Scripts', 'sage'),
            'description' => __('Add custom JavaScript code to your site. Use with caution - incorrect code may break your site.', 'sage'),
            'priority'    => 200,
        ]);

        // Header scripts
        $wp_customize->add_setting(self::SETTING_HEADER_JS, [
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitizeJS'],
            'transport'         => 'postMessage',
        ]);

        $wp_customize->add_control(self::SETTING_HEADER_JS, [
            'label'       => __('Header Scripts', 'sage'),
            'description' => __('Scripts to be added in the &lt;head&gt; section. You can include complete &lt;script&gt; tags.', 'sage'),
            'section'     => self::SECTION_ID,
            'type'        => 'textarea',
            'input_attrs' => [
                'placeholder' => '<script async src="https://example.com/script.js"></script>',
                'rows'        => 8,
            ],
        ]);

        // Footer scripts
        $wp_customize->add_setting(self::SETTING_FOOTER_JS, [
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitizeJS'],
            'transport'         => 'postMessage',
        ]);

        $wp_customize->add_control(self::SETTING_FOOTER_JS, [
            'label'       => __('Footer Scripts', 'sage'),
            'description' => __('Scripts to be added before &lt;/body&gt;. You can include complete &lt;script&gt; tags.', 'sage'),
            'section'     => self::SECTION_ID,
            'type'        => 'textarea',
            'input_attrs' => [
                'placeholder' => '<script>console.log("Hello");</script>',
                'rows'        => 8,
            ],
        ]);

        // Register output hooks
        $this->registerOutputHooks();
    }

    /**
     * Register hooks for outputting scripts on the frontend.
     *
     * @return void
     */
    protected function registerOutputHooks()
    {
        // Output header scripts
        add_action('wp_head', [$this, 'outputHeaderScripts'], 999);

        // Output footer scripts
        add_action('wp_footer', [$this, 'outputFooterScripts'], 999);
    }

    /**
     * Output header scripts.
     *
     * @return void
     */
    public function outputHeaderScripts()
    {
        $header_js = get_theme_mod(self::SETTING_HEADER_JS, '');

        if (!empty(trim($header_js))) {
            echo "\n<!-- Custom Header Scripts -->\n";
            echo $header_js;
            echo "\n";
        }
    }

    /**
     * Output footer scripts.
     *
     * @return void
     */
    public function outputFooterScripts()
    {
        $footer_js = get_theme_mod(self::SETTING_FOOTER_JS, '');

        if (!empty(trim($footer_js))) {
            echo "\n<!-- Custom Footer Scripts -->\n";
            echo $footer_js;
            echo "\n";
        }
    }

    /**
     * Sanitize script code.
     * 
     * Note: We don't escape the code since it's entered by administrators
     * and needs to execute as-is. The Customizer already requires
     * appropriate capabilities to access. Users can include complete
     * <script> tags for services like Google Analytics.
     *
     * @param string $input
     * @return string
     */
    public function sanitizeJS($input)
    {
        // Return the input as-is to allow complete script tags
        return $input;
    }
}
