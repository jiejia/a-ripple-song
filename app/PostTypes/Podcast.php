<?php

namespace App\PostTypes;

/**
 * Podcast custom post type registration and related hooks.
 */
class Podcast
{
    /**
     * Register all hooks for the Podcast post type.
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerTags'], 10);
        add_filter('wp_insert_post_data', [$this, 'setDefaultCommentStatus'], 10, 2);
        add_action('init', [$this, 'registerCategoryTaxonomy']);
        add_action('cmb2_admin_init', [$this, 'registerCmb2Fields']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueEditorAssets']);
        add_action('cmb2_before_save_post_fields_podcast_metabox', [$this, 'ensureDefaultMembersBeforeSave']);
        add_action('cmb2_save_post_fields_podcast_metabox', [$this, 'validateRequiredFields'], 20);
        add_action('cmb2_save_post_fields', [$this, 'autoFillAudioMeta'], 20, 1);
        add_filter('default_hidden_meta_boxes', [$this, 'showPodcastMetaboxesInNavMenu'], 10, 2);
        add_action('admin_init', [$this, 'ensureNavMenuVisibility']);
        add_action('rest_api_init', [$this, 'registerRestFields']);
    }

    /**
     * Register Podcast Custom Post Type.
     */
    public function registerPostType(): void
    {
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
            'show_in_nav_menus' => true,
            'rewrite' => ['slug' => 'podcasts'],
            'menu_position' => 5,
        ]);
    }

    /**
     * Allow default post tags on podcasts.
     */
    public function registerTags(): void
    {
        register_taxonomy_for_object_type('post_tag', 'podcast');
    }

    /**
     * Default comment status to open for new podcasts.
     */
    public function setDefaultCommentStatus(array $data, array $postarr): array
    {
        if ($data['post_type'] === 'podcast' && empty($postarr['ID'])) {
            $data['comment_status'] = 'open';
            $data['ping_status'] = 'open';
        }

        return $data;
    }

    /**
     * Register Podcast Category taxonomy.
     */
    public function registerCategoryTaxonomy(): void
    {
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
            'show_in_nav_menus' => true,
            'query_var' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'podcast-category'],
        ]);
    }

    /**
     * Register CMB2 custom fields for podcasts.
     */
    public function registerCmb2Fields(): void
    {
        add_action('admin_head', [$this, 'addRequiredFieldStyles']);

        $cmb = \new_cmb2_box([
            'id' => 'podcast_metabox',
            'title' => __('Podcast Details', 'sage'),
            'object_types' => ['podcast'],
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true,
        ]);

        $cmb->add_field([
            'name' => __('Audio File', 'sage'),
            'desc' => __('Required. Upload an audio file or enter audio file URL (https).', 'sage'),
            'id' => 'audio_file',
            'type' => 'file',
            'options' => [
                'url' => true,
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

        $cmb->add_field([
            'name' => __('Episode Author (override)', 'sage'),
            'desc' => __('Optional. Overrides channel author for this episode.', 'sage'),
            'id' => 'episode_author',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Episode Cover (square)', 'sage'),
            'desc' => __('Optional. Square 1400–3000px. Overrides channel cover.', 'sage'),
            'id' => 'episode_image',
            'type' => 'file',
            'options' => [
                'url' => true,
            ],
        ]);

        $cmb->add_field([
            'name' => __('Subtitle', 'sage'),
            'desc' => __('Optional. Short subtitle for iTunes.', 'sage'),
            'id' => 'episode_subtitle',
            'type' => 'text',
        ]);

        $cmb->add_field([
            'name' => __('Summary', 'sage'),
            'desc' => __('Optional. Plain text summary for iTunes.', 'sage'),
            'id' => 'episode_summary',
            'type' => 'textarea_small',
        ]);

        $cmb->add_field([
            'name' => __('Custom GUID (optional)', 'sage'),
            'desc' => __('Optional. If empty, feed uses WP permalink as GUID.', 'sage'),
            'id' => 'episode_guid',
            'type' => 'text',
        ]);

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

        $cmb->add_field([
            'name' => __('Members', 'sage'),
            'desc' => __('Select podcast members (only administrators, authors, or editors, multiple selection allowed)', 'sage'),
            'id' => 'members',
            'type' => 'multicheck',
            'options' => $this->getMembersList(),
            'default_cb' => [$this, 'getDefaultMembers'],
        ]);

        $cmb->add_field([
            'name' => __('Guests', 'sage'),
            'desc' => __('Select podcast guests (only contributors, multiple selection allowed)', 'sage'),
            'id' => 'guests',
            'type' => 'multicheck',
            'options' => $this->getGuestsList(),
        ]);
    }

    /**
     * Add required field asterisk styling on podcast edit screens.
     */
    public function addRequiredFieldStyles(): void
    {
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
    }

    /**
     * Build user options array with role label.
     */
    private function buildUserOptions(array $users): array
    {
        $options = [];

        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        foreach ($users as $user) {
            $user_roles = $user->roles;
            $role_name = '';

            if (!empty($user_roles)) {
                $role_key = reset($user_roles);
                if (isset($wp_roles->roles[$role_key])) {
                    $role_name = translate_user_role($wp_roles->roles[$role_key]['name']);
                } else {
                    $role_name = $role_key;
                }
            }

            $display_text = $user->display_name . ' (' . $user->user_login . ')';
            if (!empty($role_name)) {
                $display_text .= ' ' . $role_name;
            }

            $options[$user->ID] = $display_text;
        }

        return $options;
    }

    /**
     * Get members list (administrator, author, editor).
     */
    private function getMembersList(): array
    {
        $users = get_users([
            'role__in' => ['administrator', 'author', 'editor'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        return $this->buildUserOptions($users);
    }

    /**
     * Get guests list (contributor).
     */
    private function getGuestsList(): array
    {
        $users = get_users([
            'role' => 'contributor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        return $this->buildUserOptions($users);
    }

    /**
     * Default selected member when creating a podcast.
     */
    public function getDefaultMembers($field_args, $field): array
    {
        global $post;
        if (!$post || $post->ID == 0 || !metadata_exists('post', $post->ID, 'members')) {
            $current_user_id = get_current_user_id();
            if ($current_user_id) {
                $current_user = get_userdata($current_user_id);
                if ($current_user && !empty(array_intersect(['administrator', 'author', 'editor'], $current_user->roles))) {
                    return [$current_user_id];
                }
            }
        }
        return [];
    }

    /**
     * Ensure TinyMCE assets load for the subtitle wysiwyg when editing podcasts.
     */
    public function enqueueEditorAssets($hook): void
    {
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
    }

    /**
     * Set default members before saving a new podcast.
     */
    public function ensureDefaultMembersBeforeSave(int $post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $members_exist = metadata_exists('post', $post_id, 'members');

        if (!$members_exist) {
            $submitted_members = $_POST['members'] ?? null;

            if (empty($submitted_members)) {
                $current_user_id = get_current_user_id();
                if ($current_user_id) {
                    $current_user = get_userdata($current_user_id);
                    if ($current_user && !empty(array_intersect(['administrator', 'author', 'editor'], $current_user->roles))) {
                        $_POST['members'][$current_user_id] = 'on';
                    }
                }
            }
        }
    }

    /**
     * Validate required fields and backfill meta on save.
     */
    public function validateRequiredFields(int $post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $errors = [];
        $audio_url = isset($_POST['audio_file']) ? trim((string) $_POST['audio_file']) : '';
        $duration = isset($_POST['duration']) ? (int) $_POST['duration'] : 0;
        $audio_length = isset($_POST['audio_length']) ? (int) $_POST['audio_length'] : 0;
        $audio_mime = isset($_POST['audio_mime']) ? trim((string) $_POST['audio_mime']) : '';
        $episode_explicit = isset($_POST['episode_explicit']) ? trim((string) $_POST['episode_explicit']) : '';
        $episode_type = isset($_POST['episode_type']) ? trim((string) $_POST['episode_type']) : '';

        $auto_meta = $this->calculateAudioMeta($post_id, $audio_url);
        if ($duration <= 0 && !empty($auto_meta['duration'])) {
            $duration = $auto_meta['duration'];
            $_POST['duration'] = $duration;
            update_post_meta($post_id, 'duration', $duration);
        }
        if ($audio_length <= 0 && !empty($auto_meta['length'])) {
            $audio_length = $auto_meta['length'];
            $_POST['audio_length'] = $audio_length;
            update_post_meta($post_id, 'audio_length', $audio_length);
        }
        if ($audio_mime === '' && !empty($auto_meta['mime'])) {
            $audio_mime = $auto_meta['mime'];
            $_POST['audio_mime'] = $audio_mime;
            update_post_meta($post_id, 'audio_mime', $audio_mime);
        }

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

        $members = get_post_meta($post_id, 'members', true);

        if (empty($members)) {
            $current_user_id = get_current_user_id();
            if ($current_user_id) {
                $current_user = get_userdata($current_user_id);
                if ($current_user && !empty(array_intersect(['administrator', 'author', 'editor'], $current_user->roles))) {
                    update_post_meta($post_id, 'members', [$current_user_id => 'on']);
                }
            }
        }
    }

    /**
     * Auto calculate audio meta after save.
     */
    public function autoFillAudioMeta(int $post_id): void
    {
        if (get_post_type($post_id) !== 'podcast') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $auto_meta = $this->calculateAudioMeta($post_id);

        if (!empty($auto_meta['duration'])) {
            update_post_meta($post_id, 'duration', $auto_meta['duration']);
        }

        if (!empty($auto_meta['length'])) {
            update_post_meta($post_id, 'audio_length', $auto_meta['length']);
        }

        if (!empty($auto_meta['mime'])) {
            update_post_meta($post_id, 'audio_mime', $auto_meta['mime']);
        }
    }

    /**
     * Calculate podcast audio metadata (duration, length, mime) via getID3.
     */
    private function calculateAudioMeta(int $post_id, string $audio_url = ''): array
    {
        $result = [
            'duration' => null,
            'length'   => null,
            'mime'     => null,
        ];

        if ($audio_url === '') {
            $audio_url = get_post_meta($post_id, 'audio_file', true);
        }

        if (empty($audio_url)) {
            return $result;
        }

        if (!class_exists('getID3')) {
            $vendor_path = get_template_directory() . '/vendor/autoload.php';
            if (file_exists($vendor_path)) {
                require_once $vendor_path;
            }
        }

        if (!class_exists('getID3')) {
            error_log("Podcast #{$post_id}: getID3 not available");
            return $result;
        }

        $upload_dir = wp_get_upload_dir();
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $audio_url);

        if (!file_exists($file_path)) {
            $parsed_url = parse_url($audio_url);
            if (isset($parsed_url['path'])) {
                $file_path = ABSPATH . ltrim($parsed_url['path'], '/');
            }
        }

        if (!file_exists($file_path)) {
            error_log("Podcast #{$post_id}: audio file missing for size/mime detection");
            return $result;
        }

        try {
            $getID3 = new \getID3();
            $file_info = $getID3->analyze($file_path);

            if (isset($file_info['playtime_seconds'])) {
                $result['duration'] = (int) round($file_info['playtime_seconds']);
            }

            if (!empty($file_info['filesize'])) {
                $result['length'] = (int) $file_info['filesize'];
            }

            if (!empty($file_info['mime_type'])) {
                $result['mime'] = $file_info['mime_type'];
            }
        } catch (\Exception $e) {
            error_log("Podcast #{$post_id}: getID3 error - " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Unhide podcast objects in nav menu screen options by default.
     */
    public function showPodcastMetaboxesInNavMenu(array $hidden, $screen): array
    {
        if ($screen->id === 'nav-menus') {
            $hidden = array_diff($hidden, [
                'add-podcast',
                'add-podcast_category',
            ]);
        }
        return $hidden;
    }

    /**
     * Ensure podcast CPT appears in nav menu metaboxes.
     */
    public function ensureNavMenuVisibility(): void
    {
        global $pagenow;
        if ($pagenow === 'nav-menus.php') {
            $user_id = get_current_user_id();

            $hidden_meta_boxes = get_user_option('metaboxhidden_nav-menus', $user_id);

            if ($hidden_meta_boxes === false || !is_array($hidden_meta_boxes)) {
                $hidden_meta_boxes = [];
            }

            $hidden_meta_boxes = array_diff($hidden_meta_boxes, [
                'add-podcast',
                'add-podcast_category',
            ]);

            update_user_option($user_id, 'metaboxhidden_nav-menus', $hidden_meta_boxes, true);
        }
    }

    /**
     * Expose custom fields to REST API responses.
     */
    public function registerRestFields(): void
    {
        register_rest_field('podcast', 'audio_file', [
            'get_callback' => function ($post) {
                return get_post_meta($post['id'], 'audio_file', true);
            },
            'schema' => [
                'description' => __('Audio file URL', 'sage'),
                'type' => 'string',
            ],
        ]);

        register_rest_field('podcast', 'duration', [
            'get_callback' => function ($post) {
                return get_post_meta($post['id'], 'duration', true);
            },
            'schema' => [
                'description' => __('Audio duration (seconds)', 'sage'),
                'type' => 'integer',
            ],
        ]);
    }
}

(new Podcast())->register();

