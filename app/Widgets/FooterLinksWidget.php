<?php

use Carbon_Fields\Field;
use Carbon_Fields\Widget;

/**
 * Footer Links Widget
 * Display footer links or plain text items.
 */
class FooterLinksWidget extends Widget
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
            'footer_links_widget',
            __('aripplesong - Footer Links', 'sage'),
            __('Display a list of links or text items in the footer', 'sage'),
            [
                Field::make('text', 'footer_links_title', __('Title:', 'sage'))
                    ->set_help_text(__('e.g., Contact, Navigate, Support', 'sage')),
                Field::make('complex', 'footer_links_items', __('Items:', 'sage'))
                    ->add_fields([
                        Field::make('text', 'text', __('Text:', 'sage')),
                        Field::make('text', 'url', __('URL (optional - leave empty for plain text):', 'sage')),
                        Field::make('checkbox', 'new_tab', __('Open in new tab', 'sage'))
                            ->set_option_value('1'),
                    ]),
            ]
        );
    }

    /**
     * Render the Carbon Fields form with legacy checkbox values normalized.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     */
    public function form($instance)
    {
        $instance = $this->withLegacyAliases($instance, [
            'footer_links_title' => 'title',
            'footer_links_items' => 'items',
        ]);

        parent::form($this->normalizeItemsForForm($instance));
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

        $title = $this->textValue($instance, ['footer_links_title', 'title'], '');
        $items = $this->itemsValue($instance);

        echo \Roots\view('widgets.footer-links', [
            'title' => $title,
            'items' => $items,
        ])->render();

        echo $args['after_widget'];
    }

    /**
     * Return a text setting with a fallback.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @param  string  $key  Instance key.
     * @param  string  $default  Fallback value.
     */
    private function textValue(array $instance, string|array $keys, string $default): string
    {
        foreach ((array) $keys as $key) {
            if (isset($instance[$key]) && $instance[$key] !== '') {
                return sanitize_text_field($instance[$key]);
            }
        }

        return $default;
    }

    /**
     * Return normalized footer items for the front-end template.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @return array<int,array<string,mixed>>
     */
    private function itemsValue(array $instance): array
    {
        $items = [];

        $storedItems = $instance['footer_links_items'] ?? $instance['items'] ?? [];

        if (empty($storedItems) || ! is_array($storedItems)) {
            return $items;
        }

        foreach ($storedItems as $item) {
            if (! is_array($item) || empty($item['text'])) {
                continue;
            }

            $items[] = [
                'text' => sanitize_text_field($item['text']),
                'url' => ! empty($item['url']) ? esc_url_raw($item['url']) : '',
                'new_tab' => in_array($item['new_tab'] ?? false, [true, 1, '1', 'yes', 'on'], true),
            ];
        }

        return $items;
    }

    /**
     * Normalize legacy item checkbox values before Carbon Fields renders the form.
     *
     * @param  array<string,mixed>  $instance  Saved widget instance values.
     * @return array<string,mixed>
     */
    private function normalizeItemsForForm(array $instance): array
    {
        if (empty($instance['footer_links_items']) || ! is_array($instance['footer_links_items'])) {
            return $instance;
        }

        foreach ($instance['footer_links_items'] as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $instance['footer_links_items'][$index]['new_tab'] = in_array($item['new_tab'] ?? false, [true, 1, '1', 'yes', 'on'], true) ? '1' : '';
        }

        return $instance;
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
