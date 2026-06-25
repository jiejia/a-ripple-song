<?php

namespace App\Widgets;

use App\Abstracts\WidgetAbstract;

/**
 * Footer Links Widget
 * Display footer links or plain text items.
 */
class FooterLinksWidget extends WidgetAbstract
{
    /**
     * Return the WordPress widget id base.
     */
    public static function idBase(): string
    {
        return 'footer_links_widget';
    }

    /**
     * Return Carbon-prefixed instance keys mapped to standard widget keys.
     *
     * @return array<string,string>
     */
    public static function instanceAliases(): array
    {
        return [
            'footer_links_title' => 'title',
            'footer_links_items' => 'items',
        ];
    }

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            static::idBase(),
            __('aripplesong - Footer Links', 'a-ripple-song'),
            ['description' => __('Display a list of links or text items in the footer', 'a-ripple-song')]
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

        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : '';
        $items = $this->getSanitizedItems($instance['items'] ?? []);

        echo \Roots\view('widgets.footer-links', [
            'title' => $title,
            'items' => $items,
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
        $title = ! empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : '';
        $items = $this->getSanitizedItems($instance['items'] ?? []);

        if (empty($items)) {
            $items[] = [
                'text' => '',
                'url' => '',
                'new_tab' => false,
            ];
        }

        $widgetId = $this->get_field_id('items');
        $fieldPrefix = $this->get_field_name('items');
        ?>
        <div class="footer-links-widget-form" data-field-prefix="<?php echo esc_attr($fieldPrefix); ?>">
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                    <?php esc_html_e('Title:', 'a-ripple-song'); ?>
                </label>
                <input class="widefat"
                       id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                       name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                       type="text"
                       value="<?php echo esc_attr($title); ?>"
                       placeholder="<?php echo esc_attr__('e.g., Contact, Navigate, Support', 'a-ripple-song'); ?>">
            </p>

            <p style="margin-bottom: 8px;">
                <strong><?php esc_html_e('Items:', 'a-ripple-song'); ?></strong>
            </p>

            <div id="<?php echo esc_attr($widgetId); ?>_container" class="footer-links-container" style="margin-bottom: 10px;">
                <?php foreach ($items as $index => $item): ?>
                    <div class="footer-link-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Text:', 'a-ripple-song'); ?>
                            </label>
                            <input type="text"
                                   class="widefat footer-link-text"
                                   name="<?php echo esc_attr($fieldPrefix); ?>[<?php echo esc_attr((string) $index); ?>][text]"
                                   value="<?php echo esc_attr((string) ($item['text'] ?? '')); ?>"
                                   placeholder="<?php echo esc_attr__('Display text', 'a-ripple-song'); ?>">
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('URL (optional - leave empty for plain text):', 'a-ripple-song'); ?>
                            </label>
                            <input type="url"
                                   class="widefat footer-link-url"
                                   name="<?php echo esc_attr($fieldPrefix); ?>[<?php echo esc_attr((string) $index); ?>][url]"
                                   value="<?php echo esc_attr((string) ($item['url'] ?? '')); ?>"
                                   placeholder="https://example.com">
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label>
                                <input type="checkbox"
                                       class="footer-link-new-tab"
                                       name="<?php echo esc_attr($fieldPrefix); ?>[<?php echo esc_attr((string) $index); ?>][new_tab]"
                                       value="1"
                                       <?php checked(! empty($item['new_tab'])); ?>>
                                <?php esc_html_e('Open in new tab', 'a-ripple-song'); ?>
                            </label>
                        </div>

                        <div style="text-align: right;">
                            <button type="button" class="button button-link button-link-delete footer-remove-link" style="color: #b32d2e;">
                                <?php esc_html_e('Delete', 'a-ripple-song'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" class="footer-links-flag" name="<?php echo esc_attr($this->get_field_name('_flag')); ?>" value="1">

            <p>
                <button type="button" class="button footer-add-link" data-widget-id="<?php echo esc_attr($widgetId); ?>">
                    <?php esc_html_e('+ Add Item', 'a-ripple-song'); ?>
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
            'title' => ! empty($newInstance['title']) ? sanitize_text_field((string) $newInstance['title']) : '',
            'items' => $this->getSanitizedItems($newInstance['items'] ?? []),
        ];
    }

    /**
     * Sanitize footer item rows.
     *
     * @param  mixed  $items  Raw footer item configuration.
     * @return array<int,array<string,mixed>>
     */
    protected function getSanitizedItems($items): array
    {
        $sanitizedItems = [];

        if (! is_array($items)) {
            return $sanitizedItems;
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $text = ! empty($item['text']) ? sanitize_text_field((string) $item['text']) : '';

            if ($text === '') {
                continue;
            }

            $sanitizedItems[] = [
                'text' => $text,
                'url' => ! empty($item['url']) ? esc_url_raw((string) $item['url']) : '',
                'new_tab' => ! empty($item['new_tab']),
            ];
        }

        return $sanitizedItems;
    }
}
