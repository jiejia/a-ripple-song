<?php

/**
 * Subscribe Links Widget
 * 显示订阅链接按钮
 */
class Subscribe_Links_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'subscribe_links_widget',
            __('aripplesong - 订阅链接', 'sage'),
            ['description' => __('显示播客订阅平台链接', 'sage')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $title = !empty($instance['title']) ? $instance['title'] : 'SUBSCRIBE';
        $apple_podcast_url = !empty($instance['apple_podcast_url']) ? $instance['apple_podcast_url'] : '';
        $spotify_url = !empty($instance['spotify_url']) ? $instance['spotify_url'] : '';
        $youtube_music_url = !empty($instance['youtube_music_url']) ? $instance['youtube_music_url'] : '';
        
        // 检查是否至少有一个链接
        $has_links = !empty($apple_podcast_url) || !empty($spotify_url) || !empty($youtube_music_url);
        
        if (!$has_links) {
            // 没有配置任何链接时不显示
            echo $args['after_widget'];
            return;
        }
        ?>
        <div class="card bg-base-100 w-full mt-4">
            <div class="card-body p-4">
                <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
                
                <?php if (!empty($apple_podcast_url)): ?>
                <a href="<?php echo esc_url($apple_podcast_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="btn bg-gradient-to-r from-gray-600 via-gray-800 to-black btn-sm text-white border-black transition-all duration-500 ease-in-out hover:from-black hover:via-gray-800 hover:to-gray-600">
                    <i data-lucide="podcast" class="w-4 h-4"></i>
                    Apple Podcast
                </a>
                <?php endif; ?>
                
                <?php if (!empty($spotify_url)): ?>
                <a href="<?php echo esc_url($spotify_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="btn bg-gradient-to-r from-green-400 via-green-500 to-[#03C755] btn-sm text-white border-[#00b544] transition-all duration-500 ease-in-out hover:from-[#03C755] hover:via-green-500 hover:to-green-400">
                    <i data-lucide="music" class="w-4 h-4"></i>
                    Spotify
                </a>
                <?php endif; ?>
                
                <?php if (!empty($youtube_music_url)): ?>
                <a href="<?php echo esc_url($youtube_music_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="btn bg-gradient-to-r from-yellow-300 via-yellow-400 to-[#FEE502] btn-sm text-[#181600] border-[#f1d800] transition-all duration-500 ease-in-out hover:from-[#FEE502] hover:via-yellow-400 hover:to-yellow-300">
                    <i data-lucide="youtube" class="w-4 h-4"></i>
                    Youtube Music
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'SUBSCRIBE';
        $apple_podcast_url = !empty($instance['apple_podcast_url']) ? $instance['apple_podcast_url'] : '';
        $spotify_url = !empty($instance['spotify_url']) ? $instance['spotify_url'] : '';
        $youtube_music_url = !empty($instance['youtube_music_url']) ? $instance['youtube_music_url'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('标题:', 'sage'); ?></label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>"
                   placeholder="SUBSCRIBE">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('apple_podcast_url'); ?>">
                <?php _e('Apple Podcast 链接:', 'sage'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('apple_podcast_url'); ?>" 
                   name="<?php echo $this->get_field_name('apple_podcast_url'); ?>" 
                   type="url" 
                   value="<?php echo esc_attr($apple_podcast_url); ?>"
                   placeholder="https://podcasts.apple.com/...">
            <small class="description"><?php _e('留空则不显示该按钮', 'sage'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('spotify_url'); ?>">
                <?php _e('Spotify 链接:', 'sage'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('spotify_url'); ?>" 
                   name="<?php echo $this->get_field_name('spotify_url'); ?>" 
                   type="url" 
                   value="<?php echo esc_attr($spotify_url); ?>"
                   placeholder="https://open.spotify.com/...">
            <small class="description"><?php _e('留空则不显示该按钮', 'sage'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('youtube_music_url'); ?>">
                <?php _e('Youtube Music 链接:', 'sage'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('youtube_music_url'); ?>" 
                   name="<?php echo $this->get_field_name('youtube_music_url'); ?>" 
                   type="url" 
                   value="<?php echo esc_attr($youtube_music_url); ?>"
                   placeholder="https://music.youtube.com/...">
            <small class="description"><?php _e('留空则不显示该按钮', 'sage'); ?></small>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : 'SUBSCRIBE';
        $instance['apple_podcast_url'] = (!empty($new_instance['apple_podcast_url'])) ? esc_url_raw($new_instance['apple_podcast_url']) : '';
        $instance['spotify_url'] = (!empty($new_instance['spotify_url'])) ? esc_url_raw($new_instance['spotify_url']) : '';
        $instance['youtube_music_url'] = (!empty($new_instance['youtube_music_url'])) ? esc_url_raw($new_instance['youtube_music_url']) : '';
        return $instance;
    }
}

