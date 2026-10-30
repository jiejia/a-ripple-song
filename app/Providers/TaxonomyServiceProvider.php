<?php

namespace Jiejia\ARippleSong\Providers;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\ServiceProviderAbstract;
use Jiejia\ARippleSong\Abstracts\TaxonomyAbstract;
use Jiejia\ARippleSong\Libraries\Fields\TermMetaStore;
use Jiejia\ARippleSong\Plugin;
use Jiejia\ARippleSong\Taxonomies\PodcastCategoryTaxonomy;

/**
 * Registers plugin custom taxonomies.
 */
class TaxonomyServiceProvider extends ServiceProviderAbstract
{
    /**
     * Option holding the taxonomy list the rewrite rules were last flushed for.
     */
    private const REWRITE_OPTION = Plugin::PREFIX . 'taxonomy_rewrite_flushed';

    /**
     * Custom taxonomy classes registered by this provider.
     *
     * @var array<int,class-string<TaxonomyAbstract>>
     */
    private array $taxonomies = [
        PodcastCategoryTaxonomy::class,
    ];

    /**
     * Register custom taxonomy hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerTaxonomies']);
        add_action('admin_init', [$this, 'maybeFlushRewriteRules']);

        foreach ($this->taxonomies as $taxonomyClass) {
            $taxonomy = new $taxonomyClass();

            // The store skips the form itself when the taxonomy exposes no fields.
            (new TermMetaStore($taxonomy))->register();
            $taxonomy->registerHooks();
        }
    }

    /**
     * Register all configured custom taxonomies.
     *
     * @return void
     */
    public function registerTaxonomies(): void
    {
        foreach ($this->taxonomies as $taxonomyClass) {
            $taxonomy = new $taxonomyClass();

            register_taxonomy($taxonomy->slug(), $taxonomy->objectTypes(), $taxonomy->args());
        }
    }

    /**
     * Flush rewrite rules once whenever the taxonomy list changes, so term archive URLs work after deployment.
     *
     * @return void
     */
    public function maybeFlushRewriteRules(): void
    {
        $signature = implode(',', $this->taxonomies);

        if (get_option(self::REWRITE_OPTION) === $signature || ! current_user_can('manage_options')) {
            return;
        }

        flush_rewrite_rules(false);
        update_option(self::REWRITE_OPTION, $signature, false);
    }
}
