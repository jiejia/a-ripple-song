<?php
namespace Jiejia\ARippleSong\Abstracts;


use Jiejia\ARippleSong\Plugin;

abstract class CPTAbstract
{
    public string $slug;

    abstract public function singularName(): string;

    abstract public function pluralName(): string;

    abstract public function fields(): array;

    public function slug(): string
    {
        return Plugin::PREFIX . $this->slug;
    }

    /**
     * Return the stored post meta key of a field, e.g. _ars_podcast_audio_file.
     *
     * @param string $key Field key.
     * @return string
     */
    public function fieldMetaKey(string $key): string
    {
        return '_' . $this->slug() . '_' . $key;
    }

    /**
     * Register extra hooks required by this post type.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        // Child post types may override this method when extra hooks are needed.
    }

    public function args(): array
    {
        return [
            'label' => static::pluralName(),
            'labels' => $this->labels(),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'has_archive' => true,
            'supports' => $this->supports(),
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
            /* translators: %s is the singular custom post type name. */
            'add_new_item' => sprintf(__('Add New %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular custom post type name. */
            'edit_item' => sprintf(__('Edit %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular custom post type name. */
            'new_item' => sprintf(__('New %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the singular custom post type name. */
            'view_item' => sprintf(__('View %s', 'a-ripple-song'), $singularName),
            /* translators: %s is the plural custom post type name. */
            'search_items' => sprintf(__('Search %s', 'a-ripple-song'), $pluralName),
            /* translators: %s is the lowercase plural custom post type name. */
            'not_found' => sprintf(__('No %s found', 'a-ripple-song'), strtolower($pluralName)),
            /* translators: %s is the lowercase plural custom post type name. */
            'not_found_in_trash' => sprintf(__('No %s found in Trash', 'a-ripple-song'), strtolower($pluralName)),
        ];
    }

    /**
     * Return editor feature support for this post type.
     *
     * @return array<int,string>
     */
    protected function supports(): array
    {
        return [
            'title',
            'editor',
            'thumbnail',
            'excerpt',
        ];
    }
}