<?php

namespace Jiejia\ARippleSong\Abstracts;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Plugin;

/**
 * Base class of the plugin's custom taxonomies.
 */
abstract class TaxonomyAbstract
{
    public string $slug;

    abstract public function singularName(): string;

    abstract public function pluralName(): string;

    /**
     * Return the post types the taxonomy is attached to.
     *
     * @return array<int,string>
     */
    abstract public function objectTypes(): array;

    public function slug(): string
    {
        return Plugin::PREFIX . $this->slug;
    }

    /**
     * Return field definitions shown on the add / edit term screens; none by default.
     *
     * Keys are short names; TermMetaStore stores them as _{taxonomy}_{key}.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Return the stored term meta key of a field, e.g. _ars_podcast_category_image.
     *
     * @param string $key Field key.
     * @return string
     */
    public function fieldMetaKey(string $key): string
    {
        return '_' . $this->slug() . '_' . $key;
    }

    /**
     * Register extra hooks required by this taxonomy.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        // Child taxonomies may override this method when extra hooks are needed.
    }

    /**
     * Return WordPress registration arguments.
     *
     * @return array<string,mixed>
     */
    public function args(): array
    {
        return [
            'label' => $this->pluralName(),
            'labels' => $this->labels(),
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
        ];
    }

    /**
     * Return WordPress admin labels.
     *
     * @return array<string,string>
     */
    protected function labels(): array
    {
        $singularName = $this->singularName();
        $pluralName = $this->pluralName();

        return [
            'name' => $pluralName,
            'singular_name' => $singularName,
            'menu_name' => $pluralName,
            /* translators: %s is the plural taxonomy name. */
            'all_items' => sprintf(__('All %s', 'a-ripple-song'), $pluralName),
            /* translators: %s is the singular post type or taxonomy name. */
            'edit_item' => sprintf(__('Edit %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular post type or taxonomy name. */
            'view_item' => sprintf(__('View %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular taxonomy name. */
            'update_item' => sprintf(__('Update %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular post type or taxonomy name. */
            'add_new_item' => sprintf(__('Add New %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular taxonomy name. */
            'new_item_name' => sprintf(__('New %s Name', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular taxonomy name. */
            'parent_item' => sprintf(__('Parent %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the plural post type or taxonomy name. */
            'search_items' => sprintf(__('Search %s', 'a-ripple-song'), $pluralName),
            /* translators: %s is the lowercase plural post type or taxonomy name. */
            'not_found' => sprintf(__('No %s found', 'a-ripple-song'), strtolower($pluralName)),
            /* translators: %s is the plural taxonomy name. */
            'back_to_items' => sprintf(__('&larr; Go to %s', 'a-ripple-song'), $pluralName),
        ];
    }
}
