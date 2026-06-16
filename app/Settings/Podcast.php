<?php

namespace App\Settings;

use App\Abstracts\SettingAbstract;
use App\Theme;
use Carbon_Fields\Field;

/**
 * Carbon Fields podcast settings page.
 */
class Podcast extends SettingAbstract
{
    /**
     * Return the prefix used for all podcast option keys.
     *
     * @return string
     */
    public function fieldPrefix(): string
    {
        return Theme::PREFIX . '_podcast_settings_';
    }

    /**
     * Return the Carbon Fields page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return Theme::PREFIX . '_podcast_settings';
    }

    /**
     * Return the podcast settings page title.
     *
     * @return string
     */
    public function pageTitle(): string
    {
        return __('Podcast Settings', 'sage');
    }

    /**
     * Return the parent menu slug for this settings page.
     *
     * @return string
     */
    public function parentPageSlug(): string
    {
        return Theme::SLUG;
    }

    /**
     * Return all podcast setting fields.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array
    {
        // Reuse option lists across related select fields.
        $notSetOptions = ['' => __('(not set)', 'sage')];
        // Reuse yes/no choices for iTunes boolean tags.
        $yesNoOptions = ['no' => __('no', 'sage'), 'yes' => __('yes', 'sage')];

        /** @var \Carbon_Fields\Field\Complex_Field $fundingField */
        $fundingField = Field::make('complex', $this->fieldName('funding'), __('Podcasting 2.0 Funding Links (podcast:funding)', 'sage'));
        $fundingField->set_help_text(__('Optional. If empty, no podcast:funding tags will be generated. URLs should be https.', 'sage'));
        $fundingField->add_fields([
            Field::make('text', 'url', __('URL', 'sage'))
                ->set_attribute('type', 'url')
                ->set_width(60),
            Field::make('text', 'label', __('Label', 'sage'))->set_width(40),
        ]);

        /** @var \Carbon_Fields\Field\Html_Field $rssUrlField */
        $rssUrlField = Field::make('html', $this->fieldName('rss_url'), __('Podcast RSS URL', 'sage'));
        $rssUrlField->set_html([$this, 'renderPodcastFeedUrlField']);

        /** @var \Carbon_Fields\Field\Image_Field $coverField */
        $coverField = Field::make('image', $this->fieldName('cover'), __('Podcast Cover (1400-3000px square)', 'sage'));
        $coverField
            ->set_value_type('url')
            ->set_help_text(__('Required. Square JPG/PNG between 1400-3000px for itunes:image. Apple recommends keeping the file under 512KB.', 'sage'))
            ->set_required(true);

        /** @var \Carbon_Fields\Field\Select_Field $explicitField */
        $explicitField = Field::make('select', $this->fieldName('explicit'), __('Default Explicit Flag', 'sage'));
        $explicitField
            ->set_options([
                'clean' => __('clean (no explicit content)', 'sage'),
                'explicit' => __('explicit', 'sage'),
            ])
            ->set_default_value('clean')
            ->set_help_text(__('Required. Single-episode value can override.', 'sage'))
            ->set_required(true);

        /** @var \Carbon_Fields\Field\Select_Field $languageField */
        $languageField = Field::make('select', $this->fieldName('language'), __('Language (RFC 5646)', 'sage'));
        $languageField
            ->set_options($this->podcastLanguageOptions())
            ->set_default_value((string) (get_bloginfo('language') ?: 'en-US'))
            ->set_help_text(__('Required. Typically en-US, zh-CN, etc.', 'sage'))
            ->set_required(true);

        /** @var \Carbon_Fields\Field\Select_Field $primaryCategoryField */
        $primaryCategoryField = Field::make('select', $this->fieldName('category_primary'), __('Primary Category (Apple Podcasts)', 'sage'));
        $primaryCategoryField
            ->set_options($notSetOptions + $this->itunesCategories())
            ->set_help_text(__('Required by Apple Podcasts. Choose at least a primary category.', 'sage'))
            ->set_required(true);

        /** @var \Carbon_Fields\Field\Select_Field $secondaryCategoryField */
        $secondaryCategoryField = Field::make('select', $this->fieldName('category_secondary'), __('Secondary Category (optional)', 'sage'));
        $secondaryCategoryField
            ->set_options($notSetOptions + $this->itunesCategories())
            ->set_help_text(__('Optional. Some directories support a second category.', 'sage'));

        /** @var \Carbon_Fields\Field\Select_Field $itunesTypeField */
        $itunesTypeField = Field::make('select', $this->fieldName('itunes_type'), __('iTunes Type (itunes:type)', 'sage'));
        $itunesTypeField
            ->set_options($notSetOptions + [
                'episodic' => __('episodic', 'sage'),
                'serial' => __('serial', 'sage'),
            ])
            ->set_help_text(__('Optional. Apple Podcasts: episodic or serial.', 'sage'));

        /** @var \Carbon_Fields\Field\Select_Field $itunesBlockField */
        $itunesBlockField = Field::make('select', $this->fieldName('itunes_block'), __('iTunes Block (itunes:block)', 'sage'));
        $itunesBlockField
            ->set_options($yesNoOptions)
            ->set_default_value('no')
            ->set_help_text(__('Optional. yes = hide this show in Apple Podcasts.', 'sage'));

        /** @var \Carbon_Fields\Field\Select_Field $itunesCompleteField */
        $itunesCompleteField = Field::make('select', $this->fieldName('itunes_complete'), __('iTunes Complete (itunes:complete)', 'sage'));
        $itunesCompleteField
            ->set_options($yesNoOptions)
            ->set_default_value('no')
            ->set_help_text(__('Optional. yes = this show is complete with no more episodes.', 'sage'));

        /** @var \Carbon_Fields\Field\Select_Field $lockedField */
        $lockedField = Field::make('select', $this->fieldName('locked'), __('podcast:locked', 'sage'));
        $lockedField
            ->set_options([
                'yes' => __('yes (recommended, prevents unauthorized moves)', 'sage'),
                'no' => __('no', 'sage'),
            ])
            ->set_default_value('yes')
            ->set_help_text(__('Podcasting 2.0: lock feed to this publisher.', 'sage'));

        return [
            $rssUrlField,
            Field::make('text', $this->fieldName('title'), __('Podcast Title', 'sage'))
                ->set_default_value((string) get_bloginfo('name'))
                ->set_help_text(__('Required. If empty, falls back to site title.', 'sage'))
                ->set_required(true),
            Field::make('text', $this->fieldName('subtitle'), __('Podcast Subtitle', 'sage'))
                ->set_help_text(__('Short tagline shown in some apps.', 'sage')),
            Field::make('textarea', $this->fieldName('description'), __('Podcast Description', 'sage'))
                ->set_default_value((string) get_bloginfo('description'))
                ->set_help_text(__('Required. Plain text description of the show.', 'sage'))
                ->set_required(true),
            Field::make('text', $this->fieldName('author'), __('Podcast Author (itunes:author)', 'sage'))
                ->set_default_value((string) get_bloginfo('name'))
                ->set_help_text(__('Required. Displayed as show author in directories.', 'sage'))
                ->set_required(true),
            Field::make('text', $this->fieldName('owner_name'), __('Owner Name', 'sage'))
                ->set_default_value((string) get_bloginfo('name'))
                ->set_help_text(__('Required. For itunes:owner itunes:name.', 'sage'))
                ->set_required(true),
            Field::make('text', $this->fieldName('owner_email'), __('Owner Email', 'sage'))
                ->set_attribute('type', 'email')
                ->set_default_value((string) get_bloginfo('admin_email'))
                ->set_help_text(__('Required. For itunes:owner itunes:email. Use a monitored inbox.', 'sage'))
                ->set_required(true),
            $coverField,
            $explicitField,
            $languageField,
            $primaryCategoryField,
            $secondaryCategoryField,
            Field::make('text', $this->fieldName('copyright'), __('Copyright (optional)', 'sage'))
                ->set_help_text(__('Optional. For copyright.', 'sage')),
            $itunesTypeField,
            Field::make('text', $this->fieldName('itunes_title'), __('iTunes Title (optional)', 'sage'))
                ->set_help_text(__('Optional. Use only if you need a separate Apple-facing title.', 'sage')),
            $itunesBlockField,
            $itunesCompleteField,
            Field::make('text', $this->fieldName('itunes_new_feed_url'), __('iTunes New Feed URL (itunes:new-feed-url)', 'sage'))
                ->set_attribute('type', 'url')
                ->set_help_text(__('Optional. Only for moving your show to a new RSS feed URL.', 'sage')),
            $lockedField,
            Field::make('text', $this->fieldName('locked_owner'), __('podcast:locked owner (optional)', 'sage'))
                ->set_attribute('type', 'email')
                ->set_help_text(__('Optional. Podcasting 2.0: email used to verify ownership during moves.', 'sage')),
            Field::make('text', $this->fieldName('guid'), __('podcast:guid (optional)', 'sage'))
                ->set_default_value(home_url('/'))
                ->set_help_text(__('Podcasting 2.0 GUID. If empty, feed will use site URL as fallback.', 'sage')),
            Field::make('text', $this->fieldName('apple_verify'), __('Apple Podcasts Verify Code', 'sage'))
                ->set_help_text(__('Optional. Used by Apple Podcasts to verify feed ownership.', 'sage')),
            $fundingField,
            Field::make('text', $this->fieldName('generator'), __('Generator (optional)', 'sage'))
                ->set_help_text(__('Optional. If empty, generator tag will not be included.', 'sage')),
        ];
    }

    /**
     * Render the readonly podcast feed URL field.
     *
     * @return string
     */
    public function renderPodcastFeedUrlField(): string
    {
        // Render a copy-friendly readonly URL field.
        return sprintf(
            '<input type="text" class="regular-text" value="%1$s" readonly onclick="this.select();" /><p class="description">%2$s</p>',
            esc_attr($this->podcastFeedUrl()),
            esc_html__('Your podcast RSS feed URL. Click to select and copy.', 'sage')
        );
    }

    /**
     * Return default podcast settings.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array
    {
        // Keep defaults aligned with the old plugin settings page.
        return [
            'title' => get_bloginfo('name'),
            'subtitle' => '',
            'description' => get_bloginfo('description'),
            'author' => get_bloginfo('name'),
            'owner_name' => get_bloginfo('name'),
            'owner_email' => get_bloginfo('admin_email'),
            'cover' => '',
            'explicit' => 'clean',
            'language' => get_bloginfo('language') ?: 'en-US',
            'category_primary' => '',
            'category_secondary' => '',
            'copyright' => '',
            'itunes_type' => '',
            'itunes_title' => '',
            'itunes_block' => 'no',
            'itunes_complete' => 'no',
            'itunes_new_feed_url' => '',
            'locked' => 'yes',
            'locked_owner' => '',
            'guid' => home_url('/'),
            'apple_verify' => '',
            'funding' => [],
            'generator' => '',
        ];
    }

    /**
     * Return the podcast feed URL for the current permalink mode.
     *
     * @return string
     */
    private function podcastFeedUrl(): string
    {
        // Build the feed URL using the site's permalink structure.
        $permalinkStructure = get_option('permalink_structure');

        if (empty($permalinkStructure)) {
            return home_url('/?feed=podcast');
        }

        if (strpos((string) $permalinkStructure, '/index.php/') === 0) {
            return home_url('/index.php/feed/podcast/');
        }

        return home_url('/feed/podcast/');
    }

    /**
     * Return supported podcast language options.
     *
     * @return array<string,string>
     */
    private function podcastLanguageOptions(): array
    {
        // Keep the common language list from the previous plugin.
        return [
            'en-US' => 'en-US',
            'zh-CN' => 'zh-CN',
        ];
    }

    /**
     * Return Apple Podcasts category options.
     *
     * @return array<string,string>
     */
    private function itunesCategories(): array
    {
        // Keep values compatible with the previous plugin's feed rendering.
        return [
            'Arts' => __('Arts', 'sage'),
            'Arts::Books' => __('Arts > Books', 'sage'),
            'Arts::Design' => __('Arts > Design', 'sage'),
            'Arts::Fashion & Beauty' => __('Arts > Fashion & Beauty', 'sage'),
            'Arts::Food' => __('Arts > Food', 'sage'),
            'Arts::Performing Arts' => __('Arts > Performing Arts', 'sage'),
            'Arts::Visual Arts' => __('Arts > Visual Arts', 'sage'),
            'Business' => __('Business', 'sage'),
            'Business::Careers' => __('Business > Careers', 'sage'),
            'Business::Entrepreneurship' => __('Business > Entrepreneurship', 'sage'),
            'Business::Investing' => __('Business > Investing', 'sage'),
            'Business::Management' => __('Business > Management', 'sage'),
            'Business::Marketing' => __('Business > Marketing', 'sage'),
            'Business::Non-Profit' => __('Business > Non-Profit', 'sage'),
            'Comedy' => __('Comedy', 'sage'),
            'Comedy::Comedy Interviews' => __('Comedy > Comedy Interviews', 'sage'),
            'Comedy::Improv' => __('Comedy > Improv', 'sage'),
            'Comedy::Stand-Up' => __('Comedy > Stand-Up', 'sage'),
            'Education' => __('Education', 'sage'),
            'Education::Courses' => __('Education > Courses', 'sage'),
            'Education::How To' => __('Education > How To', 'sage'),
            'Education::Language Learning' => __('Education > Language Learning', 'sage'),
            'Education::Self-Improvement' => __('Education > Self-Improvement', 'sage'),
            'Fiction' => __('Fiction', 'sage'),
            'Fiction::Comedy Fiction' => __('Fiction > Comedy Fiction', 'sage'),
            'Fiction::Drama' => __('Fiction > Drama', 'sage'),
            'Fiction::Science Fiction' => __('Fiction > Science Fiction', 'sage'),
            'Government' => __('Government', 'sage'),
            'History' => __('History', 'sage'),
            'Health & Fitness' => __('Health & Fitness', 'sage'),
            'Health & Fitness::Alternative Health' => __('Health & Fitness > Alternative Health', 'sage'),
            'Health & Fitness::Fitness' => __('Health & Fitness > Fitness', 'sage'),
            'Health & Fitness::Medicine' => __('Health & Fitness > Medicine', 'sage'),
            'Health & Fitness::Mental Health' => __('Health & Fitness > Mental Health', 'sage'),
            'Health & Fitness::Nutrition' => __('Health & Fitness > Nutrition', 'sage'),
            'Health & Fitness::Sexuality' => __('Health & Fitness > Sexuality', 'sage'),
            'Kids & Family' => __('Kids & Family', 'sage'),
            'Kids & Family::Education for Kids' => __('Kids & Family > Education for Kids', 'sage'),
            'Kids & Family::Parenting' => __('Kids & Family > Parenting', 'sage'),
            'Kids & Family::Pets & Animals' => __('Kids & Family > Pets & Animals', 'sage'),
            'Kids & Family::Stories for Kids' => __('Kids & Family > Stories for Kids', 'sage'),
            'Leisure' => __('Leisure', 'sage'),
            'Leisure::Animation & Manga' => __('Leisure > Animation & Manga', 'sage'),
            'Leisure::Automotive' => __('Leisure > Automotive', 'sage'),
            'Leisure::Aviation' => __('Leisure > Aviation', 'sage'),
            'Leisure::Crafts' => __('Leisure > Crafts', 'sage'),
            'Leisure::Games' => __('Leisure > Games', 'sage'),
            'Leisure::Hobbies' => __('Leisure > Hobbies', 'sage'),
            'Leisure::Home & Garden' => __('Leisure > Home & Garden', 'sage'),
            'Leisure::Video Games' => __('Leisure > Video Games', 'sage'),
            'Music' => __('Music', 'sage'),
            'Music::Music Commentary' => __('Music > Music Commentary', 'sage'),
            'Music::Music History' => __('Music > Music History', 'sage'),
            'Music::Music Interviews' => __('Music > Music Interviews', 'sage'),
            'News' => __('News', 'sage'),
            'News::Business News' => __('News > Business News', 'sage'),
            'News::Daily News' => __('News > Daily News', 'sage'),
            'News::Entertainment News' => __('News > Entertainment News', 'sage'),
            'News::News Commentary' => __('News > News Commentary', 'sage'),
            'News::Politics' => __('News > Politics', 'sage'),
            'News::Sports News' => __('News > Sports News', 'sage'),
            'News::Tech News' => __('News > Tech News', 'sage'),
            'Religion & Spirituality' => __('Religion & Spirituality', 'sage'),
            'Religion & Spirituality::Buddhism' => __('Religion & Spirituality > Buddhism', 'sage'),
            'Religion & Spirituality::Christianity' => __('Religion & Spirituality > Christianity', 'sage'),
            'Religion & Spirituality::Hinduism' => __('Religion & Spirituality > Hinduism', 'sage'),
            'Religion & Spirituality::Islam' => __('Religion & Spirituality > Islam', 'sage'),
            'Religion & Spirituality::Judaism' => __('Religion & Spirituality > Judaism', 'sage'),
            'Religion & Spirituality::Religion' => __('Religion & Spirituality > Religion', 'sage'),
            'Religion & Spirituality::Spirituality' => __('Religion & Spirituality > Spirituality', 'sage'),
            'Science' => __('Science', 'sage'),
            'Science::Astronomy' => __('Science > Astronomy', 'sage'),
            'Science::Chemistry' => __('Science > Chemistry', 'sage'),
            'Science::Earth Sciences' => __('Science > Earth Sciences', 'sage'),
            'Science::Life Sciences' => __('Science > Life Sciences', 'sage'),
            'Science::Mathematics' => __('Science > Mathematics', 'sage'),
            'Science::Natural Sciences' => __('Science > Natural Sciences', 'sage'),
            'Science::Nature' => __('Science > Nature', 'sage'),
            'Science::Physics' => __('Science > Physics', 'sage'),
            'Society & Culture' => __('Society & Culture', 'sage'),
            'Society & Culture::Documentary' => __('Society & Culture > Documentary', 'sage'),
            'Society & Culture::Personal Journals' => __('Society & Culture > Personal Journals', 'sage'),
            'Society & Culture::Philosophy' => __('Society & Culture > Philosophy', 'sage'),
            'Society & Culture::Places & Travel' => __('Society & Culture > Places & Travel', 'sage'),
            'Society & Culture::Relationships' => __('Society & Culture > Relationships', 'sage'),
            'Sports' => __('Sports', 'sage'),
            'Sports::Baseball' => __('Sports > Baseball', 'sage'),
            'Sports::Basketball' => __('Sports > Basketball', 'sage'),
            'Sports::Cricket' => __('Sports > Cricket', 'sage'),
            'Sports::Fantasy Sports' => __('Sports > Fantasy Sports', 'sage'),
            'Sports::Football' => __('Sports > Football', 'sage'),
            'Sports::Golf' => __('Sports > Golf', 'sage'),
            'Sports::Hockey' => __('Sports > Hockey', 'sage'),
            'Sports::Rugby' => __('Sports > Rugby', 'sage'),
            'Sports::Running' => __('Sports > Running', 'sage'),
            'Sports::Soccer' => __('Sports > Soccer', 'sage'),
            'Sports::Swimming' => __('Sports > Swimming', 'sage'),
            'Sports::Tennis' => __('Sports > Tennis', 'sage'),
            'Sports::Volleyball' => __('Sports > Volleyball', 'sage'),
            'Technology' => __('Technology', 'sage'),
            'True Crime' => __('True Crime', 'sage'),
            'TV & Film' => __('TV & Film', 'sage'),
            'TV & Film::After Shows' => __('TV & Film > After Shows', 'sage'),
            'TV & Film::Film History' => __('TV & Film > Film History', 'sage'),
            'TV & Film::Film Interviews' => __('TV & Film > Film Interviews', 'sage'),
            'TV & Film::Film Reviews' => __('TV & Film > Film Reviews', 'sage'),
            'TV & Film::TV Reviews' => __('TV & Film > TV Reviews', 'sage'),
        ];
    }
}
