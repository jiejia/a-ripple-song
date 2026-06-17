<?php

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

/**
 * Banner Carousel Widget
 * Display a banner carousel.
 */
class BannerCarouselWidget extends Widget
{
    /**
     * Keep the original WordPress widget id for existing widget instances.
     *
     * @var string
     */
    protected $widget_id_prefix = '';

    /**
     * Create the widget and its Carbon Fields admin form.
     */
    public function __construct()
    {
        $this->setup(
            'banner_carousel_widget',
            __('aripplesong - Banner Carousel', 'sage'),
            __('Display banner carousel with images', 'sage'),
            [
                Field::make('complex', 'banner_carousel_slides', __('Banner Slides:', 'sage'))
                    ->add_fields([
                        Field::make('image', 'image', __('Image URL:', 'sage'))
                            ->set_value_type('url'),
                        Field::make('text', 'link', __('Link URL (optional):', 'sage')),
                        Field::make('select', 'link_target', __('Link Target:', 'sage'))
                            ->add_options([
                                '_self' => __('Current Page', 'sage'),
                                '_blank' => __('New Tab', 'sage'),
                            ])
                            ->set_default_value('_self'),
                        Field::make('text', 'description', __('Description:', 'sage')),
                    ]),
            ]
        );
    }

    /**
     * Render the Carbon Fields form with legacy values mapped to unique keys.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function form($instance)
    {
        parent::form($this->withLegacyAliases($instance, [
            'banner_carousel_slides' => 'slides',
        ]));
    }

    /**
     * Render the widget with values normalized from legacy and Carbon Fields storage.
     *
     * @param  array<string,mixed>  $args  Widget wrapper arguments.
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $slides = $this->slidesValue($instance);
        $carousel_id = 'banner-carousel-'.$this->id;

        echo \Roots\view('widgets.banner-carousel', [
            'slides' => $slides,
            'carousel_id' => $carousel_id,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Return normalized slides for the front-end template.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @return array<int,array<string,string>>
     */
    private function slidesValue(array $instance): array
    {
        $slides = [];

        $storedSlides = $instance['banner_carousel_slides'] ?? $instance['slides'] ?? [];

        if (empty($storedSlides) || ! is_array($storedSlides)) {
            return $slides;
        }

        foreach ($storedSlides as $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $image_url = $this->imageUrl($slide['image'] ?? '');

            if ($image_url === '') {
                continue;
            }

            $slides[] = [
                'image' => $image_url,
                'link' => ! empty($slide['link']) ? esc_url_raw($slide['link']) : '',
                'description' => isset($slide['description']) ? sanitize_text_field($slide['description']) : '',
                'link_target' => isset($slide['link_target']) && in_array($slide['link_target'], ['_self', '_blank'], true) ? $slide['link_target'] : '_self',
            ];
        }

        return $slides;
    }

    /**
     * Return an image URL from a legacy URL or attachment id.
     *
     * @param  mixed  $image  Stored image value.
     */
    private function imageUrl($image): string
    {
        if (is_numeric($image)) {
            return wp_get_attachment_image_url((int) $image, 'full') ?: '';
        }

        return is_string($image) && $image !== '' ? esc_url_raw($image) : '';
    }

    /**
     * Copy legacy instance values to unique Carbon Fields keys for editing.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  array<string,string>  $aliases  New key to legacy key map.
     * @return array<string,mixed>
     */
    private function withLegacyAliases(array $instance, array $aliases): array
    {
        foreach ($aliases as $newKey => $legacyKey) {
            if (! array_key_exists($newKey, $instance) && array_key_exists($legacyKey, $instance)) {
                $instance[$newKey] = $instance[$legacyKey];
            }
        }

        return $instance;
    }
}
