<?php

namespace App\Providers;

use App\CustomPostTypes\Episode;
use App\Taxonomies\EpisodeCategory;
use Illuminate\Support\ServiceProvider;

/**
 * Registers theme taxonomies.
 */
class TaxonomyServiceProvider extends ServiceProvider
{
    /**
     * Taxonomy classes mapped to their target post type classes.
     *
     * @var array<class-string,array<int,class-string>>
     */
    private array $taxonomies = [
        EpisodeCategory::class => [Episode::class],
    ];

    /**
     * Register taxonomy hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerTaxonomies']);

        foreach (array_keys($this->taxonomies) as $taxonomyClass) {
            // Allow each taxonomy to attach its own supporting hooks.
            $taxonomy = new $taxonomyClass();

            if (method_exists($taxonomy, 'registerHooks')) {
                $taxonomy->registerHooks();
            }
        }
    }

    /**
     * Register all configured taxonomies.
     *
     * @return void
     */
    public function registerTaxonomies(): void
    {
        foreach ($this->taxonomies as $taxonomyClass => $postTypeClasses) {
            // Convert configured custom post type classes into WordPress post type slugs.
            $postTypeSlugs = array_map(static function (string $postTypeClass): string {
                return $postTypeClass::slug();
            }, $postTypeClasses);

            $taxonomy = new $taxonomyClass();
            register_taxonomy($taxonomy->slug(), $postTypeSlugs, $taxonomy->args());
        }
    }
}
