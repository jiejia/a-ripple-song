<?php

namespace Jiejia\ARippleSong\Taxonomies;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\TaxonomyAbstract;
use Jiejia\ARippleSong\CPTs\PodcastCPT;

/**
 * Hierarchical episode categories (ars_podcast_category), separate from post categories.
 */
class PodcastCategoryTaxonomy extends TaxonomyAbstract
{
    public string $slug = 'podcast_category';

    public function singularName(): string
    {
        return __('Episode Category', 'a-ripple-song');
    }

    public function pluralName(): string
    {
        return __('Episode Categories', 'a-ripple-song');
    }

    /**
     * Return field definitions shown on the add / edit category screens.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'image',
                'key' => 'image',
                'label' => __('Category Image', 'a-ripple-song'),
                'width' => 'half',
                'dimensions' => ['square' => true],
                'help' => __('Optional. A square image shown by themes on the category page and in category lists.', 'a-ripple-song'),
            ],
            [
                'type' => 'image',
                'key' => 'icon',
                'label' => __('Category Icon', 'a-ripple-song'),
                'width' => 'half',
                'dimensions' => ['square' => true],
                'help' => __('Optional. A small square image, e.g. a 128 × 128 px PNG.', 'a-ripple-song'),
            ],
        ];
    }

    /**
     * Register term meta, and the icon column of the category list.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerMeta']);
        add_filter('manage_edit-' . $this->slug() . '_columns', [$this, 'addIconColumn']);
        add_filter('manage_' . $this->slug() . '_custom_column', [$this, 'renderIconColumn'], 10, 3);
    }

    /**
     * Expose the image and icon URLs in the REST API, read-only.
     *
     * @return void
     */
    public function registerMeta(): void
    {
        foreach (['image', 'icon'] as $key) {
            register_term_meta($this->slug(), $this->fieldMetaKey($key), [
                'type' => 'string',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'esc_url_raw',
                // Edited on the category screens only.
                'auth_callback' => '__return_false',
            ]);
        }
    }

    /**
     * Return the image URL of a category, or "".
     *
     * @param int $termId Term ID.
     * @return string
     */
    public function image(int $termId): string
    {
        return (string) get_term_meta($termId, $this->fieldMetaKey('image'), true);
    }

    /**
     * Return the icon URL of a category, or "".
     *
     * @param int $termId Term ID.
     * @return string
     */
    public function icon(int $termId): string
    {
        return (string) get_term_meta($termId, $this->fieldMetaKey('icon'), true);
    }

    /**
     * Add an icon column before the name column.
     *
     * @param array<string,string> $columns Column labels keyed by column ID.
     * @return array<string,string>
     */
    public function addIconColumn(array $columns): array
    {
        $result = [];

        foreach ($columns as $id => $label) {
            if ($id === 'name') {
                $result['ars_icon'] = __('Icon', 'a-ripple-song');
            }

            $result[$id] = $label;
        }

        return $result;
    }

    /**
     * Print the icon thumbnail of a category, falling back to its image.
     *
     * @param string $content Column content.
     * @param string $column Column ID.
     * @param int $termId Term ID.
     * @return string
     */
    public function renderIconColumn(string $content, string $column, int $termId): string
    {
        if ($column !== 'ars_icon') {
            return $content;
        }

        $url = $this->icon($termId) ?: $this->image($termId);

        return $url !== '' ? sprintf('<img src="%s" alt="" width="32" height="32" style="object-fit:cover;border-radius:4px">', esc_url($url)) : '';
    }

        /**
     * Return the post types the taxonomy is attached to.
     *
     * @return array<int,string>
     */
    public function objectTypes(): array
    {
        return [(new PodcastCPT())->slug()];
    }

    /**
     * Return WordPress registration arguments.
     *
     * @return array<string,mixed>
     */
    public function args(): array
    {
        $args = parent::args();

        $args['hierarchical'] = true;
        $args['rewrite'] = [
            'slug' => 'podcast-category',
            'hierarchical' => true,
        ];

        return $args;
    }
}
