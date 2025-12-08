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
            'name' => __('Podcasts', 'sage'),
            'singular_name' => __('Podcast', 'sage'),
            'add_new' => __('Add New Podcast', 'sage'),
            'add_new_item' => __('Add New Podcast', 'sage'),
            'edit_item' => __('Edit Podcast', 'sage'),
            'new_item' => __('New Podcast', 'sage'),
            'view_item' => __('View Podcast', 'sage'),
            'view_items' => __('View Podcasts', 'sage'),
            'search_items' => __('Search Podcasts', 'sage'),
            'not_found' => __('No podcasts found', 'sage'),
            'not_found_in_trash' => __('No podcasts found in Trash', 'sage'),
            'all_items' => __('All Podcasts', 'sage'),
            'menu_name' => __('Podcasts', 'sage'),
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-microphone',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'trackbacks'],
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
 * Set default comment status to open for podcast post type
 * 为播客文章类型设置默认打开评论
 *
 * @param array $data Post data
 * @param array $postarr Original post data
 * @return array Modified post data
 */
add_filter('wp_insert_post_data', function ($data, $postarr) {
    // 只处理新创建的播客文章（ID 为 0 表示新文章）
    if ($data['post_type'] === 'podcast' && empty($postarr['ID'])) {
        $data['comment_status'] = 'open';
        $data['ping_status'] = 'open';
    }
    return $data;
}, 10, 2);

/**
 * Register Podcast Category Taxonomy
 *
 * @return void
 */
add_action('init', function () {
    register_taxonomy('podcast_category', 'podcast', [
        'labels' => [
            'name' => __('Podcast Categories', 'sage'),
            'singular_name' => __('Podcast Category', 'sage'),
            'search_items' => __('Search Podcast Categories', 'sage'),
            'all_items' => __('All Podcast Categories', 'sage'),
            'parent_item' => __('Parent Podcast Category', 'sage'),
            'parent_item_colon' => __('Parent Podcast Category:', 'sage'),
            'edit_item' => __('Edit Podcast Category', 'sage'),
            'update_item' => __('Update Podcast Category', 'sage'),
            'add_new_item' => __('Add New Podcast Category', 'sage'),
            'new_item_name' => __('New Podcast Category Name', 'sage'),
            'menu_name' => __('Podcast Categories', 'sage'),
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
        'title' => __('Podcast Details', 'sage'),
        'object_types' => ['podcast'],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);

    // 后台标红必填星号
    add_action('admin_head', function () {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->post_type !== 'podcast') {
            return;
        }
        ?>
        <style>
            .cmb-row.cmb-required .cmb-th label:after {
                content: " *";
                color: #e11d48;
                font-weight: 700;
                margin-left: 4px;
            }
        </style>
        <?php
    });

    // 音频文件字段（必填）
    $cmb->add_field([
        'name' => __('Audio File', 'sage'),
        'desc' => __('Required. Upload an audio file or enter audio file URL (https).', 'sage'),
        'id' => 'audio_file',
        'type' => 'file',
        'options' => [
            'url' => true, // 允许直接输入 URL
        ],
        'attributes' => [
            'required' => 'required',
            'aria-required' => 'true',
        ],
        'classes' => 'cmb-required',
        'text' => [
            'add_upload_file_text' => __('Add Audio File', 'sage'),
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

    // 时长字段（秒，必填）
    $cmb->add_field([
        'name' => __('Duration (seconds)', 'sage'),
        'desc' => __('Required. Enter audio duration in seconds. Example: 180 = 3 minutes', 'sage'),
        'id' => 'duration',
        'type' => 'text',
        'attributes' => [
            'type' => 'number',
            'pattern' => '\d*',
            'min' => '0',
            'step' => '1',
            'required' => 'required',
            'aria-required' => 'true',
        ],
        'classes' => 'cmb-required',
        'sanitization_cb' => 'absint',
    ]);

    // 音频文件大小（字节，必填）
    $cmb->add_field([
        'name' => __('Audio Length (bytes)', 'sage'),
        'desc' => __('Required. Use file size in bytes for enclosure length. Example: 12345678', 'sage'),
        'id' => 'audio_length',
        'type' => 'text_small',
        'attributes' => [
            'type' => 'number',
            'pattern' => '\d*',
            'min' => '1',
            'required' => 'required',
            'aria-required' => 'true',
        ],
        'classes' => 'cmb-required',
        'sanitization_cb' => 'absint',
    ]);

    // 音频 MIME 类型（必填）
    $cmb->add_field([
        'name' => __('Audio MIME Type', 'sage'),
        'desc' => __('Required. Example: audio/mpeg, audio/mp3, audio/aac', 'sage'),
        'id' => 'audio_mime',
        'type' => 'text_medium',
        'default' => 'audio/mpeg',
        'attributes' => [
            'required' => 'required',
            'aria-required' => 'true',
        ],
        'classes' => 'cmb-required',
    ]);

    // 显式级别（必填）
    $cmb->add_field([
        'name' => __('Explicit', 'sage'),
        'desc' => __('Required. clean / explicit.', 'sage'),
        'id' => 'episode_explicit',
        'type' => 'radio_inline',
        'options' => [
            'clean' => __('clean', 'sage'),
            'explicit' => __('explicit', 'sage'),
        ],
        'default' => 'clean',
        'attributes' => [
            'required' => 'required',
            'aria-required' => 'true',
        ],
        'classes' => 'cmb-required',
    ]);

    // Episode type（必填）
    $cmb->add_field([
        'name' => __('Episode Type', 'sage'),
        'desc' => __('Required. full / trailer / bonus', 'sage'),
        'id' => 'episode_type',
        'type' => 'select',
        'options' => [
            'full' => __('full', 'sage'),
            'trailer' => __('trailer', 'sage'),
            'bonus' => __('bonus', 'sage'),
        ],
        'default' => 'full',
        'attributes' => [
            'required' => 'required',
            'aria-required' => 'true',
        ],
        'classes' => 'cmb-required',
    ]);

    // Episode number
    $cmb->add_field([
        'name' => __('Episode Number', 'sage'),
        'desc' => __('Optional but recommended. Integer.', 'sage'),
        'id' => 'episode_number',
        'type' => 'text_small',
        'attributes' => [
            'type' => 'number',
            'pattern' => '\d*',
            'min' => '0',
            'step' => '1',
        ],
        'sanitization_cb' => 'absint',
    ]);

    // Season number
    $cmb->add_field([
        'name' => __('Season Number', 'sage'),
        'desc' => __('Optional. Integer.', 'sage'),
        'id' => 'season_number',
        'type' => 'text_small',
        'attributes' => [
            'type' => 'number',
            'pattern' => '\d*',
            'min' => '0',
            'step' => '1',
        ],
        'sanitization_cb' => 'absint',
    ]);

    // 单集作者覆盖
    $cmb->add_field([
        'name' => __('Episode Author (override)', 'sage'),
        'desc' => __('Optional. Overrides channel author for this episode.', 'sage'),
        'id' => 'episode_author',
        'type' => 'text',
    ]);

    // 单集封面
    $cmb->add_field([
        'name' => __('Episode Cover (square)', 'sage'),
        'desc' => __('Optional. Square 1400–3000px. Overrides channel cover.', 'sage'),
        'id' => 'episode_image',
        'type' => 'file',
        'options' => [
            'url' => true,
        ],
    ]);

    // Subtitle
    $cmb->add_field([
        'name' => __('Subtitle', 'sage'),
        'desc' => __('Optional. Short subtitle for iTunes.', 'sage'),
        'id' => 'episode_subtitle',
        'type' => 'text',
    ]);

    // Summary
    $cmb->add_field([
        'name' => __('Summary', 'sage'),
        'desc' => __('Optional. Plain text summary for iTunes.', 'sage'),
        'id' => 'episode_summary',
        'type' => 'textarea_small',
    ]);

    // Episode GUID（可自定义）
    $cmb->add_field([
        'name' => __('Custom GUID (optional)', 'sage'),
        'desc' => __('Optional. If empty, feed uses WP permalink as GUID.', 'sage'),
        'id' => 'episode_guid',
        'type' => 'text',
    ]);

    // 是否 block（隐藏单集）
    $cmb->add_field([
        'name' => __('iTunes Block', 'sage'),
        'desc' => __('Optional. yes = hide this episode in Apple Podcasts.', 'sage'),
        'id' => 'episode_block',
        'type' => 'radio_inline',
        'options' => [
            'no' => __('no', 'sage'),
            'yes' => __('yes', 'sage'),
        ],
        'default' => 'no',
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
        'name' => __('Members', 'sage'),
        'desc' => __('Select podcast members (only administrators, authors, or editors, multiple selection allowed)', 'sage'),
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
        'name' => __('Guests', 'sage'),
        'desc' => __('Select podcast guests (only contributors, multiple selection allowed)', 'sage'),
        'id' => 'guests',
        'type' => 'multicheck',
        'options' => $get_guests_list(),
    ]);
});

/**
 * Ensure TinyMCE assets load for the subtitle wysiwyg when editing podcasts in
 * the block editor.
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!$screen || $screen->post_type !== 'podcast') {
        return;
    }

    if (function_exists('wp_enqueue_editor')) {
        wp_enqueue_editor();
    }
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

    // 必填字段验证（阻止保存）
    $errors = [];
    $audio_url = isset($_POST['audio_file']) ? trim((string) $_POST['audio_file']) : '';
    $duration = isset($_POST['duration']) ? (int) $_POST['duration'] : 0;
    $audio_length = isset($_POST['audio_length']) ? (int) $_POST['audio_length'] : 0;
    $audio_mime = isset($_POST['audio_mime']) ? trim((string) $_POST['audio_mime']) : '';
    $episode_explicit = isset($_POST['episode_explicit']) ? trim((string) $_POST['episode_explicit']) : '';
    $episode_type = isset($_POST['episode_type']) ? trim((string) $_POST['episode_type']) : '';

    if ($audio_url === '') {
        $errors[] = __('Audio File is required.', 'sage');
    } elseif (!filter_var($audio_url, FILTER_VALIDATE_URL) || stripos($audio_url, 'http') !== 0) {
        $errors[] = __('Audio File must be a valid URL (https recommended).', 'sage');
    }

    if ($duration <= 0) {
        $errors[] = __('Duration (seconds) must be greater than 0.', 'sage');
    }

    if ($audio_length <= 0) {
        $errors[] = __('Audio Length (bytes) must be greater than 0.', 'sage');
    }

    if ($audio_mime === '') {
        $errors[] = __('Audio MIME Type is required.', 'sage');
    }

    if (!in_array($episode_explicit, ['clean', 'explicit'], true)) {
        $errors[] = __('Explicit must be clean or explicit.', 'sage');
    }

    if (!in_array($episode_type, ['full', 'trailer', 'bonus'], true)) {
        $errors[] = __('Episode Type must be full, trailer, or bonus.', 'sage');
    }

    if ($errors) {
        wp_die(
            '<p>' . implode('<br>', array_map('esc_html', $errors)) . '</p>',
            __('Podcast validation failed', 'sage'),
            ['back_link' => true]
        );
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

        // 获取文件大小（字节）并保存到 audio_length
        if (!empty($file_info['filesize'])) {
            $length_bytes = (int) $file_info['filesize'];
            update_post_meta($post_id, 'audio_length', $length_bytes);
            error_log("播客 #{$post_id}: 检测到文件大小 = {$length_bytes} 字节");
        } else {
            error_log("播客 #{$post_id}: 未能获取文件大小");
        }

        // 获取 MIME 类型
        $mime = $file_info['mime_type'] ?? '';
        if ($mime) {
            update_post_meta($post_id, 'audio_mime', $mime);
            error_log("播客 #{$post_id}: 检测到 MIME = {$mime}");
        } else {
            error_log("播客 #{$post_id}: 未能获取 MIME 类型");
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


/**
 * Register custom fields for REST API
 */
add_action('rest_api_init', function () {
    // 注册 audio_file 字段
    register_rest_field('podcast', 'audio_file', [
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], 'audio_file', true);
        },
        'schema' => [
            'description' => __('Audio file URL', 'sage'),
            'type' => 'string',
        ],
    ]);

    // 注册 duration 字段
    register_rest_field('podcast', 'duration', [
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], 'duration', true);
        },
        'schema' => [
            'description' => __('Audio duration (seconds)', 'sage'),
            'type' => 'integer',
        ],
    ]);
});