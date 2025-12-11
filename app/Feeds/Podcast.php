<?php

namespace App\Feeds;

/**
 * Register /feed/podcast for Apple Podcasts / Spotify / YouTube Music + Podcasting 2.0.
 */
class Podcast
{
    /**
    * Register the podcast feed hook.
    */
    public function register(): void
    {
        add_action('init', [$this, 'registerFeed'], 20);
    }

    /**
     * Add the podcast feed endpoint.
     */
    public function registerFeed(): void
    {
        add_feed('podcast', [$this, 'renderFeed']);
    }

    /**
     * Format seconds to HH:MM:SS.
     */
    private function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%02d:%02d', $m, $s);
    }

    /**
     * Render the podcast RSS feed.
     */
    public function renderFeed(): void
    {
        header('Content-Type: application/rss+xml; charset=UTF-8');

        $site_url = home_url('/');
        $channel_title = carbon_get_theme_option('crb_podcast_title') ?: get_bloginfo('name');
        $channel_subtitle = carbon_get_theme_option('crb_podcast_subtitle') ?: '';
        $channel_description = carbon_get_theme_option('crb_podcast_description') ?: get_bloginfo('description');
        $channel_author = carbon_get_theme_option('crb_podcast_author') ?: get_bloginfo('name');
        $channel_owner_name = carbon_get_theme_option('crb_podcast_owner_name') ?: $channel_author;
        $channel_owner_email = carbon_get_theme_option('crb_podcast_owner_email') ?: get_bloginfo('admin_email');
        $channel_cover = carbon_get_theme_option('crb_podcast_cover') ?: '';
        $channel_explicit = carbon_get_theme_option('crb_podcast_explicit') ?: 'clean';
        $channel_language = carbon_get_theme_option('crb_podcast_language') ?: (get_bloginfo('language') ?: 'en-US');
        $channel_category_primary = carbon_get_theme_option('crb_podcast_category_primary') ?: '';
        $channel_category_secondary = carbon_get_theme_option('crb_podcast_category_secondary') ?: '';
        $channel_copyright = carbon_get_theme_option('crb_podcast_copyright') ?: '';
        $podcast_locked = carbon_get_theme_option('crb_podcast_locked') ?: 'yes';
        $podcast_guid = carbon_get_theme_option('crb_podcast_guid') ?: $site_url;

        $query = new \WP_Query([
            'post_type' => 'podcast',
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        ?>
<rss version="2.0"
    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:podcast="https://podcastindex.org/namespace/1.0">
    <channel>
        <title><?php echo esc_html($channel_title); ?></title>
        <link><?php echo esc_url($site_url); ?></link>
        <language><?php echo esc_html($channel_language); ?></language>
        <description><?php echo esc_html($channel_description); ?></description>
        <?php if ($channel_subtitle) : ?>
        <itunes:subtitle><?php echo esc_html($channel_subtitle); ?></itunes:subtitle>
        <?php endif; ?>
        <itunes:author><?php echo esc_html($channel_author); ?></itunes:author>
        <itunes:summary><?php echo esc_html($channel_description); ?></itunes:summary>
        <itunes:explicit><?php echo esc_html($channel_explicit); ?></itunes:explicit>
        <?php if ($channel_cover) : ?>
        <itunes:image href="<?php echo esc_url($channel_cover); ?>" />
        <?php endif; ?>
        <itunes:owner>
            <itunes:name><?php echo esc_html($channel_owner_name); ?></itunes:name>
            <itunes:email><?php echo esc_html($channel_owner_email); ?></itunes:email>
        </itunes:owner>
        <?php if ($channel_copyright) : ?>
        <copyright><?php echo esc_html($channel_copyright); ?></copyright>
        <?php endif; ?>
        <?php
        $categories = array_filter([$channel_category_primary, $channel_category_secondary]);
        foreach ($categories as $cat) {
            $parts = explode('::', $cat);
            if (count($parts) === 2) {
                printf(
                    '<itunes:category text="%1$s"><itunes:category text="%2$s" /></itunes:category>',
                    esc_attr($parts[0]),
                    esc_attr($parts[1])
                );
            } else {
                printf('<itunes:category text="%s" />', esc_attr($parts[0]));
            }
        }
        ?>
        <podcast:locked><?php echo esc_html($podcast_locked); ?></podcast:locked>
        <podcast:guid><?php echo esc_html($podcast_guid); ?></podcast:guid>
        <lastBuildDate><?php echo esc_html(gmdate('r')); ?></lastBuildDate>
        <?php
        if ($query->have_posts()) :
            while ($query->have_posts()) :
                $query->the_post();

                $post_id = get_the_ID();
                $audio_url = get_post_meta($post_id, 'audio_file', true);
                $audio_length = (int) get_post_meta($post_id, 'audio_length', true);
                if (empty($audio_url) || $audio_length <= 0) {
                    // Skip invalid episodes to keep feed valid.
                    continue;
                }

                $audio_mime = get_post_meta($post_id, 'audio_mime', true) ?: 'audio/mpeg';
                $duration_seconds = (int) get_post_meta($post_id, 'duration', true);
                $duration_formatted = $duration_seconds ? $this->formatDuration($duration_seconds) : '';
                $episode_explicit = get_post_meta($post_id, 'episode_explicit', true) ?: $channel_explicit;
                $episode_type = get_post_meta($post_id, 'episode_type', true) ?: 'full';
                $episode_number = get_post_meta($post_id, 'episode_number', true);
                $season_number = get_post_meta($post_id, 'season_number', true);
                $episode_author = get_post_meta($post_id, 'episode_author', true) ?: $channel_author;
                $episode_image = get_post_meta($post_id, 'episode_image', true) ?: $channel_cover;
                $episode_subtitle = get_post_meta($post_id, 'episode_subtitle', true);
                $episode_summary = get_post_meta($post_id, 'episode_summary', true);
                $episode_block = get_post_meta($post_id, 'episode_block', true) ?: 'no';
                $episode_guid = get_post_meta($post_id, 'episode_guid', true) ?: get_permalink();

                $item_summary = $episode_summary ?: wp_strip_all_tags(get_the_excerpt(), true);
                $content_html = apply_filters('the_content', get_the_content(null, false, $post_id));
                $pub_date = mysql2date('r', get_post_time('Y-m-d H:i:s', true, $post_id), false);
                ?>
        <item>
            <title><?php echo esc_html(get_the_title()); ?></title>
            <link><?php echo esc_url(get_permalink()); ?></link>
            <guid isPermaLink="false"><?php echo esc_html($episode_guid); ?></guid>
            <pubDate><?php echo esc_html($pub_date); ?></pubDate>
            <description><?php echo esc_html($item_summary); ?></description>
            <?php if ($content_html) : ?>
            <content:encoded><![CDATA[<?php echo $content_html; ?>]]></content:encoded>
            <?php endif; ?>
            <enclosure url="<?php echo esc_url($audio_url); ?>" length="<?php echo esc_attr($audio_length); ?>" type="<?php echo esc_attr($audio_mime); ?>" />
            <?php if ($duration_formatted) : ?>
            <itunes:duration><?php echo esc_html($duration_formatted); ?></itunes:duration>
            <?php endif; ?>
            <itunes:explicit><?php echo esc_html($episode_explicit); ?></itunes:explicit>
            <itunes:author><?php echo esc_html($episode_author); ?></itunes:author>
            <?php if ($episode_subtitle) : ?>
            <itunes:subtitle><?php echo esc_html($episode_subtitle); ?></itunes:subtitle>
            <?php endif; ?>
            <?php if ($episode_summary) : ?>
            <itunes:summary><?php echo esc_html($episode_summary); ?></itunes:summary>
            <?php endif; ?>
            <?php if ($episode_image) : ?>
            <itunes:image href="<?php echo esc_url($episode_image); ?>" />
            <?php endif; ?>
            <?php if (!empty($episode_number)) : ?>
            <itunes:episode><?php echo esc_html((int) $episode_number); ?></itunes:episode>
            <?php endif; ?>
            <?php if (!empty($season_number)) : ?>
            <itunes:season><?php echo esc_html((int) $season_number); ?></itunes:season>
            <?php endif; ?>
            <itunes:episodeType><?php echo esc_html($episode_type); ?></itunes:episodeType>
            <?php if ($episode_block === 'yes') : ?>
            <itunes:block>Yes</itunes:block>
            <?php endif; ?>
        </item>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
    </channel>
</rss>
        <?php
    }
}

(new Podcast())->register();

