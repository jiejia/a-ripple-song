<?php

/**
 * Theme Configuration
 * 
 * 集中管理主题相关的配置常量和辅助函数
 */

namespace App;

class Theme
{
    /**
     * 主题前缀 - 用于所有需要命名空间的 ID（sidebar、widgets 等）
     * 
     * @var string
     */
    public const PREFIX = 'aripplesong';

    /**
     * Sidebar IDs
     * 使用主题前缀避免命名冲突
     */
    public const SIDEBAR_PRIMARY = self::PREFIX . '-sidebar-primary';
    public const SIDEBAR_LEFTBAR = self::PREFIX . '-leftbar-primary';
    public const SIDEBAR_HOME_MAIN = self::PREFIX . '-home-main';
    public const SIDEBAR_FOOTER_LINKS = self::PREFIX . '-footer-links';

    /**
     * 获取主题前缀
     *
     * @return string
     */
    public static function prefix(): string
    {
        return self::PREFIX;
    }

    /**
     * 使用主题前缀生成 ID
     *
     * @param string $id 原始 ID
     * @return string 带前缀的 ID
     */
    public static function prefixed(string $id): string
    {
        return self::PREFIX . '-' . $id;
    }

    /**
     * 获取所有 sidebar ID 的映射
     *
     * @return array<string, string>
     */
    public static function sidebars(): array
    {
        return [
            'sidebar-primary' => self::SIDEBAR_PRIMARY,
            'leftbar-primary' => self::SIDEBAR_LEFTBAR,
            'home-main' => self::SIDEBAR_HOME_MAIN,
            'footer-links' => self::SIDEBAR_FOOTER_LINKS,
        ];
    }
}
