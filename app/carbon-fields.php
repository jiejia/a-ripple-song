<?php

/**
 * Theme Options using Carbon Fields.
 * 
 * This file registers all theme options using Carbon Fields library.
 * Creates a top-level menu "Theme Settings" with sub-pages for different options.
 */

namespace App;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Carbon_Fields;

/**
 * Boot Carbon Fields library.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    Carbon_Fields::boot();
});

/**
 * Register all Carbon Fields containers and fields.
 *
 * @return void
 */
add_action('carbon_fields_register_fields', function () {
    // Main Theme Settings container (top-level menu)
    $theme_settings = Container::make('theme_options', __('Theme Settings', 'sage'))
        ->set_icon('dashicons-admin-settings')
        ->set_page_menu_position(60)
        ->add_fields([
            Field::make('image', 'crb_site_logo', __('Site Logo', 'sage'))
                ->set_value_type('url')
                ->set_help_text(__('Upload a logo image. If no logo is set, the site title with icon will be displayed.', 'sage')),
            Field::make('header_scripts', 'crb_header_scripts', __('Header Scripts', 'sage'))
                ->set_help_text(esc_html__('Scripts to be added in the <head> section. You can include complete <script> tags for services like Google Analytics.', 'sage')),
            Field::make('footer_scripts', 'crb_footer_scripts', __('Footer Scripts', 'sage'))
                ->set_help_text(esc_html__('Scripts to be added before </body>. You can include complete <script> tags.', 'sage')),
        ]);

    // Social Links sub-page
    Container::make('theme_options', __('Social Links', 'sage'))
        ->set_page_parent($theme_settings)
        ->add_fields(crb_get_social_links_fields());

});

/**
 * Get social links fields configuration.
 *
 * @return array
 */
function crb_get_social_links_fields()
{
    $platforms = [
        'facebook' => [
            'label' => __('Facebook', 'sage'),
            'placeholder' => 'https://facebook.com/yourpage',
        ],
        'twitter' => [
            'label' => __('Twitter / X', 'sage'),
            'placeholder' => 'https://twitter.com/yourhandle',
        ],
        'instagram' => [
            'label' => __('Instagram', 'sage'),
            'placeholder' => 'https://instagram.com/yourhandle',
        ],
        'linkedin' => [
            'label' => __('LinkedIn', 'sage'),
            'placeholder' => 'https://linkedin.com/in/yourprofile',
        ],
        'youtube' => [
            'label' => __('YouTube', 'sage'),
            'placeholder' => 'https://youtube.com/@yourchannel',
        ],
        'tiktok' => [
            'label' => __('TikTok', 'sage'),
            'placeholder' => 'https://tiktok.com/@yourhandle',
        ],
        'pinterest' => [
            'label' => __('Pinterest', 'sage'),
            'placeholder' => 'https://pinterest.com/yourhandle',
        ],
        'threads' => [
            'label' => __('Threads', 'sage'),
            'placeholder' => 'https://threads.net/@yourhandle',
        ],
        'weibo' => [
            'label' => __('Weibo', 'sage'),
            'placeholder' => 'https://weibo.com/yourpage',
        ],
        'wechat' => [
            'label' => __('WeChat', 'sage'),
            'placeholder' => 'WeChat ID or QR code link',
        ],
        'rss' => [
            'label' => __('RSS Feed', 'sage'),
            'placeholder' => '/feed/',
        ],
    ];

    $fields = [
        Field::make('html', 'crb_social_links_info')
            ->set_html(sprintf(
                '<p>%s</p>',
                __('Configure your social media links. Leave empty to hide a platform.', 'sage')
            )),
    ];

    foreach ($platforms as $key => $platform) {
        $fields[] = Field::make('text', 'crb_social_' . $key, $platform['label'])
            ->set_attribute('placeholder', $platform['placeholder'])
            ->set_attribute('type', 'url');
    }

    return $fields;
}

