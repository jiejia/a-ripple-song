<?php

/**
 * Authors Widget
 * Display the authors list (members and guests).
 */
class Authors_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'authors_widget',
            __('aripplesong - Authors List', 'a-ripple-song'),
            ['description' => __('Display members and guest authors list', 'a-ripple-song')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $members_title = !empty($instance['members_title']) ? $instance['members_title'] : 'Members';
        $guests_title = !empty($instance['guests_title']) ? $instance['guests_title'] : 'Guests';
        $show_members = isset($instance['show_members']) ? $instance['show_members'] : true;
        $show_guests = isset($instance['show_guests']) ? $instance['show_guests'] : true;
        
        // Members (administrators, editors, authors).
        $members = get_users([
            'role__in' => ['administrator', 'editor', 'author'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);
        
        // Guests (contributors).
        $contributors = get_users([
            'role' => 'contributor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        // Precompute base post counts to avoid repeated queries inside the loops.
        $post_counts_by_user = [];
        $podcast_counts_by_user = [];
        if (function_exists('count_many_users_posts')) {
            $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
            $all_users = array_merge($members ?: [], $contributors ?: []);
            $user_ids = array_values(array_unique(array_map(static function ($user) {
                return (int) $user->ID;
            }, $all_users)));

            if (!empty($user_ids)) {
                $post_counts_by_user = count_many_users_posts($user_ids, 'post', true);
                $podcast_counts_by_user = $podcast_post_type ? count_many_users_posts($user_ids, $podcast_post_type, true) : [];
            }
        }
        ?>
        <div class="bg-base-100 rounded-lg p-4">
            <?php if ($show_members && !empty($members)): ?>
            <h3 class="text-sm font-bold text-base-content/50"><?php echo esc_html($members_title); ?></h3>
            <div class="grid grid-flow-row gap-2 mt-4">
                <?php foreach($members as $user): ?>
                    <?php
                    $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
                    $base_count = (int) ($post_counts_by_user[$user->ID] ?? 0) + (int) ($podcast_counts_by_user[$user->ID] ?? 0);
                    $participated_count = function_exists('aripplesong_get_participated_podcast_ids')
                        ? count(aripplesong_get_participated_podcast_ids($user->ID))
                        : 0;
                    $post_count = $base_count + $participated_count;
                    ?>
                    <a href="<?php echo esc_url(get_author_posts_url($user->ID)); ?>" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
                        <div class="avatar">
                            <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
                                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>" />
                            </div>
                        </div>
                        <span class="text-xs"><?php echo esc_html($user->display_name); ?></span>
                        <span class="text-xs text-base-content/50"><?php echo esc_html($post_count); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($show_guests && !empty($contributors)): ?>
            <h3 class="text-sm font-bold text-base-content/50 <?php echo ($show_members && !empty($members)) ? 'mt-4' : ''; ?>"><?php echo esc_html($guests_title); ?></h3>
            <div class="grid grid-flow-row gap-2 mt-4">
                <?php foreach($contributors as $user): ?>
                    <?php
                    $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
                    $base_count = (int) ($post_counts_by_user[$user->ID] ?? 0) + (int) ($podcast_counts_by_user[$user->ID] ?? 0);
                    $participated_count = function_exists('aripplesong_get_participated_podcast_ids')
                        ? count(aripplesong_get_participated_podcast_ids($user->ID))
                        : 0;
                    $post_count = $base_count + $participated_count;
                    ?>
                    <a href="<?php echo esc_url(get_author_posts_url($user->ID)); ?>" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
                        <div class="avatar">
                            <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
                                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>" />
                            </div>
                        </div>
                        <span class="text-xs"><?php echo esc_html($user->display_name); ?></span>
                        <span class="text-xs text-base-content/50"><?php echo esc_html($post_count); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if ((!$show_members || empty($members)) && (!$show_guests || empty($contributors))): ?>
            <div class="text-center py-8">
                <div class="text-base-content/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p class="text-sm font-medium"><?php _e('No authors yet', 'a-ripple-song'); ?></p>
                    <p class="text-xs mt-1"><?php _e('Authors will appear here after adding users', 'a-ripple-song'); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $members_title = !empty($instance['members_title']) ? $instance['members_title'] : 'Members';
        $guests_title = !empty($instance['guests_title']) ? $instance['guests_title'] : 'Guests';
        $show_members = isset($instance['show_members']) ? $instance['show_members'] : true;
        $show_guests = isset($instance['show_guests']) ? $instance['show_guests'] : true;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('members_title'); ?>"><?php _e('Members Title:', 'a-ripple-song'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('members_title'); ?>" 
                   name="<?php echo $this->get_field_name('members_title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($members_title); ?>">
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_members); ?> 
                   id="<?php echo $this->get_field_id('show_members'); ?>" 
                   name="<?php echo $this->get_field_name('show_members'); ?>">
            <label for="<?php echo $this->get_field_id('show_members'); ?>"><?php _e('Show Members (Administrators, Editors, Authors)', 'a-ripple-song'); ?></label>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('guests_title'); ?>"><?php _e('Guests Title:', 'a-ripple-song'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('guests_title'); ?>" 
                   name="<?php echo $this->get_field_name('guests_title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($guests_title); ?>">
        </p>
        
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_guests); ?> 
                   id="<?php echo $this->get_field_id('show_guests'); ?>" 
                   name="<?php echo $this->get_field_name('show_guests'); ?>">
            <label for="<?php echo $this->get_field_id('show_guests'); ?>"><?php _e('Show Guests (Contributors)', 'a-ripple-song'); ?></label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['members_title'] = (!empty($new_instance['members_title'])) ? sanitize_text_field($new_instance['members_title']) : 'Members';
        $instance['guests_title'] = (!empty($new_instance['guests_title'])) ? sanitize_text_field($new_instance['guests_title']) : 'Guests';
        $instance['show_members'] = (!empty($new_instance['show_members'])) ? 1 : 0;
        $instance['show_guests'] = (!empty($new_instance['show_guests'])) ? 1 : 0;
        return $instance;
    }
}
