<?php

namespace App\Abstracts;

use App\Contracts\CustomPostTypeInterface;

abstract class CustomPostTypeAbstract implements CustomPostTypeInterface
{
    /**
     * Return a prefixed top-level field key for this custom post type.
     *
     * @param string $key Raw field key.
     * @return string
     */
    public static function fieldKey(string $key): string
    {
        return static::slug() . '_' . ltrim($key, '_');
    }
    /**
     * Return the stored private post meta key for this custom post type.
     *
     * @param string $key Raw field key.
     * @return string
     */
    public static function storedFieldKey(string $key): string
    {
        return '_' . static::fieldKey($key);
    }
    /**
     * Return WordPress registration arguments.
     *
     * @return array<string,mixed>
     */
    public function args(): array
    {
        return ['label' => static::pluralName(), 'labels' => $this->labels(), 'public' => \true, 'show_ui' => \true, 'show_in_menu' => \false, 'show_in_rest' => \true, 'has_archive' => \true, 'supports' => $this->supports()];
    }
    /**
     * Return Carbon Fields registered for this post type.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array
    {
        return [];
    }
    /**
     * Return the Carbon Fields meta box title.
     *
     * @return string
     */
    public function metaBoxTitle(): string
    {
        /* translators: %s is the singular custom post type name. */
        return sprintf(__('%s Details', 'a-ripple-song'), static::singularName());
    }
    /**
     * Register extra hooks required by this custom post type.
     *
     * @return void
     */
    public function registerHooks(): void
    {
        // Most custom post types only need the shared provider hooks.
    }
    /**
     * Return WordPress admin labels.
     *
     * @return array<string,string>
     */
    protected function labels(): array
    {
        $singularName = static::singularName();
        $pluralName = static::pluralName();
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
        return ['title', 'editor', 'thumbnail', 'excerpt'];
    }
}