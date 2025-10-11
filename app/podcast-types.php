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
