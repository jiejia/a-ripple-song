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
        add_action('admin_notices', [$this, 'showAudioMetaErrorNotice']);
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
            'desc' => __('Auto detected from "Audio File" on save.', 'sage'),
            'id' => 'duration',
            'type' => 'text',
            'attributes' => [
                'type' => 'number',
                'pattern' => '\d*',
                'min' => '0',
                'step' => '1',
                'readonly' => 'readonly',
            ],
            'sanitization_cb' => 'absint',
        ]);

        $cmb->add_field([
            'name' => __('Audio Length (bytes)', 'sage'),
            'desc' => __('Auto detected from "Audio File" on save.', 'sage'),
            'id' => 'audio_length',
            'type' => 'text_small',
            'attributes' => [
                'type' => 'number',
                'pattern' => '\d*',
                'min' => '1',
                'readonly' => 'readonly',
            ],
            'sanitization_cb' => 'absint',
        ]);

        $cmb->add_field([
            'name' => __('Audio MIME Type', 'sage'),
            'desc' => __('Auto detected from "Audio File" on save.', 'sage'),
            'id' => 'audio_mime',
            'type' => 'text_medium',
            'default' => 'audio/mpeg',
            'attributes' => [
                'readonly' => 'readonly',
            ],
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
            'name' => __('Transcript (optional)', 'sage'),
            'desc' => __('Optional. Upload a transcript file (vtt/srt/txt/pdf) or enter a transcript URL (https).', 'sage'),
            'id' => 'episode_transcript',
            'type' => 'file',
            'options' => [
                'url' => true,
            ],
            'text' => [
                'add_upload_file_text' => __('Add Transcript', 'sage'),
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

        if (function_exists('use_block_editor_for_post_type') && use_block_editor_for_post_type('podcast')) {
            wp_register_script(
                'podcast-editor-guard',
                '',
                ['wp-data', 'wp-notices', 'wp-edit-post', 'wp-dom-ready'],
                null,
                true
            );
            wp_enqueue_script('podcast-editor-guard');
            wp_add_inline_script(
                'podcast-editor-guard',
                "(function(wp){if(!wp||!wp.data||!wp.data.dispatch||!wp.data.select){return;}var lockKey='podcast-audio-required';var editor=wp.data.dispatch('core/editor');var notices=wp.data.dispatch('core/notices');var selectNotices=wp.data.select('core/notices');var locked=null;function getAudioValue(){var el=document.querySelector('#audio_file')||document.querySelector('input[name=\"audio_file\"]');if(!el){return '';}return (el.value||'').trim();}function setLocked(shouldLock){if(locked===shouldLock){return;}locked=shouldLock;if(shouldLock){editor.lockPostSaving(lockKey);if(!selectNotices.getNotice(lockKey)){notices.createNotice('error','Audio File 不能为空，填写后才能保存。',{id:lockKey,isDismissible:false});}}else{editor.unlockPostSaving(lockKey);notices.removeNotice(lockKey);}}function apply(){setLocked(getAudioValue()==='');}wp.domReady(function(){apply();setInterval(apply,500);});})(window.wp);"
            );
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
        $episode_explicit = isset($_POST['episode_explicit']) ? trim((string) $_POST['episode_explicit']) : '';
        $episode_type = isset($_POST['episode_type']) ? trim((string) $_POST['episode_type']) : '';

        $existing_audio_url = (string) get_post_meta($post_id, 'audio_file', true);
        $audio_url_submitted = array_key_exists('audio_file', $_POST);
        $audio_url_posted = $audio_url_submitted ? $this->normalizeAudioFileValue($_POST['audio_file']) : '';
        $audio_url = $audio_url_submitted ? $audio_url_posted : $existing_audio_url;
        $existing_duration = (int) get_post_meta($post_id, 'duration', true);
        $existing_length = (int) get_post_meta($post_id, 'audio_length', true);
        $existing_mime = (string) get_post_meta($post_id, 'audio_mime', true);

        if ($audio_url === '') {
            $errors[] = __('Audio File is required.', 'sage');
        } elseif (
            $audio_url !== ''
            && $existing_audio_url !== ''
            && $audio_url === $existing_audio_url
            && $existing_duration > 0
            && $existing_length > 0
            && $existing_mime !== ''
        ) {
            $auto_meta = [
                'duration' => $existing_duration,
                'length' => $existing_length,
                'mime' => $existing_mime,
            ];
        } else {
            $auto_meta = $this->calculateAudioMeta($post_id, $audio_url);
        }

        if ($audio_url !== '') {
            $duration = !empty($auto_meta['duration']) ? (int) $auto_meta['duration'] : 0;
            $audio_length = !empty($auto_meta['length']) ? (int) $auto_meta['length'] : 0;
            $audio_mime = !empty($auto_meta['mime']) ? (string) $auto_meta['mime'] : '';

            $_POST['duration'] = $duration;
            $_POST['audio_length'] = $audio_length;
            $_POST['audio_mime'] = $audio_mime;

            if ($duration > 0) {
                update_post_meta($post_id, 'duration', $duration);
            }
            if ($audio_length > 0) {
                update_post_meta($post_id, 'audio_length', $audio_length);
            }
            if ($audio_mime !== '') {
                update_post_meta($post_id, 'audio_mime', $audio_mime);
            }

            if (!$this->isValidHttpUrl($audio_url)) {
                $errors[] = __('Audio File must be a valid URL (https recommended).', 'sage');
            }

            if ($duration <= 0) {
                $errors[] = __('Duration (seconds) could not be auto detected from Audio File.', 'sage');
            }

            if ($audio_length <= 0) {
                $errors[] = __('Audio Length (bytes) could not be auto detected from Audio File.', 'sage');
            }

            if ($audio_mime === '') {
                $errors[] = __('Audio MIME Type could not be auto detected from Audio File.', 'sage');
            }
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

    private function normalizeAudioFileValue($value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            foreach (['url', 'value', 'file', 'src'] as $key) {
                if (isset($value[$key]) && is_string($value[$key])) {
                    return trim($value[$key]);
                }
            }

            foreach ($value as $item) {
                if (is_string($item) && trim($item) !== '') {
                    return trim($item);
                }
            }
        }

        return '';
    }

    private function isValidHttpUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        $encoded = $this->encodeUrlForRequest($url);

        if (function_exists('wp_http_validate_url')) {
            return (bool) wp_http_validate_url($encoded);
        }

        $parts = parse_url($encoded);
        if ($parts === false) {
            return false;
        }

        $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '';
        return in_array($scheme, ['http', 'https'], true) && !empty($parts['host']);
    }

    private function encodeUrlForRequest(string $url): string
    {
        $parts = function_exists('wp_parse_url') ? wp_parse_url($url) : parse_url($url);
        if ($parts === false || !is_array($parts)) {
            return $url;
        }

        $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '';
        $host = isset($parts['host']) ? (string) $parts['host'] : '';
        if ($scheme === '' || $host === '') {
            return $url;
        }

        $user = isset($parts['user']) ? (string) $parts['user'] : '';
        $pass = isset($parts['pass']) ? (string) $parts['pass'] : '';
        $auth = '';
        if ($user !== '') {
            $auth = $user;
            if ($pass !== '') {
                $auth .= ':' . $pass;
            }
            $auth .= '@';
        }

        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

        $path = isset($parts['path']) ? (string) $parts['path'] : '';
        if ($path !== '') {
            $segments = explode('/', $path);
            $segments = array_map(static function (string $segment): string {
                return rawurlencode(rawurldecode($segment));
            }, $segments);
            $path = implode('/', $segments);
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . (string) $parts['query'] : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . (string) $parts['fragment'] : '';

        return $scheme . '://' . $auth . $host . $port . $path . $query . $fragment;
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

        $last_error = null;

        if (!class_exists('getID3')) {
            $vendor_path = get_template_directory() . '/vendor/autoload.php';
            if (file_exists($vendor_path)) {
                require_once $vendor_path;
            }
        }

        if (!class_exists('getID3')) {
            $last_error = "Podcast #{$post_id}: getID3 not available";
            error_log($last_error);
            update_post_meta($post_id, '_podcast_audio_meta_last_error', $last_error);
            return $result;
        }

        $upload_dir = wp_get_upload_dir();
        $file_path = $audio_url;

        if (filter_var($audio_url, FILTER_VALIDATE_URL)) {
            // Fast path: standard uploads URL (same domain).
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $audio_url);

            // CDN/domain-mapped uploads URL: map by path fragment rather than full baseurl.
            if (!file_exists($file_path)) {
                $audio_path = (string) parse_url($audio_url, PHP_URL_PATH);
                $audio_path = $audio_path !== '' ? rawurldecode($audio_path) : '';
                $uploads_url_path = (string) parse_url($upload_dir['baseurl'], PHP_URL_PATH);

                if ($audio_path !== '' && $uploads_url_path !== '' && strpos($audio_path, $uploads_url_path) === 0) {
                    $relative = ltrim(substr($audio_path, strlen($uploads_url_path)), '/');
                    $file_path = trailingslashit($upload_dir['basedir']) . $relative;
                }
            }
        }

        if (!file_exists($file_path)) {
            $parsed_url = parse_url($audio_url);
            if (isset($parsed_url['path'])) {
                $file_path = ABSPATH . ltrim(rawurldecode((string) $parsed_url['path']), '/');
            }
        }

        if (!file_exists($file_path)) {
            // Last resort: download remote audio to a temp file so getID3 can analyze it.
            if ($this->isValidHttpUrl($audio_url)) {
                $request_url = $this->encodeUrlForRequest($audio_url);

                if (function_exists('wp_http_validate_url') && !wp_http_validate_url($request_url)) {
                    $last_error = "Podcast #{$post_id}: audio URL rejected by wp_http_validate_url";
                    error_log($last_error);
                    update_post_meta($post_id, '_podcast_audio_meta_last_error', $last_error);
                    return $result;
                }

                if (!function_exists('download_url')) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }

                $timeout = (int) apply_filters('podcast_audio_meta_download_timeout', 300, $audio_url, $post_id);
                if ($timeout < 30) {
                    $timeout = 30;
                }

                $tmp = download_url($request_url, $timeout);
                if (is_wp_error($tmp)) {
                    $last_error = "Podcast #{$post_id}: audio download failed - " . $tmp->get_error_message();
                    error_log($last_error);
                    update_post_meta($post_id, '_podcast_audio_meta_last_error', $last_error);
                    return $result;
                }

                try {
                    $getID3 = new \getID3();
                    $file_info = $getID3->analyze($tmp);

                    if (isset($file_info['playtime_seconds'])) {
                        $result['duration'] = (int) round($file_info['playtime_seconds']);
                    } else {
                        $last_error = "Podcast #{$post_id}: getID3 did not return playtime_seconds for downloaded audio";
                    }

                    $tmp_size = @filesize($tmp);
                    if ($tmp_size !== false) {
                        $result['length'] = (int) $tmp_size;
                    }

                    if (!empty($file_info['mime_type'])) {
                        $result['mime'] = $file_info['mime_type'];
                    }
                } catch (\Exception $e) {
                    $last_error = "Podcast #{$post_id}: getID3 error - " . $e->getMessage();
                    error_log($last_error);
                } finally {
                    @unlink($tmp);
                }

                if ($last_error) {
                    update_post_meta($post_id, '_podcast_audio_meta_last_error', $last_error);
                } elseif (!empty($result['duration'])) {
                    delete_post_meta($post_id, '_podcast_audio_meta_last_error');
                }

                return $result;
            }

            $last_error = "Podcast #{$post_id}: audio file missing for duration/size/mime detection";
            error_log($last_error);
            update_post_meta($post_id, '_podcast_audio_meta_last_error', $last_error);
            return $result;
        }

        try {
            $getID3 = new \getID3();
            $file_info = $getID3->analyze($file_path);

            if (isset($file_info['playtime_seconds'])) {
                $result['duration'] = (int) round($file_info['playtime_seconds']);
            } else {
                $last_error = "Podcast #{$post_id}: getID3 did not return playtime_seconds for local file";
            }

            if (!empty($file_info['filesize'])) {
                $result['length'] = (int) $file_info['filesize'];
            }

            if (!empty($file_info['mime_type'])) {
                $result['mime'] = $file_info['mime_type'];
            }
        } catch (\Exception $e) {
            $last_error = "Podcast #{$post_id}: getID3 error - " . $e->getMessage();
            error_log($last_error);
        }

        if ($last_error) {
            update_post_meta($post_id, '_podcast_audio_meta_last_error', $last_error);
        } elseif (!empty($result['duration'])) {
            delete_post_meta($post_id, '_podcast_audio_meta_last_error');
        }

        return $result;
    }

    /**
     * Show last audio meta extraction error on the podcast editor screen.
     */
    public function showAudioMetaErrorNotice(): void
    {
        if (!is_admin()) {
            return;
        }

        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'podcast') {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
        if ($post_id <= 0) {
            return;
        }

        $last_error = (string) get_post_meta($post_id, '_podcast_audio_meta_last_error', true);
        if ($last_error === '') {
            return;
        }

        echo '<div class="notice notice-warning"><p>' . esc_html($last_error) . '</p></div>';
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

        register_rest_field('podcast', 'episode_transcript', [
            'get_callback' => function ($post) {
                return get_post_meta($post['id'], 'episode_transcript', true);
            },
            'schema' => [
                'description' => __('Episode transcript URL', 'sage'),
                'type' => 'string',
            ],
        ]);
    }
}

(new Podcast())->register();
