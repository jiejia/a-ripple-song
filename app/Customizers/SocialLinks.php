<?php

namespace App\Customizers;

use App\Abstracts\CustomizerAbstract;
use App\Theme;
use WP_Customize_Manager;

/**
 * Registers social links Customizer options.
 */
class SocialLinks extends CustomizerAbstract
{
    /**
     * Return supported social link platforms.
     *
     * @return array<string,array{label:string,icon:string}>
     */
    public static function getPlatforms(): array
    {
        return [
            'facebook' => [
                'label' => __('Facebook', 'a-ripple-song'),
                'icon' => 'facebook',
            ],
            'twitter' => [
                'label' => __('Twitter / X', 'a-ripple-song'),
                'icon' => 'x',
            ],
            'instagram' => [
                'label' => __('Instagram', 'a-ripple-song'),
                'icon' => 'instagram',
            ],
            'linkedin' => [
                'label' => __('LinkedIn', 'a-ripple-song'),
                'icon' => 'linkedin',
            ],
            'youtube' => [
                'label' => __('YouTube', 'a-ripple-song'),
                'icon' => 'youtube',
            ],
            'tiktok' => [
                'label' => __('TikTok', 'a-ripple-song'),
                'icon' => 'tiktok',
            ],
            'pinterest' => [
                'label' => __('Pinterest', 'a-ripple-song'),
                'icon' => 'pinterest',
            ],
            'threads' => [
                'label' => __('Threads', 'a-ripple-song'),
                'icon' => 'threads',
            ],
            'weibo' => [
                'label' => __('Weibo', 'a-ripple-song'),
                'icon' => 'sinaweibo',
            ],
            'wechat' => [
                'label' => __('WeChat', 'a-ripple-song'),
                'icon' => 'wechat',
            ],
            'rss' => [
                'label' => __('RSS Feed', 'a-ripple-song'),
                'icon' => 'rss',
            ],
        ];
    }

    /**
     * Return configured social links.
     *
     * @return array<string,array{label:string,icon:string,url:string}>
     */
    public static function getConfiguredLinks(): array
    {
        $configuredLinks = [];

        foreach (self::getPlatforms() as $platformKey => $platformData) {
            $url = (string) get_theme_mod(self::settingKey($platformKey), '');

            if ($url === '') {
                continue;
            }

            $configuredLinks[$platformKey] = [
                'label' => $platformData['label'],
                'icon' => $platformData['icon'],
                'url' => $url,
            ];
        }

        return $configuredLinks;
    }

    /**
     * Return whether any social links are configured.
     */
    public static function hasLinks(): bool
    {
        return ! empty(self::getConfiguredLinks());
    }

    /**
     * Register social link Customizer fields.
     *
     * @param WP_Customize_Manager $wpCustomize WordPress Customizer manager.
     */
    public function register(WP_Customize_Manager $wpCustomize): void
    {
        $wpCustomize->add_section(Theme::fieldKey('social_links'), [
            'title' => __('Social Links', 'a-ripple-song'),
            'priority' => 170,
        ]);

        foreach (self::getPlatforms() as $platformKey => $platformData) {
            $wpCustomize->add_setting(self::settingKey($platformKey), [
                'default' => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport' => 'refresh',
            ]);

            $wpCustomize->add_control(self::settingKey($platformKey), [
                'section' => Theme::fieldKey('social_links'),
                'label' => $platformData['label'],
                'description' => __('Optional. Enter a full URL.', 'a-ripple-song'),
                'type' => 'url',
                'input_attrs' => [
                    'placeholder' => __('Enter a full URL', 'a-ripple-song'),
                ],
            ]);
        }
    }

    /**
     * Return the theme mod key for a social platform.
     */
    private static function settingKey(string $platformKey): string
    {
        return Theme::fieldKey('social_link_' . $platformKey);
    }
}
