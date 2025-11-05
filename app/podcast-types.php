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
        'taxonomies' => ['post_tag'],
        'show_in_rest' => true,
        'show_in_nav_menus' => true, // 允许在导航菜单中显示
        'rewrite' => ['slug' => 'podcasts'],
        'menu_position' => 5,
    ]);
});

/**
 * Register Post Tag Taxonomy for Podcast
 * 让播客文章类型支持 WordPress 默认标签
 *
 * @return void
 */
add_action('init', function () {
    register_taxonomy_for_object_type('post_tag', 'podcast');
}, 10);

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
        'show_in_nav_menus' => true, // 允许在导航菜单中显示
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

    // 获取用户列表的辅助函数
    $build_user_options = function($users) {
        $options = [];
        
        // 获取所有角色信息
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }
        
        foreach ($users as $user) {
            // 获取用户角色
            $user_roles = $user->roles;
            $role_name = '';
            
            if (!empty($user_roles)) {
                // 获取第一个角色（通常是主角色）
                $role_key = reset($user_roles);
                // 获取角色的显示名称（本地化）
                if (isset($wp_roles->roles[$role_key])) {
                    $role_name = translate_user_role($wp_roles->roles[$role_key]['name']);
                } else {
                    $role_name = $role_key;
                }
            }
            
            // 构建显示文本：Display Name (username) role_name
            $display_text = $user->display_name . ' (' . $user->user_login . ')';
            if (!empty($role_name)) {
                $display_text .= ' ' . $role_name;
            }
            
            $options[$user->ID] = $display_text;
        }
        return $options;
    };

    // 获取成员列表（只能是 administrator、author 和 editor 角色）
    $get_members_list = function() use ($build_user_options) {
        $users = get_users([
            'role__in' => ['administrator', 'author', 'editor'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);
        return $build_user_options($users);
    };

    // 获取嘉宾列表（只能是 contributor 角色）
    $get_guests_list = function() use ($build_user_options) {
        $users = get_users([
            'role' => 'contributor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);
        return $build_user_options($users);
    };

    // Members 字段（多选用户，只能是 administrator、author 和 editor，默认当前用户）
    $members_field = $cmb->add_field([
        'name' => __('成员', 'sage'),
        'desc' => __('选择播客成员（只能选择管理员、作者或编辑角色，可多选）', 'sage'),
        'id' => 'members',
        'type' => 'multicheck',
        'options' => $get_members_list(),
        'default_cb' => function($field_args, $field) {
            // 如果是新建文章，返回当前用户ID数组（如果是 administrator、author 或 editor）
            global $post;
            if (!$post || $post->ID == 0 || !metadata_exists('post', $post->ID, 'members')) {
                $current_user_id = get_current_user_id();
                if ($current_user_id) {
                    $current_user = get_userdata($current_user_id);
                    // 只有当前用户是 administrator、author 或 editor 时才设置默认值
                    if ($current_user && !empty(array_intersect(['administrator', 'author', 'editor'], $current_user->roles))) {
                        return [$current_user_id];
                    }
                }
            }
            return [];
        },
    ]);

    // Guests 字段（多选用户，只能是 contributor 角色，默认为空）
    $cmb->add_field([
        'name' => __('嘉宾', 'sage'),
        'desc' => __('选择播客嘉宾（只能选择投稿者角色，可多选）', 'sage'),
        'id' => 'guests',
        'type' => 'multicheck',
        'options' => $get_guests_list(),
    ]);
});

/**
 * 设置播客成员的默认值
 * 新建播客时，如果 members 字段为空，自动添加当前用户（仅当用户是 administrator、author 或 editor 时）
 *
 * @param int $post_id 文章 ID
 * @return void
 */
add_action('cmb2_before_save_post_fields_podcast_metabox', function ($post_id) {
    // 检查是否是自动保存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 检查用户权限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 检查是否是新建文章（通过检查 meta 是否已存在）
    $members_exist = metadata_exists('post', $post_id, 'members');
    
    // 如果是新建文章且表单中没有提交 members 值，设置默认值
    if (!$members_exist) {
        // 检查表单中是否有提交 members（multicheck 类型使用数组格式）
        $submitted_members = isset($_POST['members']) ? $_POST['members'] : null;
        
        // 如果表单中没有提交值，设置默认值为当前用户（仅当用户是 administrator、author 或 editor 时）
        if (empty($submitted_members)) {
            $current_user_id = get_current_user_id();
            if ($current_user_id) {
                $current_user = get_userdata($current_user_id);
                // 只有当前用户是 administrator、author 或 editor 时才设置默认值
                if ($current_user && !empty(array_intersect(['administrator', 'author', 'editor'], $current_user->roles))) {
                    // multicheck 类型使用数组格式，key 为用户ID，value 为 'on'
                    $_POST['members'][$current_user_id] = 'on';
                }
            }
        }
    }
});

/**
 * 备用方案：在 CMB2 保存之后检查并设置默认值
 *
 * @param int $post_id 文章 ID
 * @return void
 */
add_action('cmb2_save_post_fields_podcast_metabox', function ($post_id) {
    // 检查是否是自动保存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 检查用户权限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 获取当前的 members 值（multicheck 类型存储为数组）
    $members = get_post_meta($post_id, 'members', true);
    
    // 如果 members 为空，设置默认值为当前用户（仅当用户是 administrator、author 或 editor 时）
    if (empty($members)) {
        $current_user_id = get_current_user_id();
        if ($current_user_id) {
            $current_user = get_userdata($current_user_id);
            // 只有当前用户是 administrator、author 或 editor 时才设置默认值
            if ($current_user && !empty(array_intersect(['administrator', 'author', 'editor'], $current_user->roles))) {
                // multicheck 类型存储为数组，key 为用户ID
                update_post_meta($post_id, 'members', [$current_user_id => 'on']);
            }
        }
    }
}, 20);

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

/**
 * 在导航菜单的 Screen Options 中默认显示播客文章类型和分类
 *
 * @param array $user_metaboxes 用户已选中的 metaboxes
 * @param int $screen_id 当前屏幕 ID
 * @param int $user_id 当前用户 ID
 * @return array
 */
add_filter('default_hidden_meta_boxes', function ($hidden, $screen) {
    // 只在导航菜单页面生效
    if ($screen->id === 'nav-menus') {
        // 从隐藏列表中移除播客相关的 metaboxes
        $hidden = array_diff($hidden, [
            'add-podcast',           // 播客文章类型
            'add-podcast_category',  // 播客分类
        ]);
    }
    return $hidden;
}, 10, 2);

/**
 * 确保播客文章类型和分类在导航菜单中可见
 * 为用户设置默认的显示选项
 *
 * @param int $user_id 用户 ID
 * @return void
 */
add_action('admin_init', function () {
    // 检查是否在导航菜单页面
    global $pagenow;
    if ($pagenow === 'nav-menus.php') {
        $user_id = get_current_user_id();
        
        // 获取用户的 metabox 隐藏设置
        $hidden_meta_boxes = get_user_option('metaboxhidden_nav-menus', $user_id);
        
        // 如果是首次访问（没有设置），或者需要更新
        if ($hidden_meta_boxes === false || !is_array($hidden_meta_boxes)) {
            $hidden_meta_boxes = [];
        }
        
        // 从隐藏列表中移除播客相关的 metaboxes
        $hidden_meta_boxes = array_diff($hidden_meta_boxes, [
            'add-podcast',
            'add-podcast_category',
        ]);
        
        // 更新用户选项
        update_user_option($user_id, 'metaboxhidden_nav-menus', $hidden_meta_boxes, true);
    }
});
