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
                'label' => __('Facebook', 'sage'),
                'icon'  => 'facebook',
            ],
            'twitter' => [
                'label' => __('Twitter / X', 'sage'),
                'icon'  => 'twitter',
            ],
            'instagram' => [
                'label' => __('Instagram', 'sage'),
                'icon'  => 'instagram',
            ],
            'linkedin' => [
                'label' => __('LinkedIn', 'sage'),
                'icon'  => 'linkedin',
            ],
            'youtube' => [
                'label' => __('YouTube', 'sage'),
                'icon'  => 'youtube',
            ],
            'tiktok' => [
                'label' => __('TikTok', 'sage'),
                'icon'  => 'music-2',
            ],
            'pinterest' => [
                'label' => __('Pinterest', 'sage'),
                'icon'  => 'pin',
            ],
            'threads' => [
                'label' => __('Threads', 'sage'),
                'icon'  => 'at-sign',
            ],
            'weibo' => [
                'label' => __('Weibo', 'sage'),
                'icon'  => 'message-circle',
            ],
            'wechat' => [
                'label' => __('WeChat', 'sage'),
                'icon'  => 'message-square',
            ],
            'rss' => [
                'label' => __('RSS Feed', 'sage'),
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
            $url = carbon_get_theme_option(self::SETTING_PREFIX . $key);
            
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

