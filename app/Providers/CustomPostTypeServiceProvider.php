<?php

namespace App\Providers;

use App\CustomPostTypes\Episode;
use App\Contracts\CustomPostTypeInterface;
use Carbon_Fields\Container;
use Illuminate\Support\ServiceProvider;

class CustomPostTypeServiceProvider extends ServiceProvider
{
    /**
     * Custom post type classes registered by this provider.
     *
     * @var array<int,class-string<CustomPostTypeInterface>>
     */
    private array $postTypes = [Episode::class];
    /**
     * Register custom post type hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Register custom post types during WordPress initialization.
        add_action('init', [$this, 'registerPostTypes']);
        // Register post type fields when Carbon Fields collects field definitions.
        add_action('carbon_fields_register_fields', [$this, 'registerPostTypeFields']);
        foreach ($this->postTypes as $postTypeClass) {
            // Allow each custom post type to attach its own supporting hooks.
            (new $postTypeClass())->registerHooks();
        }
    }
    /**
     * Register all configured custom post types.
     *
     * @return void
     */
    public function registerPostTypes(): void
    {
        foreach ($this->postTypes as $postTypeClass) {
            // Build a WordPress custom post type from the configured contract.
            $postType = new $postTypeClass();
            register_post_type($postTypeClass::slug(), $postType->args());
        }
    }
    /**
     * Register Carbon Fields meta boxes for all configured custom post types.
     *
     * @return void
     */
    public function registerPostTypeFields(): void
    {
        foreach ($this->postTypes as $postTypeClass) {
            // Build post type fields only when the post type exposes any fields.
            $postType = new $postTypeClass();
            $fields = $postType->fields();
            if ($fields === []) {
                continue;
            }
            Container::make('post_meta', $postType->metaBoxTitle())->where('post_type', '=', $postTypeClass::slug())->add_fields($fields);
        }
    }
}
