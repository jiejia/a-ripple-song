<?php

/**
 * Podcast List Widget
 * Display podcast lists.
 */
class Podcast_List_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'podcast_list_widget',
            __('aripplesong - Podcast List', 'sage'),
            ['description' => __('Display latest podcast list', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];

        $episode_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
        if (!$episode_post_type) {
            echo $args['after_widget'];
            return;
        }
        
        $title = !empty($instance['title']) ? $instance['title'] : 'PODCAST';
        $posts_per_page = !empty($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 3;
        $show_see_all = !empty($instance['show_see_all']) ? $instance['show_see_all'] : true;
        
        // Query latest podcasts (recent).
        $recent_podcasts = new WP_Query([
            'post_type' => $episode_post_type,
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        // Query popular podcasts (by views + plays weighted score).
        // Fetch more posts to ensure we get the most popular ones after sorting.
        $popular_query = new WP_Query([
            'post_type' => $episode_post_type,
            'posts_per_page' => max($posts_per_page * 3, 20), // Fetch more to sort properly
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        // Calculate weighted score and sort.
        $popular_posts_with_score = [];
        if ($popular_query->have_posts()) {
            while ($popular_query->have_posts()) {
                $popular_query->the_post();
                $pid = get_the_ID();
                $views = (int) get_post_meta($pid, '_views_count', true);
                $plays = (int) get_post_meta($pid, '_play_count', true);
                // Weighted score: views + plays (can adjust weights if needed)
                $score = $views + $plays;
                $popular_posts_with_score[] = [
                    'post' => get_post($pid),
                    'score' => $score
                ];
            }
            wp_reset_postdata();
        }
        
        // Sort by score descending.
        usort($popular_posts_with_score, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        // Get top N posts.
        $popular_posts_with_score = array_slice($popular_posts_with_score, 0, $posts_per_page);
        
        // Query random podcasts.
        $random_podcasts = new WP_Query([
            'post_type' => $episode_post_type,
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'orderby' => 'rand'
        ]);
        
        // Prepare the podcast data for the three tabs.
        $podcast_data = [
            'recent' => $this->prepare_podcast_list($recent_podcasts),
            'popular' => $this->prepare_podcast_list_from_posts($popular_posts_with_score),
            'random' => $this->prepare_podcast_list($random_podcasts)
        ];
        
        wp_reset_postdata();
        ?>
        <div class="rounded-lg bg-base-100 p-4" 
             x-data="{ 
                 activeTab: 'recent',
                 podcastData: <?php echo esc_attr(wp_json_encode($podcast_data)); ?>
             }">
            <div class="grid grid-cols-[1fr_auto] items-center">
                <h2 class="text-lg font-bold">
                    <?php echo esc_html($title); ?>
                </h2>
                <?php if ($show_see_all): ?>
                <span class="text-xs text-base-content/70">
                    <?php
                        $see_all_page = get_page_by_path('episodes');
                        $see_all_url = $see_all_page ? get_permalink($see_all_page) : '';
                        if (!$see_all_url) {
                            $archive_url = get_post_type_archive_link($episode_post_type);
                            $see_all_url = $archive_url ?: home_url('/episodes/');
                        }
                    ?>
                    <a href="<?php echo esc_url($see_all_url); ?>"><?php _e('See all', 'sage'); ?></a>
                </span>
                <?php endif; ?>
            </div>
            <ul class="flex gap-2 mt-2">
                <li>
                    <button 
                        @click="activeTab = 'recent'" 
                        :class="activeTab === 'recent' ? 'bg-base-200' : 'bg-base-100'"
                        class="btn rounded-full btn-sm">
                        <?php _e('Recent', 'sage'); ?>
                    </button>
                </li>
                <li>
                    <button 
                        @click="activeTab = 'popular'" 
                        :class="activeTab === 'popular' ? 'bg-base-200' : 'bg-base-100'"
                        class="btn rounded-full btn-sm">
                        <?php _e('Popular', 'sage'); ?>
                    </button>
                </li>
                <li>
                    <button 
                        @click="activeTab = 'random'" 
                        :class="activeTab === 'random' ? 'bg-base-200' : 'bg-base-100'"
                        class="btn rounded-full btn-sm">
                        <?php _e('Random', 'sage'); ?>
                    </button>
                </li>
            </ul>
            
            <!-- Recent Tab -->
            <ul class="grid grid-flow-row gap-y-4 mt-4" x-show="activeTab === 'recent'">
                <?php if (!empty($podcast_data['recent'])): ?>
                    <?php foreach ($podcast_data['recent'] as $podcast): ?>
                        <li x-data="{ episode: <?php echo esc_attr(wp_json_encode($podcast['episode_data'])); ?> }">
                            <?php 
                                echo \Roots\view('partials.podcast-episode-card', [
                                    'post_id' => $podcast['post_id'],
                                    'audio_file' => $podcast['audio_file'],
                                    'episode_data' => $podcast['episode_data'],
                                    'title' => $podcast['title'],
                                    'show_link' => true
                                ])->render();
                            ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-center text-base-content/50 py-8"><?php _e('No podcast content', 'sage'); ?></li>
                <?php endif; ?>
            </ul>
            
            <!-- Popular Tab -->
            <ul class="grid grid-flow-row gap-y-4 mt-4" x-show="activeTab === 'popular'" style="display: none;">
                <?php if (!empty($podcast_data['popular'])): ?>
                    <?php foreach ($podcast_data['popular'] as $podcast): ?>
                        <li x-data="{ episode: <?php echo esc_attr(wp_json_encode($podcast['episode_data'])); ?> }">
                            <?php 
                                echo \Roots\view('partials.podcast-episode-card', [
                                    'post_id' => $podcast['post_id'],
                                    'audio_file' => $podcast['audio_file'],
                                    'episode_data' => $podcast['episode_data'],
                                    'title' => $podcast['title'],
                                    'show_link' => true
                                ])->render();
                            ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-center text-base-content/50 py-8"><?php _e('No podcast content', 'sage'); ?></li>
                <?php endif; ?>
            </ul>
            
            <!-- Random Tab -->
            <ul class="grid grid-flow-row gap-y-4 mt-4" x-show="activeTab === 'random'" style="display: none;">
                <?php if (!empty($podcast_data['random'])): ?>
                    <?php foreach ($podcast_data['random'] as $podcast): ?>
                        <li x-data="{ episode: <?php echo esc_attr(wp_json_encode($podcast['episode_data'])); ?> }">
                            <?php 
                                echo \Roots\view('partials.podcast-episode-card', [
                                    'post_id' => $podcast['post_id'],
                                    'audio_file' => $podcast['audio_file'],
                                    'episode_data' => $podcast['episode_data'],
                                    'title' => $podcast['title'],
                                    'show_link' => true
                                ])->render();
                            ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-center text-base-content/50 py-8"><?php _e('No podcast content', 'sage'); ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    /**
     * Prepare podcast list data.
     */
    private function prepare_podcast_list($query) {
        $podcasts = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $audio_file = function_exists('aripplesong_get_episode_meta')
                    ? aripplesong_get_episode_meta($post_id, 'audio_file', '')
                    : get_post_meta($post_id, 'audio_file', true);
                $episode_data = get_episode_data($post_id);
                
                $podcasts[] = [
                    'post_id' => $post_id,
                    'audio_file' => $audio_file,
                    'episode_data' => $episode_data,
                    'title' => get_the_title()
                ];
            }
        }
        
        return $podcasts;
    }
    
    /**
     * Prepare podcast list data from pre-sorted posts array.
     */
    private function prepare_podcast_list_from_posts($posts_with_score) {
        $podcasts = [];
        
        foreach ($posts_with_score as $item) {
            $post = $item['post'];
            $post_id = $post->ID;
            $audio_file = function_exists('aripplesong_get_episode_meta')
                ? aripplesong_get_episode_meta($post_id, 'audio_file', '')
                : get_post_meta($post_id, 'audio_file', true);
            $episode_data = get_episode_data($post_id);
            
            $podcasts[] = [
                'post_id' => $post_id,
                'audio_file' => $audio_file,
                'episode_data' => $episode_data,
                'title' => $post->post_title
            ];
        }
        
        return $podcasts;
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'PODCAST';
        $posts_per_page = !empty($instance['posts_per_page']) ? absint($instance['posts_per_page']) : 3;
        $show_see_all = isset($instance['show_see_all']) ? $instance['show_see_all'] : true;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e('Number of posts:', 'sage'); ?></label>
            <input class="tiny-text" 
                   id="<?php echo $this->get_field_id('posts_per_page'); ?>" 
                   name="<?php echo $this->get_field_name('posts_per_page'); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($posts_per_page); ?>" 
                   size="3">
        </p>
        <p>
            <input class="checkbox" 
                   type="checkbox" 
                   <?php checked($show_see_all); ?> 
                   id="<?php echo $this->get_field_id('show_see_all'); ?>" 
                   name="<?php echo $this->get_field_name('show_see_all'); ?>">
            <label for="<?php echo $this->get_field_id('show_see_all'); ?>"><?php _e('Show "See all" link', 'sage'); ?></label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? absint($new_instance['posts_per_page']) : 3;
        $instance['show_see_all'] = (!empty($new_instance['show_see_all'])) ? 1 : 0;
        return $instance;
    }
}
