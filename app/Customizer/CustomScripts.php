<?php

/**
 * Custom Scripts Customizer Module.
 * 
 * Allows adding custom JavaScript code and external script URLs
 * through the WordPress Customizer.
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
    const SETTING_EXTERNAL_JS = 'aripplesong_external_js';
    const SETTING_EXTERNAL_JS_POSITION = 'aripplesong_external_js_position';

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

        // Header inline JS
        $wp_customize->add_setting(self::SETTING_HEADER_JS, [
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitizeJS'],
            'transport'         => 'postMessage',
        ]);

        $wp_customize->add_control(self::SETTING_HEADER_JS, [
            'label'       => __('Header Inline JavaScript', 'sage'),
            'description' => __('JavaScript code to be added in the &lt;head&gt; section. Do not include &lt;script&gt; tags.', 'sage'),
            'section'     => self::SECTION_ID,
            'type'        => 'textarea',
            'input_attrs' => [
                'placeholder' => 'console.log("Hello from header");',
                'rows'        => 6,
            ],
        ]);

        // Footer inline JS
        $wp_customize->add_setting(self::SETTING_FOOTER_JS, [
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitizeJS'],
            'transport'         => 'postMessage',
        ]);

        $wp_customize->add_control(self::SETTING_FOOTER_JS, [
            'label'       => __('Footer Inline JavaScript', 'sage'),
            'description' => __('JavaScript code to be added before &lt;/body&gt;. Do not include &lt;script&gt; tags.', 'sage'),
            'section'     => self::SECTION_ID,
            'type'        => 'textarea',
            'input_attrs' => [
                'placeholder' => 'console.log("Hello from footer");',
                'rows'        => 6,
            ],
        ]);

        // External JS URLs
        $wp_customize->add_setting(self::SETTING_EXTERNAL_JS, [
            'default'           => '',
            'sanitize_callback' => [$this, 'sanitizeURLs'],
            'transport'         => 'postMessage',
        ]);

        $wp_customize->add_control(self::SETTING_EXTERNAL_JS, [
            'label'       => __('External JavaScript URLs', 'sage'),
            'description' => __('Enter external JavaScript URLs, one per line.', 'sage'),
            'section'     => self::SECTION_ID,
            'type'        => 'textarea',
            'input_attrs' => [
                'placeholder' => "https://example.com/script1.js\nhttps://example.com/script2.js",
                'rows'        => 4,
            ],
        ]);

        // External JS position
        $wp_customize->add_setting(self::SETTING_EXTERNAL_JS_POSITION, [
            'default'           => 'footer',
            'sanitize_callback' => [$this, 'sanitizePosition'],
            'transport'         => 'postMessage',
        ]);

        $wp_customize->add_control(self::SETTING_EXTERNAL_JS_POSITION, [
            'label'   => __('External Scripts Loading Position', 'sage'),
            'section' => self::SECTION_ID,
            'type'    => 'radio',
            'choices' => [
                'header' => __('Header (in &lt;head&gt;)', 'sage'),
                'footer' => __('Footer (before &lt;/body&gt;)', 'sage'),
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

        // Enqueue external scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueueExternalScripts'], 999);
    }

    /**
     * Output header inline JavaScript.
     *
     * @return void
     */
    public function outputHeaderScripts()
    {
        $header_js = get_theme_mod(self::SETTING_HEADER_JS, '');

        if (!empty(trim($header_js))) {
            echo "\n<!-- Custom Header Scripts -->\n";
            echo "<script>\n";
            echo $header_js;
            echo "\n</script>\n";
        }
    }

    /**
     * Output footer inline JavaScript.
     *
     * @return void
     */
    public function outputFooterScripts()
    {
        $footer_js = get_theme_mod(self::SETTING_FOOTER_JS, '');

        if (!empty(trim($footer_js))) {
            echo "\n<!-- Custom Footer Scripts -->\n";
            echo "<script>\n";
            echo $footer_js;
            echo "\n</script>\n";
        }
    }

    /**
     * Enqueue external JavaScript files.
     *
     * @return void
     */
    public function enqueueExternalScripts()
    {
        $external_js = get_theme_mod(self::SETTING_EXTERNAL_JS, '');
        $position = get_theme_mod(self::SETTING_EXTERNAL_JS_POSITION, 'footer');

        if (empty(trim($external_js))) {
            return;
        }

        $urls = $this->parseURLs($external_js);
        $in_footer = ($position === 'footer');

        foreach ($urls as $index => $url) {
            if (!empty($url)) {
                wp_enqueue_script(
                    'aripplesong-custom-external-' . $index,
                    $url,
                    [],
                    null,
                    $in_footer
                );
            }
        }
    }

    /**
     * Sanitize JavaScript code.
     * 
     * Note: We don't escape JS code since it's entered by administrators
     * and needs to execute as-is. The Customizer already requires
     * appropriate capabilities to access.
     *
     * @param string $input
     * @return string
     */
    public function sanitizeJS($input)
    {
        // Remove script tags if accidentally included
        $input = preg_replace('/<\/?script[^>]*>/i', '', $input);
        
        return $input;
    }

    /**
     * Sanitize URL list.
     *
     * @param string $input
     * @return string
     */
    public function sanitizeURLs($input)
    {
        $urls = $this->parseURLs($input);
        $sanitized = [];

        foreach ($urls as $url) {
            $url = trim($url);
            if (!empty($url)) {
                $sanitized_url = esc_url_raw($url);
                if (!empty($sanitized_url)) {
                    $sanitized[] = $sanitized_url;
                }
            }
        }

        return implode("\n", $sanitized);
    }

    /**
     * Sanitize position setting.
     *
     * @param string $input
     * @return string
     */
    public function sanitizePosition($input)
    {
        $valid = ['header', 'footer'];
        return in_array($input, $valid, true) ? $input : 'footer';
    }

    /**
     * Parse URLs from textarea input.
     *
     * @param string $input
     * @return array
     */
    protected function parseURLs($input)
    {
        return array_filter(
            array_map('trim', explode("\n", $input)),
            function ($url) {
                return !empty($url);
            }
        );
    }
}

