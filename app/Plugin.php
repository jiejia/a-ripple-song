<?php


namespace App;

defined('ABSPATH') || exit;

use App\Contracts\ServiceProvider;
use App\Providers\ControllerServiceProvider;
use App\Providers\CPTServiceProvider;
use App\Providers\FeedServiceProvider;
use App\Providers\PlayerServiceProvider;
use App\Providers\SettingServiceProvider;
use App\Providers\StatisticsServiceProvider;
use App\Providers\TaxonomyServiceProvider;
use App\Providers\TranslationServiceProvider;

/**
 * Main plugin application class.
 */
class Plugin
{
    public const SLUG = 'a-ripple-song';

    public const NAME = 'A Ripple Song';

    public const VERSION = '0.5.3';

    public const DIR = A_RIPPLE_SONG_PLUGIN_DIR;

    public const PREFIX = 'ars_';

    /**
     * Service provider classes registered by this plugin.
     *
     * @var array<int,class-string<ServiceProvider>>
     */
    private array $providers = [
        TranslationServiceProvider::class,
        CPTServiceProvider::class,
        TaxonomyServiceProvider::class,
        SettingServiceProvider::class,
        ControllerServiceProvider::class,
        FeedServiceProvider::class,
        PlayerServiceProvider::class,
        StatisticsServiceProvider::class,
    ];

    /**
     * Service provider instances created during plugin boot.
     *
     * @var array<int,ServiceProvider>
     */
    private array $providerInstances = [];

    /**
     * Return an asset version that changes whenever the file does, so edits bust the browser cache.
     *
     * @param string $path Path relative to the plugin directory.
     * @return string
     */
    public static function assetVersion(string $path): string
    {
        $mtime = @filemtime(self::DIR . $path);

        return $mtime !== false ? (string) $mtime : self::VERSION;
    }

    /**
     * Create the plugin application and boot its providers.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the plugin lifecycle.
     *
     * @return void
     */
    public function init(): void
    {
        $this->registerProviders();
        $this->bootProviders();
    }

    /**
     * Register all configured service providers.
     *
     * @return void
     */
    private function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            // Keep provider instances so boot() runs on the same objects.
            $provider = new $providerClass();
            $provider->register();

            $this->providerInstances[] = $provider;
        }
    }

    /**
     * Boot all registered service providers.
     *
     * @return void
     */
    private function bootProviders(): void
    {
        foreach ($this->providerInstances as $provider) {
            // Allow providers to run logic after every provider is registered.
            $provider->boot();
        }
    }
}
