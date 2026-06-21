<?php

namespace App\Widgets;

use App\Abstracts\WidgetAbstract;

/**
 * Banner Carousel Widget
 * Display a banner carousel.
 */
class BannerCarouselWidget extends WidgetAbstract
{
    /**
     * Return the WordPress widget id base.
     */
    public static function idBase(): string
    {
        return 'banner_carousel_widget';
    }

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [
            'banner_carousel_slides' => 'slides',
        ];
    }

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::idBase(),
            __('aripplesong - Banner Carousel', 'sage'),
            ['description' => __('Display banner carousel with images', 'sage')]
        );
    }

    /**
     * Front-end display of widget.
     *
     * @param  array<string,mixed>  $args  Widget wrapper arguments.
     * @param  array<string,mixed>  $instance  Saved widget option values.
     */
    public function widget($args, $instance): void
    {
        echo $args['before_widget'];

        $slides = $this->getSanitizedSlides($instance['slides'] ?? []);
        $carouselId = 'banner-carousel-'.$this->id;

        echo \Roots\view('widgets.banner-carousel', [
            'slides' => $slides,
            'carousel_id' => $carouselId,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form displayed in the WordPress admin.
     *
     * @param  array<string,mixed>  $instance  Current widget settings.
     */
    public function form($instance): void
    {
        $slides = $this->getSanitizedSlides($instance['slides'] ?? []);

        if (empty($slides)) {
            $slides[] = $this->getEmptySlide();
        }

        $widgetId = $this->get_field_id('slides');
        $fieldPrefix = $this->get_field_name('slides');
        ?>
        <div class="banner-carousel-widget-form"
             data-widget-id="<?php echo esc_attr($widgetId); ?>"
             data-field-prefix="<?php echo esc_attr($fieldPrefix); ?>">
            <p><strong><?php esc_html_e('Banner Slides:', 'sage'); ?></strong></p>

            <div class="banner-slides-container" id="<?php echo esc_attr($widgetId); ?>_container">
                <?php foreach ($slides as $index => $slide): ?>
                    <div class="banner-slide-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div class="banner-image-url-row" style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Image URL:', 'sage'); ?>
                            </label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text"
                                       class="widefat banner-image-url"
                                       name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][image]"
                                       value="<?php echo esc_attr($slide['image']); ?>"
                                       placeholder="<?php echo esc_attr__('Image URL', 'sage'); ?>"
                                       style="flex: 1;">
                                <button type="button" class="button banner-select-image" style="flex-shrink: 0;">
                                    <?php esc_html_e('Select Image', 'sage'); ?>
                                </button>
                            </div>

                            <?php if (! empty($slide['image'])): ?>
                                <div class="banner-image-preview" style="margin-top: 8px;">
                                    <img src="<?php echo esc_url($slide['image']); ?>" style="max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Link URL (optional):', 'sage'); ?>
                            </label>
                            <input type="url"
                                   class="widefat banner-link-url"
                                   name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][link]"
                                   value="<?php echo esc_attr($slide['link']); ?>"
                                   placeholder="https://example.com">
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Link Target:', 'sage'); ?>
                            </label>
                            <select class="widefat banner-link-target"
                                    name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][link_target]">
                                <option value="_self" <?php selected($slide['link_target'], '_self'); ?>>
                                    <?php esc_html_e('Current Page', 'sage'); ?>
                                </option>
                                <option value="_blank" <?php selected($slide['link_target'], '_blank'); ?>>
                                    <?php esc_html_e('New Tab', 'sage'); ?>
                                </option>
                            </select>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Description:', 'sage'); ?>
                            </label>
                            <input type="text"
                                   class="widefat banner-description"
                                   name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][description]"
                                   value="<?php echo esc_attr($slide['description']); ?>"
                                   placeholder="<?php echo esc_attr__('Image description', 'sage'); ?>">
                        </div>

                        <div style="text-align: right;">
                            <button type="button" class="button banner-remove-slide button-link-delete">
                                <?php esc_html_e('Delete', 'sage'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" class="banner-slides-flag" name="<?php echo esc_attr($fieldPrefix); ?>[__flag]" value="1">

            <p>
                <button type="button" class="button banner-add-slide" data-widget-id="<?php echo esc_attr($widgetId); ?>">
                    <?php esc_html_e('+ Add Banner', 'sage'); ?>
                </button>
            </p>
        </div>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param  array<string,mixed>  $newInstance  New widget settings submitted from the form.
     * @param  array<string,mixed>  $oldInstance  Previous widget settings.
     * @return array<string,mixed>
     */
    public function update($newInstance, $oldInstance): array
    {
        return [
            'slides' => $this->getSanitizedSlides($newInstance['slides'] ?? []),
        ];
    }

    /**
     * Return the default empty slide structure.
     *
     * @return array<string,string>
     */
    protected function getEmptySlide(): array
    {
        return [
            'image' => '',
            'link' => '',
            'description' => '',
            'link_target' => '_self',
        ];
    }

    /**
     * Sanitize the repeatable slide array.
     *
     * @param  mixed  $slides  Raw slide configuration.
     * @return array<int,array<string,string>>
     */
    protected function getSanitizedSlides($slides): array
    {
        $sanitizedSlides = [];

        if (! is_array($slides)) {
            return $sanitizedSlides;
        }

        foreach ($slides as $slideKey => $slide) {
            if ($slideKey === '__flag' || ! is_array($slide)) {
                continue;
            }

            $imageUrl = $this->resolveImageUrl($slide['image'] ?? '');

            if ($imageUrl === '') {
                continue;
            }

            $linkTarget = ! empty($slide['link_target']) && in_array($slide['link_target'], ['_self', '_blank'], true)
                ? (string) $slide['link_target']
                : '_self';

            $sanitizedSlides[] = [
                'image' => $imageUrl,
                'link' => ! empty($slide['link']) ? esc_url_raw((string) $slide['link']) : '',
                'description' => ! empty($slide['description']) ? sanitize_text_field((string) $slide['description']) : '',
                'link_target' => $linkTarget,
            ];
        }

        return $sanitizedSlides;
    }

    /**
     * Return an image URL from a stored URL or attachment id.
     *
     * @param  mixed  $image  Stored image value.
     */
    protected function resolveImageUrl($image): string
    {
        if (is_numeric($image)) {
            return wp_get_attachment_image_url((int) $image, 'full') ?: '';
        }

        return is_string($image) && $image !== '' ? esc_url_raw($image) : '';
    }
}
