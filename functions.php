<?php

use App\Providers\CommentServiceProvider;
use App\Providers\ThemeServiceProvider;
use App\Providers\CarbonFieldsServiceProvider;
use App\Providers\CustomAreasServiceProvider;
use App\Providers\CustomizerServiceProvider;
use App\Providers\CustomPostTypeServiceProvider;
use App\Providers\FeedServiceProvider;
use App\Providers\MenuServiceProvider;
use App\Providers\MetaServiceProvider;
use App\Providers\NavigationServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Providers\SettingServiceProvider;
use App\Providers\TaxonomyServiceProvider;
use App\Providers\WidgetServiceProvider;
use Roots\Acorn\Application;


if (!defined('A_RIPPLE_SONG_THEME_DIR')) {
    define('A_RIPPLE_SONG_THEME_DIR', get_stylesheet_directory());
}


/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
    ->withProviders([
        ThemeServiceProvider::class,
        CommentServiceProvider::class,
        CarbonFieldsServiceProvider::class,
        CustomPostTypeServiceProvider::class,
        TaxonomyServiceProvider::class,
        SettingServiceProvider::class,
        FeedServiceProvider::class,
        MetaServiceProvider::class,
        CustomAreasServiceProvider::class,
        CustomizerServiceProvider::class,
        NavigationServiceProvider::class,
        RouteServiceProvider::class,
        MenuServiceProvider::class,
        WidgetServiceProvider::class,
    ])
    ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['helpers', 'setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });
