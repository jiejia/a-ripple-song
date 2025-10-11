<?php

/**
 * Podcast Custom Post Type and Custom Fields
 */

namespace App;

/**
 * Register Podcast Custom Post Type
 *
 * @return void
 */
add_action('init', function () {
    register_post_type('podcast', [
        'labels' => [
            'name' => __('播客', 'sage'),
            'singular_name' => __('播客', 'sage'),
            'add_new' => __('添加新播客', 'sage'),
            'add_new_item' => __('添加新播客', 'sage'),
            'edit_item' => __('编辑播客', 'sage'),
            'new_item' => __('新播客', 'sage'),
            'view_item' => __('查看播客', 'sage'),
            'view_items' => __('查看播客', 'sage'),
            'search_items' => __('搜索播客', 'sage'),
            'not_found' => __('未找到播客', 'sage'),
            'not_found_in_trash' => __('回收站中未找到播客', 'sage'),
            'all_items' => __('所有播客', 'sage'),
            'menu_name' => __('播客', 'sage'),
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-microphone',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author'],
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'podcasts'],
        'menu_position' => 5,
    ]);
});

/**
 * Register Podcast Category Taxonomy
 *
 * @return void
 */
add_action('init', function () {
    register_taxonomy('podcast_category', 'podcast', [
        'labels' => [
            'name' => __('播客分类', 'sage'),
            'singular_name' => __('播客分类', 'sage'),
            'search_items' => __('搜索播客分类', 'sage'),
            'all_items' => __('所有播客分类', 'sage'),
            'parent_item' => __('父级播客分类', 'sage'),
            'parent_item_colon' => __('父级播客分类：', 'sage'),
            'edit_item' => __('编辑播客分类', 'sage'),
            'update_item' => __('更新播客分类', 'sage'),
            'add_new_item' => __('添加新播客分类', 'sage'),
            'new_item_name' => __('新播客分类名称', 'sage'),
            'menu_name' => __('播客分类', 'sage'),
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'podcast-category'],
    ]);
});

/**
 * Register CMB2 Custom Fields for Podcast
 *
 * @return void
 */
add_action('cmb2_admin_init', function () {
    $cmb = \new_cmb2_box([
        'id' => 'podcast_metabox',
        'title' => __('播客详细信息', 'sage'),
        'object_types' => ['podcast'],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);

    // 音频文件字段
    $cmb->add_field([
        'name' => __('音频文件', 'sage'),
        'desc' => __('上传音频文件或输入音频文件 URL', 'sage'),
        'id' => 'audio_file',
        'type' => 'file',
        'options' => [
            'url' => true, // 允许直接输入 URL
        ],
        'text' => [
            'add_upload_file_text' => __('添加音频文件', 'sage'),
        ],
        'query_args' => [
            'type' => [
                'audio/mpeg',
                'audio/mp3',
                'audio/wav',
                'audio/ogg',
                'audio/m4a',
            ],
        ],
    ]);

    // 时长字段（秒）
    $cmb->add_field([
        'name' => __('时长（秒）', 'sage'),
        'desc' => __('输入音频时长，单位为秒。例如：180 表示 3 分钟', 'sage'),
        'id' => 'duration',
        'type' => 'text',
        'attributes' => [
            'type' => 'number',
            'pattern' => '\d*',
            'min' => '0',
            'step' => '1',
        ],
        'sanitization_cb' => 'absint',
    ]);

    // 字幕字段（富文本）
    $cmb->add_field([
        'name' => __('字幕', 'sage'),
        'desc' => __('输入播客字幕内容（支持富文本格式）', 'sage'),
        'id' => 'subtitle',
        'type' => 'wysiwyg',
        'options' => [
            'textarea_rows' => 10,
            'media_buttons' => true,
            'teeny' => false,
        ],
    ]);
});

/**
 * 自动获取音频文件时长
 * 当保存播客时，如果有音频文件，自动使用 getID3 获取时长
 *
 * @param int $post_id 文章 ID
 * @return void
 */
add_action('cmb2_save_post_fields', function ($post_id) {
    // 只处理播客类型
    if (get_post_type($post_id) !== 'podcast') {
        return;
    }

    // 检查是否是自动保存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 检查用户权限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 获取音频文件 URL
    $audio_file = get_post_meta($post_id, 'audio_file', true);
    
    if (empty($audio_file)) {
        error_log("播客 #{$post_id}: 没有找到音频文件");
        return;
    }

    error_log("播客 #{$post_id}: 音频文件 URL = {$audio_file}");

    // 检查 getID3 类是否存在
    if (!class_exists('getID3')) {
        // 尝试加载 composer autoload
        $vendor_path = get_template_directory() . '/vendor/autoload.php';
        if (file_exists($vendor_path)) {
            require_once $vendor_path;
        }
    }

    // 如果 getID3 类还是不存在，退出
    if (!class_exists('getID3')) {
        error_log("播客 #{$post_id}: getID3 类不存在");
        return;
    }

    try {
        // 将 URL 转换为本地文件路径
        $upload_dir = wp_get_upload_dir();
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $audio_file);
        
        // 尝试另一种方式：处理域名可能不一致的情况
        if (!file_exists($file_path)) {
            // 解析 URL，只保留路径部分
            $parsed_url = parse_url($audio_file);
            if (isset($parsed_url['path'])) {
                // 尝试使用 ABSPATH 构建完整路径
                $file_path = ABSPATH . ltrim($parsed_url['path'], '/');
            }
        }
        
        error_log("播客 #{$post_id}: 文件路径 = {$file_path}");
        error_log("播客 #{$post_id}: 文件存在? " . (file_exists($file_path) ? '是' : '否'));
        
        // 如果文件不存在，退出
        if (!file_exists($file_path)) {
            error_log("播客 #{$post_id}: 文件不存在，无法解析时长");
            return;
        }

        // 初始化 getID3
        $getID3 = new \getID3();
        
        // 分析音频文件
        $file_info = $getID3->analyze($file_path);
        
        error_log("播客 #{$post_id}: getID3 分析结果 = " . print_r($file_info, true));
        
        // 获取播放时长（秒）
        if (isset($file_info['playtime_seconds'])) {
            $duration = round($file_info['playtime_seconds']);
            
            error_log("播客 #{$post_id}: 检测到时长 = {$duration} 秒");
            
            // 更新 duration 字段
            update_post_meta($post_id, 'duration', $duration);
        } else {
            error_log("播客 #{$post_id}: 未能从文件信息中获取播放时长");
        }
    } catch (\Exception $e) {
        // 记录错误，不影响文章保存
        error_log("播客 #{$post_id}: getID3 错误 - " . $e->getMessage());
    }
}, 20, 1);
