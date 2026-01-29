<?php

/**
 * Social Links Helper.
 * 
 * Retrieves social media links configured via Carbon Fields.
 * Only displays configured links in the frontend.
 */

namespace App\ThemeOptions;

class SocialLinks
{
    /**
     * Setting prefix for Carbon Fields.
     */
    const SETTING_PREFIX = 'crb_social_';

    /**
     * Available social media platforms with their icons and labels.
     *
     * @return array
     */
    public static function getPlatforms()
    {
        return [
            'facebook' => [
                'label' => __('Facebook', 'a-ripple-song'),
                'icon'  => 'facebook',
            ],
            'twitter' => [
                'label' => __('Twitter / X', 'a-ripple-song'),
                'icon'  => 'twitter',
            ],
            'instagram' => [
                'label' => __('Instagram', 'a-ripple-song'),
                'icon'  => 'instagram',
            ],
            'linkedin' => [
                'label' => __('LinkedIn', 'a-ripple-song'),
                'icon'  => 'linkedin',
            ],
            'youtube' => [
                'label' => __('YouTube', 'a-ripple-song'),
                'icon'  => 'youtube',
            ],
            'tiktok' => [
                'label' => __('TikTok', 'a-ripple-song'),
                'icon'  => 'music-2',
            ],
            'pinterest' => [
                'label' => __('Pinterest', 'a-ripple-song'),
                'icon'  => 'pin',
            ],
            'threads' => [
                'label' => __('Threads', 'a-ripple-song'),
                'icon'  => 'at-sign',
            ],
            'weibo' => [
                'label' => __('Weibo', 'a-ripple-song'),
                'icon'  => 'message-circle',
            ],
            'wechat' => [
                'label' => __('WeChat', 'a-ripple-song'),
                'icon'  => 'message-square',
            ],
            'rss' => [
                'label' => __('RSS Feed', 'a-ripple-song'),
                'icon'  => 'rss',
            ],
        ];
    }

    /**
     * Get all configured social links.
     *
     * @return array Array of configured social links with platform info
     */
    public static function getConfiguredLinks()
    {
        $configured = [];
        $platforms = self::getPlatforms();

        foreach ($platforms as $key => $platform) {
            $url = ThemeSettings::getOptionString(self::SETTING_PREFIX . $key, '');
            
            if (!empty(trim($url))) {
                $configured[$key] = [
                    'url'   => esc_url($url),
                    'label' => $platform['label'],
                    'icon'  => $platform['icon'],
                ];
            }
        }

        return $configured;
    }

    /**
     * Check if any social links are configured.
     *
     * @return bool
     */
    public static function hasLinks()
    {
        return !empty(self::getConfiguredLinks());
    }
}
