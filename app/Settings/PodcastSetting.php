<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;
use Jiejia\ARippleSong\Feeds\PodcastFeed;

/**
 * Podcast (channel) settings page, stored in the ars_podcast_settings option.
 */
class PodcastSetting extends SettingAbstract
{

    public string $slug = 'podcast_settings';

    public function isTopLevelMenu(): bool
    {
        return false;
    }

    public function title(): string
    {
        return __('Podcast Settings', 'a-ripple-song');
    }

    /**
     * Return podcast setting field definitions.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'separator',
                'label' => __('Feed', 'a-ripple-song'),
            ],
            [
                'type' => 'url',
                'key' => 'rss_url',
                'label' => __('Podcast RSS URL', 'a-ripple-song'),
                'readonly' => true,
                'copyable' => true,
                'default' => PodcastFeed::url(...),
                'help' => __('Your podcast RSS feed URL. Submit it to Apple Podcasts, Spotify and other directories.', 'a-ripple-song'),
            ],

            [
                'type' => 'separator',
                'label' => __('Show', 'a-ripple-song'),
            ],
            [
                'type' => 'image',
                'key' => 'cover',
                'label' => __('Podcast Cover (1400-3000px square)', 'a-ripple-song'),
                'required' => true,
                'dimensions' => [
                    'square' => true,
                    'min_width' => 1400,
                    'min_height' => 1400,
                    'max_width' => 3000,
                    'max_height' => 3000,
                ],
                'help' => __('Required. Square JPG/PNG between 1400-3000px for itunes:image. Apple recommends keeping the file under 512KB.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'title',
                'label' => __('Podcast Title', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'default' => (string) get_bloginfo('name'),
                'help' => __('Required. If empty, falls back to site title.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'subtitle',
                'label' => __('Podcast Subtitle', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Short tagline shown in some apps.', 'a-ripple-song'),
            ],
            [
                'type' => 'textarea',
                'key' => 'description',
                'label' => __('Podcast Description', 'a-ripple-song'),
                'required' => true,
                'default' => (string) get_bloginfo('description'),
                'help' => __('Required. Plain text description of the show.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'language',
                'label' => __('Language (RFC 5646)', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => $this->podcastLanguageOptions(),
                'default' => (string) (get_bloginfo('language') ?: 'en-US'),
                'help' => __('Required. Typically en-US, zh-CN, etc.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'explicit',
                'label' => __('Default Explicit Flag', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'options' => [
                    'clean' => __('clean (no explicit content)', 'a-ripple-song'),
                    'explicit' => __('explicit', 'a-ripple-song'),
                ],
                'default' => 'clean',
                'help' => __('Required. Single-episode value can override.', 'a-ripple-song'),
            ],

            [
                'type' => 'separator',
                'label' => __('Owner', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'author',
                'label' => __('Podcast Author (itunes:author)', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'default' => (string) get_bloginfo('name'),
                'help' => __('Required. Displayed as show author in directories.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'copyright',
                'label' => __('Copyright (optional)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. For copyright.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'owner_name',
                'label' => __('Owner Name', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'default' => (string) get_bloginfo('name'),
                'help' => __('Required. For itunes:owner itunes:name.', 'a-ripple-song'),
            ],
            [
                'type' => 'email',
                'key' => 'owner_email',
                'label' => __('Owner Email', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'default' => (string) get_bloginfo('admin_email'),
                'help' => __('Required. For itunes:owner itunes:email. Use a monitored inbox.', 'a-ripple-song'),
            ],

            [
                'type' => 'separator',
                'label' => __('Apple Podcasts', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'category_primary',
                'label' => __('Primary Category (Apple Podcasts)', 'a-ripple-song'),
                'width' => 'half',
                'required' => true,
                'placeholder' => __('(not set)', 'a-ripple-song'),
                'options' => $this->itunesCategories(),
                'help' => __('Required by Apple Podcasts. Choose at least a primary category.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'category_secondary',
                'label' => __('Secondary Category (optional)', 'a-ripple-song'),
                'width' => 'half',
                'placeholder' => __('(not set)', 'a-ripple-song'),
                'options' => $this->itunesCategories(),
                'help' => __('Optional. Some directories support a second category.', 'a-ripple-song'),
            ],
            [
                'type' => 'select',
                'key' => 'itunes_type',
                'label' => __('iTunes Type (itunes:type)', 'a-ripple-song'),
                'width' => 'third',
                'placeholder' => __('(not set)', 'a-ripple-song'),
                'options' => [
                    'episodic' => __('episodic', 'a-ripple-song'),
                    'serial' => __('serial', 'a-ripple-song'),
                ],
                'help' => __('Optional. Apple Podcasts: episodic or serial.', 'a-ripple-song'),
            ],
            [
                'type' => 'switch',
                'key' => 'itunes_block',
                'label' => __('iTunes Block (itunes:block)', 'a-ripple-song'),
                'width' => 'third',
                'default' => 'no',
                'help' => __('Optional. Turn on to hide this show in Apple Podcasts.', 'a-ripple-song'),
            ],
            [
                'type' => 'switch',
                'key' => 'itunes_complete',
                'label' => __('iTunes Complete (itunes:complete)', 'a-ripple-song'),
                'width' => 'third',
                'default' => 'no',
                'help' => __('Optional. Turn on when this show is complete with no more episodes.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'itunes_title',
                'label' => __('iTunes Title (optional)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. Use only if you need a separate Apple-facing title.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'apple_verify',
                'label' => __('Apple Podcasts Verify Code', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. Used by Apple Podcasts to verify feed ownership.', 'a-ripple-song'),
            ],
            [
                'type' => 'url',
                'key' => 'itunes_new_feed_url',
                'label' => __('iTunes New Feed URL (itunes:new-feed-url)', 'a-ripple-song'),
                'help' => __('Optional. Only for moving your show to a new RSS feed URL.', 'a-ripple-song'),
            ],

            [
                'type' => 'separator',
                'label' => __('Podcasting 2.0', 'a-ripple-song'),
            ],
            [
                'type' => 'switch',
                'key' => 'locked',
                'label' => __('podcast:locked', 'a-ripple-song'),
                'width' => 'half',
                'default' => 'yes',
                'help' => __('Podcasting 2.0: lock feed to this publisher. Recommended, it prevents unauthorized moves.', 'a-ripple-song'),
            ],
            [
                'type' => 'email',
                'key' => 'locked_owner',
                'label' => __('podcast:locked owner (optional)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. Podcasting 2.0: email used to verify ownership during moves.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'guid',
                'label' => __('podcast:guid (optional)', 'a-ripple-song'),
                'width' => 'half',
                'default' => home_url('/'),
                'help' => __('Podcasting 2.0 GUID. If empty, feed will use site URL as fallback.', 'a-ripple-song'),
            ],
            [
                'type' => 'text',
                'key' => 'generator',
                'label' => __('Generator (optional)', 'a-ripple-song'),
                'width' => 'half',
                'help' => __('Optional. If empty, generator tag will not be included.', 'a-ripple-song'),
            ],
            [
                'type' => 'complex',
                'key' => 'funding',
                'label' => __('Podcasting 2.0 Funding Links (podcast:funding)', 'a-ripple-song'),
                'row_label' => __('Funding Link', 'a-ripple-song'),
                'row_title_field' => 'label',
                'help' => __('Optional. If empty, no podcast:funding tags will be generated. URLs should be https.', 'a-ripple-song'),
                'fields' => [
                    [
                        'type' => 'url',
                        'key' => 'url',
                        'label' => __('URL', 'a-ripple-song'),
                        'width' => 'half',
                    ],
                    [
                        'type' => 'text',
                        'key' => 'label',
                        'label' => __('Label', 'a-ripple-song'),
                        'width' => 'half',
                    ],
                ],
            ],
        ];
    }

    /**
     * Return supported podcast language options.
     *
     * @return array<string,string>
     */
    private function podcastLanguageOptions(): array
    {
        return [
            'en-US' => 'en-US',
            'zh-CN' => 'zh-CN',
        ];
    }

    /**
     * Return Apple Podcasts category options; "Parent::Child" values keep the old feed format.
     *
     * @return array<string,string>
     */
    private function itunesCategories(): array
    {
        return [
            'Arts' => __('Arts', 'a-ripple-song'),
            'Arts::Books' => __('Arts > Books', 'a-ripple-song'),
            'Arts::Design' => __('Arts > Design', 'a-ripple-song'),
            'Arts::Fashion & Beauty' => __('Arts > Fashion & Beauty', 'a-ripple-song'),
            'Arts::Food' => __('Arts > Food', 'a-ripple-song'),
            'Arts::Performing Arts' => __('Arts > Performing Arts', 'a-ripple-song'),
            'Arts::Visual Arts' => __('Arts > Visual Arts', 'a-ripple-song'),
            'Business' => __('Business', 'a-ripple-song'),
            'Business::Careers' => __('Business > Careers', 'a-ripple-song'),
            'Business::Entrepreneurship' => __('Business > Entrepreneurship', 'a-ripple-song'),
            'Business::Investing' => __('Business > Investing', 'a-ripple-song'),
            'Business::Management' => __('Business > Management', 'a-ripple-song'),
            'Business::Marketing' => __('Business > Marketing', 'a-ripple-song'),
            'Business::Non-Profit' => __('Business > Non-Profit', 'a-ripple-song'),
            'Comedy' => __('Comedy', 'a-ripple-song'),
            'Comedy::Comedy Interviews' => __('Comedy > Comedy Interviews', 'a-ripple-song'),
            'Comedy::Improv' => __('Comedy > Improv', 'a-ripple-song'),
            'Comedy::Stand-Up' => __('Comedy > Stand-Up', 'a-ripple-song'),
            'Education' => __('Education', 'a-ripple-song'),
            'Education::Courses' => __('Education > Courses', 'a-ripple-song'),
            'Education::How To' => __('Education > How To', 'a-ripple-song'),
            'Education::Language Learning' => __('Education > Language Learning', 'a-ripple-song'),
            'Education::Self-Improvement' => __('Education > Self-Improvement', 'a-ripple-song'),
            'Fiction' => __('Fiction', 'a-ripple-song'),
            'Fiction::Comedy Fiction' => __('Fiction > Comedy Fiction', 'a-ripple-song'),
            'Fiction::Drama' => __('Fiction > Drama', 'a-ripple-song'),
            'Fiction::Science Fiction' => __('Fiction > Science Fiction', 'a-ripple-song'),
            'Government' => __('Government', 'a-ripple-song'),
            'History' => __('History', 'a-ripple-song'),
            'Health & Fitness' => __('Health & Fitness', 'a-ripple-song'),
            'Health & Fitness::Alternative Health' => __('Health & Fitness > Alternative Health', 'a-ripple-song'),
            'Health & Fitness::Fitness' => __('Health & Fitness > Fitness', 'a-ripple-song'),
            'Health & Fitness::Medicine' => __('Health & Fitness > Medicine', 'a-ripple-song'),
            'Health & Fitness::Mental Health' => __('Health & Fitness > Mental Health', 'a-ripple-song'),
            'Health & Fitness::Nutrition' => __('Health & Fitness > Nutrition', 'a-ripple-song'),
            'Health & Fitness::Sexuality' => __('Health & Fitness > Sexuality', 'a-ripple-song'),
            'Kids & Family' => __('Kids & Family', 'a-ripple-song'),
            'Kids & Family::Education for Kids' => __('Kids & Family > Education for Kids', 'a-ripple-song'),
            'Kids & Family::Parenting' => __('Kids & Family > Parenting', 'a-ripple-song'),
            'Kids & Family::Pets & Animals' => __('Kids & Family > Pets & Animals', 'a-ripple-song'),
            'Kids & Family::Stories for Kids' => __('Kids & Family > Stories for Kids', 'a-ripple-song'),
            'Leisure' => __('Leisure', 'a-ripple-song'),
            'Leisure::Animation & Manga' => __('Leisure > Animation & Manga', 'a-ripple-song'),
            'Leisure::Automotive' => __('Leisure > Automotive', 'a-ripple-song'),
            'Leisure::Aviation' => __('Leisure > Aviation', 'a-ripple-song'),
            'Leisure::Crafts' => __('Leisure > Crafts', 'a-ripple-song'),
            'Leisure::Games' => __('Leisure > Games', 'a-ripple-song'),
            'Leisure::Hobbies' => __('Leisure > Hobbies', 'a-ripple-song'),
            'Leisure::Home & Garden' => __('Leisure > Home & Garden', 'a-ripple-song'),
            'Leisure::Video Games' => __('Leisure > Video Games', 'a-ripple-song'),
            'Music' => __('Music', 'a-ripple-song'),
            'Music::Music Commentary' => __('Music > Music Commentary', 'a-ripple-song'),
            'Music::Music History' => __('Music > Music History', 'a-ripple-song'),
            'Music::Music Interviews' => __('Music > Music Interviews', 'a-ripple-song'),
            'News' => __('News', 'a-ripple-song'),
            'News::Business News' => __('News > Business News', 'a-ripple-song'),
            'News::Daily News' => __('News > Daily News', 'a-ripple-song'),
            'News::Entertainment News' => __('News > Entertainment News', 'a-ripple-song'),
            'News::News Commentary' => __('News > News Commentary', 'a-ripple-song'),
            'News::Politics' => __('News > Politics', 'a-ripple-song'),
            'News::Sports News' => __('News > Sports News', 'a-ripple-song'),
            'News::Tech News' => __('News > Tech News', 'a-ripple-song'),
            'Religion & Spirituality' => __('Religion & Spirituality', 'a-ripple-song'),
            'Religion & Spirituality::Buddhism' => __('Religion & Spirituality > Buddhism', 'a-ripple-song'),
            'Religion & Spirituality::Christianity' => __('Religion & Spirituality > Christianity', 'a-ripple-song'),
            'Religion & Spirituality::Hinduism' => __('Religion & Spirituality > Hinduism', 'a-ripple-song'),
            'Religion & Spirituality::Islam' => __('Religion & Spirituality > Islam', 'a-ripple-song'),
            'Religion & Spirituality::Judaism' => __('Religion & Spirituality > Judaism', 'a-ripple-song'),
            'Religion & Spirituality::Religion' => __('Religion & Spirituality > Religion', 'a-ripple-song'),
            'Religion & Spirituality::Spirituality' => __('Religion & Spirituality > Spirituality', 'a-ripple-song'),
            'Science' => __('Science', 'a-ripple-song'),
            'Science::Astronomy' => __('Science > Astronomy', 'a-ripple-song'),
            'Science::Chemistry' => __('Science > Chemistry', 'a-ripple-song'),
            'Science::Earth Sciences' => __('Science > Earth Sciences', 'a-ripple-song'),
            'Science::Life Sciences' => __('Science > Life Sciences', 'a-ripple-song'),
            'Science::Mathematics' => __('Science > Mathematics', 'a-ripple-song'),
            'Science::Natural Sciences' => __('Science > Natural Sciences', 'a-ripple-song'),
            'Science::Nature' => __('Science > Nature', 'a-ripple-song'),
            'Science::Physics' => __('Science > Physics', 'a-ripple-song'),
            'Society & Culture' => __('Society & Culture', 'a-ripple-song'),
            'Society & Culture::Documentary' => __('Society & Culture > Documentary', 'a-ripple-song'),
            'Society & Culture::Personal Journals' => __('Society & Culture > Personal Journals', 'a-ripple-song'),
            'Society & Culture::Philosophy' => __('Society & Culture > Philosophy', 'a-ripple-song'),
            'Society & Culture::Places & Travel' => __('Society & Culture > Places & Travel', 'a-ripple-song'),
            'Society & Culture::Relationships' => __('Society & Culture > Relationships', 'a-ripple-song'),
            'Sports' => __('Sports', 'a-ripple-song'),
            'Sports::Baseball' => __('Sports > Baseball', 'a-ripple-song'),
            'Sports::Basketball' => __('Sports > Basketball', 'a-ripple-song'),
            'Sports::Cricket' => __('Sports > Cricket', 'a-ripple-song'),
            'Sports::Fantasy Sports' => __('Sports > Fantasy Sports', 'a-ripple-song'),
            'Sports::Football' => __('Sports > Football', 'a-ripple-song'),
            'Sports::Golf' => __('Sports > Golf', 'a-ripple-song'),
            'Sports::Hockey' => __('Sports > Hockey', 'a-ripple-song'),
            'Sports::Rugby' => __('Sports > Rugby', 'a-ripple-song'),
            'Sports::Running' => __('Sports > Running', 'a-ripple-song'),
            'Sports::Soccer' => __('Sports > Soccer', 'a-ripple-song'),
            'Sports::Swimming' => __('Sports > Swimming', 'a-ripple-song'),
            'Sports::Tennis' => __('Sports > Tennis', 'a-ripple-song'),
            'Sports::Volleyball' => __('Sports > Volleyball', 'a-ripple-song'),
            'Technology' => __('Technology', 'a-ripple-song'),
            'True Crime' => __('True Crime', 'a-ripple-song'),
            'TV & Film' => __('TV & Film', 'a-ripple-song'),
            'TV & Film::After Shows' => __('TV & Film > After Shows', 'a-ripple-song'),
            'TV & Film::Film History' => __('TV & Film > Film History', 'a-ripple-song'),
            'TV & Film::Film Interviews' => __('TV & Film > Film Interviews', 'a-ripple-song'),
            'TV & Film::Film Reviews' => __('TV & Film > Film Reviews', 'a-ripple-song'),
            'TV & Film::TV Reviews' => __('TV & Film > TV Reviews', 'a-ripple-song'),
        ];
    }
}
