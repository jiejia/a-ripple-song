<?php

namespace App\ThemeOptions;

final class ThemeSettings
{
    public const PAGE_SLUG = 'aripplesong-theme-settings';
    public const SETTINGS_GROUP = 'aripplesong-theme-settings';

    public static function boot(): void
    {
        if (is_admin()) {
            add_action('admin_menu', [static::class, 'registerMenu']);
            add_action('admin_init', [static::class, 'registerSettings']);
        }

        add_action('wp_head', [static::class, 'outputHeaderScripts'], 999);
        add_action('wp_footer', [static::class, 'outputFooterScripts'], 999);
    }

    public static function getOptionString(string $key, string $default = ''): string
    {
        $value = get_option(static::optionName($key), $default);
        return is_string($value) ? $value : $default;
    }

    public static function getLightTheme(): string
    {
        $allowed = array_keys(static::getDaisyUiLightThemes());
        $stored = sanitize_key(static::getOptionString('crb_light_theme', ''));

        if ($stored && in_array($stored, $allowed, true)) {
            return $stored;
        }

        return 'retro';
    }

    public static function getDarkTheme(): string
    {
        $allowed = array_keys(static::getDaisyUiDarkThemes());
        $stored = sanitize_key(static::getOptionString('crb_dark_theme', ''));

        if ($stored && in_array($stored, $allowed, true)) {
            return $stored;
        }

        return 'dim';
    }

    /**
     * DaisyUI light themes list keyed by slug.
     *
     * @return array<string, string>
     */
    public static function getDaisyUiLightThemes(): array
    {
        return [
            'retro' => 'retro',
            'pastel-breeze' => 'Pastel Breeze',
            'soft-sand' => 'Soft Sand',
            'mint-cream' => 'Mint Cream',
            'blush-mist' => 'Blush Mist',
            'sky-peach' => 'Sky Peach',
            'lemon-fizz' => 'Lemon Fizz',
            'lavender-fog' => 'Lavender Fog',
            'coral-sunset' => 'Coral Sunset',
            'sea-glass' => 'Sea Glass',
            'apricot-sorbet' => 'Apricot Sorbet',
            'cotton-candy' => 'Cotton Candy',
            'pear-spritz' => 'Pear Spritz',
            'cloud-latte' => 'Cloud Latte',
            'dew-frost' => 'Dew Frost',
            'peach-foam' => 'Peach Foam',
            'lilac-ice' => 'Lilac Ice',
            'sage-mint' => 'Sage Mint',
            'buttercup' => 'Buttercup',
            'powder-blue' => 'Powder Blue',
            'melon-ice' => 'Melon Ice',
            'hazy-rose' => 'Hazy Rose',
            'calm-water' => 'Calm Water',
            'honey-milk' => 'Honey Milk',
            'arctic-mint' => 'Arctic Mint',
            'vanilla-berry' => 'Vanilla Berry',
            'morning-sun' => 'Morning Sun',
            'matcha-cream' => 'Matcha Cream',
        ];
    }

    /**
     * DaisyUI dark themes list keyed by slug.
     *
     * @return array<string, string>
     */
    public static function getDaisyUiDarkThemes(): array
    {
        return [
            'dim' => 'dim',
            'midnight-aurora' => 'Midnight Aurora',
            'neon-plasma' => 'Neon Plasma',
            'cyber-grape' => 'Cyber Grape',
            'velvet-ember' => 'Velvet Ember',
            'ink-cyan' => 'Ink Cyan',
            'dusk-rose' => 'Dusk Rose',
            'obsidian-gold' => 'Obsidian Gold',
            'deep-space' => 'Deep Space',
            'ocean-night' => 'Ocean Night',
            'noir-mint' => 'Noir Mint',
            'plum-neon' => 'Plum Neon',
            'cobalt-flare' => 'Cobalt Flare',
            'dusk-marine' => 'Dusk Marine',
            'ember-glow' => 'Ember Glow',
            'midnight-teal' => 'Midnight Teal',
            'aurora-mist' => 'Aurora Mist',
            'shadow-berry' => 'Shadow Berry',
            'neon-blush' => 'Neon Blush',
            'abyss-blue' => 'Abyss Blue',
            'charcoal-mint' => 'Charcoal Mint',
            'galaxy-candy' => 'Galaxy Candy',
            'violet-storm' => 'Violet Storm',
            'magma-ice' => 'Magma Ice',
            'stormy-sea' => 'Stormy Sea',
            'lunar-mauve' => 'Lunar Mauve',
            'acid-jungle' => 'Acid Jungle',
            'carbon-ember' => 'Carbon Ember',
        ];
    }

    /**
     * Build a palette map for the themes we expose in the frontend.
     *
     * @param array<string> $slugs
     * @return array<string, array<string, string>>
     */
    public static function getDaisyUiThemePalette(array $slugs): array
    {
        static $cache = [];

        $custom_palette = [
            'retro' => [
                'base100' => 'oklch(91.637% 0.034 90.515)',
                'base200' => 'oklch(88.272% 0.049 91.774)',
                'base300' => 'oklch(84.133% 0.065 90.856)',
                'baseContent' => 'oklch(41% 0.112 45.904)',
                'primary' => 'oklch(80% 0.114 19.571)',
                'primaryContent' => 'oklch(39% 0.141 25.723)',
                'secondary' => 'oklch(92% 0.084 155.995)',
                'secondaryContent' => 'oklch(44% 0.119 151.328)',
                'accent' => 'oklch(68% 0.162 75.834)',
                'accentContent' => 'oklch(41% 0.112 45.904)',
                'neutral' => 'oklch(44% 0.011 73.639)',
                'neutralContent' => 'oklch(86% 0.005 56.366)',
            ],
            'pastel-breeze' => [
                'base100' => '#f1f5fa',
                'base200' => '#e5edf6',
                'base300' => '#d6e3f0',
                'baseContent' => '#1e293b',
                'primary' => '#7c9cff',
                'primaryContent' => '#0f172a',
                'secondary' => '#8fd3ff',
                'secondaryContent' => '#0c4a6e',
                'accent' => '#ffb3c6',
                'accentContent' => '#4a0e2a',
                'neutral' => '#94a3b8',
                'neutralContent' => '#0f172a',
            ],
            'soft-sand' => [
                'base100' => '#f5ebdf',
                'base200' => '#e9ddcd',
                'base300' => '#dccfbf',
                'baseContent' => '#3f2d20',
                'primary' => '#d58b3b',
                'primaryContent' => '#331904',
                'secondary' => '#f0c987',
                'secondaryContent' => '#42210b',
                'accent' => '#84c7ae',
                'accentContent' => '#0f2f24',
                'neutral' => '#c7b8a3',
                'neutralContent' => '#2b1d12',
            ],
            'mint-cream' => [
                'base100' => '#e9f7f0',
                'base200' => '#d7eddf',
                'base300' => '#c4e0cf',
                'baseContent' => '#0f261c',
                'primary' => '#4bc0a9',
                'primaryContent' => '#05241c',
                'secondary' => '#9ee6c3',
                'secondaryContent' => '#0f2e1f',
                'accent' => '#ffd66b',
                'accentContent' => '#3c2900',
                'neutral' => '#93a59c',
                'neutralContent' => '#0b1b14',
            ],
            'blush-mist' => [
                'base100' => '#faeaf0',
                'base200' => '#f1d7e0',
                'base300' => '#e3c0cc',
                'baseContent' => '#31111d',
                'primary' => '#f472b6',
                'primaryContent' => '#3f0a1c',
                'secondary' => '#fbb1bd',
                'secondaryContent' => '#3c0e1e',
                'accent' => '#9fb4ff',
                'accentContent' => '#0b1b4d',
                'neutral' => '#c7b6be',
                'neutralContent' => '#2a1b24',
            ],
            'sky-peach' => [
                'base100' => '#f5ebe5',
                'base200' => '#e8d8cf',
                'base300' => '#d7c3b7',
                'baseContent' => '#2f1f18',
                'primary' => '#ff9e7a',
                'primaryContent' => '#3a1307',
                'secondary' => '#6ec3ff',
                'secondaryContent' => '#0b1f36',
                'accent' => '#ffd76f',
                'accentContent' => '#3c2a00',
                'neutral' => '#b8a99d',
                'neutralContent' => '#1f160f',
            ],
            'lemon-fizz' => [
                'base100' => '#fbf4d9',
                'base200' => '#e9e0b8',
                'base300' => '#d2c99d',
                'baseContent' => '#3a3004',
                'primary' => '#f8d03f',
                'primaryContent' => '#3a3004',
                'secondary' => '#7ed9a4',
                'secondaryContent' => '#0c2a1b',
                'accent' => '#ff9fb2',
                'accentContent' => '#3c0e1e',
                'neutral' => '#c4bb8c',
                'neutralContent' => '#1f1b0c',
            ],
            'lavender-fog' => [
                'base100' => '#f1ecff',
                'base200' => '#e1d8ff',
                'base300' => '#cbbff7',
                'baseContent' => '#24164a',
                'primary' => '#b399ff',
                'primaryContent' => '#130b2e',
                'secondary' => '#ffb4e6',
                'secondaryContent' => '#2c0822',
                'accent' => '#8fe0f0',
                'accentContent' => '#062730',
                'neutral' => '#b7b2c5',
                'neutralContent' => '#130b2e',
            ],
            'coral-sunset' => [
                'base100' => '#fce9e1',
                'base200' => '#f7d4c6',
                'base300' => '#efb8a6',
                'baseContent' => '#3a1a13',
                'primary' => '#ff6b6b',
                'primaryContent' => '#3a0b0b',
                'secondary' => '#ffb86b',
                'secondaryContent' => '#3a1f0b',
                'accent' => '#6be7b8',
                'accentContent' => '#07241b',
                'neutral' => '#bca49a',
                'neutralContent' => '#1f110c',
            ],
            'sea-glass' => [
                'base100' => '#e8f6f5',
                'base200' => '#d1ece8',
                'base300' => '#b9dfd9',
                'baseContent' => '#0f2c2a',
                'primary' => '#22c1b7',
                'primaryContent' => '#051f1d',
                'secondary' => '#8be8c9',
                'secondaryContent' => '#073021',
                'accent' => '#ffd76b',
                'accentContent' => '#3c2900',
                'neutral' => '#93a7a1',
                'neutralContent' => '#0b1b18',
            ],
            'apricot-sorbet' => [
                'base100' => '#fff0e5',
                'base200' => '#ffd9c2',
                'base300' => '#ffbb91',
                'baseContent' => '#3d1a0b',
                'primary' => '#ff8c42',
                'primaryContent' => '#3a1307',
                'secondary' => '#6ec3ff',
                'secondaryContent' => '#0b1f36',
                'accent' => '#ffd76f',
                'accentContent' => '#3c2a00',
                'neutral' => '#b8a99d',
                'neutralContent' => '#1f160f',
            ],
            'cotton-candy' => [
                'base100' => '#fff0f6',
                'base200' => '#ffd6e8',
                'base300' => '#ffb3d5',
                'baseContent' => '#3a0f1f',
                'primary' => '#ff5fa2',
                'primaryContent' => '#3a091d',
                'secondary' => '#b399ff',
                'secondaryContent' => '#130b2e',
                'accent' => '#8fe0f0',
                'accentContent' => '#062730',
                'neutral' => '#b7aab0',
                'neutralContent' => '#1f0f17',
            ],
            'pear-spritz' => [
                'base100' => '#f3fbe2',
                'base200' => '#e2f6c0',
                'base300' => '#ccee98',
                'baseContent' => '#1f2a0b',
                'primary' => '#95d84f',
                'primaryContent' => '#0f1f05',
                'secondary' => '#4bc0a9',
                'secondaryContent' => '#05241c',
                'accent' => '#ffd66b',
                'accentContent' => '#3c2900',
                'neutral' => '#96a588',
                'neutralContent' => '#0f1b0b',
            ],
            'cloud-latte' => [
                'base100' => '#f6f2ea',
                'base200' => '#ebe3d6',
                'base300' => '#dfd1c0',
                'baseContent' => '#2c1d12',
                'primary' => '#d1a67a',
                'primaryContent' => '#2b1d12',
                'secondary' => '#84c7ae',
                'secondaryContent' => '#0f2f24',
                'accent' => '#ff9fb2',
                'accentContent' => '#3c0e1e',
                'neutral' => '#b6a79a',
                'neutralContent' => '#1f110c',
            ],
            'dew-frost' => [
                'base100' => '#edf6ff',
                'base200' => '#d6eaff',
                'base300' => '#bdd9ff',
                'baseContent' => '#0b1f36',
                'primary' => '#5aa8ff',
                'primaryContent' => '#04172b',
                'secondary' => '#8fe0f0',
                'secondaryContent' => '#062730',
                'accent' => '#ffb86b',
                'accentContent' => '#3a1f0b',
                'neutral' => '#9aa7b8',
                'neutralContent' => '#0f172a',
            ],
            'peach-foam' => [
                'base100' => '#fff1ea',
                'base200' => '#ffd7c7',
                'base300' => '#ffb79f',
                'baseContent' => '#331904',
                'primary' => '#ff9067',
                'primaryContent' => '#2e0b05',
                'secondary' => '#6ec3ff',
                'secondaryContent' => '#0b1f36',
                'accent' => '#7ed9a4',
                'accentContent' => '#0c2a1b',
                'neutral' => '#b9a79f',
                'neutralContent' => '#1f110c',
            ],
            'lilac-ice' => [
                'base100' => '#f7f1ff',
                'base200' => '#e7d8ff',
                'base300' => '#d2bff7',
                'baseContent' => '#1f0f3a',
                'primary' => '#b399ff',
                'primaryContent' => '#130b2e',
                'secondary' => '#ffb3c6',
                'secondaryContent' => '#4a0e2a',
                'accent' => '#8fe0f0',
                'accentContent' => '#062730',
                'neutral' => '#b7b2c5',
                'neutralContent' => '#130b2e',
            ],
            'sage-mint' => [
                'base100' => '#ecf5ef',
                'base200' => '#d8ede0',
                'base300' => '#c1e0cc',
                'baseContent' => '#0f261c',
                'primary' => '#5fbf8c',
                'primaryContent' => '#05241c',
                'secondary' => '#4bc0a9',
                'secondaryContent' => '#05241c',
                'accent' => '#ffd66b',
                'accentContent' => '#3c2900',
                'neutral' => '#97a59c',
                'neutralContent' => '#0b1b14',
            ],
            'buttercup' => [
                'base100' => '#fff7d1',
                'base200' => '#ffed9e',
                'base300' => '#ffe16b',
                'baseContent' => '#3a3004',
                'primary' => '#f8d03f',
                'primaryContent' => '#3a3004',
                'secondary' => '#ff9fb2',
                'secondaryContent' => '#3c0e1e',
                'accent' => '#6ec3ff',
                'accentContent' => '#0b1f36',
                'neutral' => '#c4bb8c',
                'neutralContent' => '#1f1b0c',
            ],
            'powder-blue' => [
                'base100' => '#e9f2ff',
                'base200' => '#d6e5ff',
                'base300' => '#bdd5ff',
                'baseContent' => '#0b1f36',
                'primary' => '#7c9cff',
                'primaryContent' => '#0f172a',
                'secondary' => '#8fd3ff',
                'secondaryContent' => '#0c4a6e',
                'accent' => '#ffb3c6',
                'accentContent' => '#4a0e2a',
                'neutral' => '#9aa7b8',
                'neutralContent' => '#0f172a',
            ],
            'melon-ice' => [
                'base100' => '#ecfdf5',
                'base200' => '#d1fae5',
                'base300' => '#a7f3d0',
                'baseContent' => '#042f2e',
                'primary' => '#34d399',
                'primaryContent' => '#05241c',
                'secondary' => '#6ee7b7',
                'secondaryContent' => '#07221b',
                'accent' => '#ffd66b',
                'accentContent' => '#3c2900',
                'neutral' => '#93a59c',
                'neutralContent' => '#0b1b14',
            ],
            'hazy-rose' => [
                'base100' => '#fff1f2',
                'base200' => '#ffe4e6',
                'base300' => '#fecdd3',
                'baseContent' => '#3a0f1f',
                'primary' => '#fb7185',
                'primaryContent' => '#3a0b0b',
                'secondary' => '#fbb1bd',
                'secondaryContent' => '#3c0e1e',
                'accent' => '#9fb4ff',
                'accentContent' => '#0b1b4d',
                'neutral' => '#b7aab0',
                'neutralContent' => '#1f0f17',
            ],
            'calm-water' => [
                'base100' => '#e8f7ff',
                'base200' => '#d1efff',
                'base300' => '#b9e5ff',
                'baseContent' => '#0b1f36',
                'primary' => '#38bdf8',
                'primaryContent' => '#04172b',
                'secondary' => '#8fd3ff',
                'secondaryContent' => '#0c4a6e',
                'accent' => '#ffb3c6',
                'accentContent' => '#4a0e2a',
                'neutral' => '#94a3b8',
                'neutralContent' => '#0f172a',
            ],
            'honey-milk' => [
                'base100' => '#fdf6e3',
                'base200' => '#f7e8bf',
                'base300' => '#efd89a',
                'baseContent' => '#3a2c05',
                'primary' => '#fbbf24',
                'primaryContent' => '#3a1f0b',
                'secondary' => '#f59e0b',
                'secondaryContent' => '#2b1d12',
                'accent' => '#84c7ae',
                'accentContent' => '#0f2f24',
                'neutral' => '#b8a99d',
                'neutralContent' => '#1f160f',
            ],
            'arctic-mint' => [
                'base100' => '#e6fffb',
                'base200' => '#c5fff6',
                'base300' => '#9bf5e8',
                'baseContent' => '#042f2e',
                'primary' => '#2dd4bf',
                'primaryContent' => '#05241c',
                'secondary' => '#22c1b7',
                'secondaryContent' => '#051f1d',
                'accent' => '#ffd66b',
                'accentContent' => '#3c2900',
                'neutral' => '#93a59c',
                'neutralContent' => '#0b1b14',
            ],
            'vanilla-berry' => [
                'base100' => '#fff1f6',
                'base200' => '#ffd6e8',
                'base300' => '#ffb3d5',
                'baseContent' => '#3a0f1f',
                'primary' => '#ff5fa2',
                'primaryContent' => '#3a091d',
                'secondary' => '#b399ff',
                'secondaryContent' => '#130b2e',
                'accent' => '#ffd66b',
                'accentContent' => '#3c2900',
                'neutral' => '#b7aab0',
                'neutralContent' => '#1f0f17',
            ],
            'morning-sun' => [
                'base100' => '#fff7e6',
                'base200' => '#ffebc2',
                'base300' => '#ffd694',
                'baseContent' => '#3a3004',
                'primary' => '#fb923c',
                'primaryContent' => '#3a1f0b',
                'secondary' => '#fbbf24',
                'secondaryContent' => '#3a3004',
                'accent' => '#6ee7b7',
                'accentContent' => '#07221b',
                'neutral' => '#b8a99d',
                'neutralContent' => '#1f160f',
            ],
            'matcha-cream' => [
                'base100' => '#f3f9e9',
                'base200' => '#e1f0d0',
                'base300' => '#cbe3b2',
                'baseContent' => '#1f2a0b',
                'primary' => '#84cc16',
                'primaryContent' => '#0f1f05',
                'secondary' => '#4bc0a9',
                'secondaryContent' => '#05241c',
                'accent' => '#ffd66b',
                'accentContent' => '#3c2900',
                'neutral' => '#96a588',
                'neutralContent' => '#0f1b0b',
            ],
            'dim' => [
                'base100' => 'oklch(33.707% 0.029 139.822)',
                'base200' => 'oklch(30.494% 0.026 139.822)',
                'base300' => 'oklch(27.79% 0.024 139.822)',
                'baseContent' => 'oklch(84.153% 0.007 139.822)',
                'primary' => 'oklch(86.238% 0.022 139.822)',
                'primaryContent' => 'oklch(17.247% 0.005 139.822)',
                'secondary' => 'oklch(70.308% 0.02 3.827)',
                'secondaryContent' => 'oklch(14.061% 0.004 3.827)',
                'accent' => 'oklch(71.772% 0.013 219.75)',
                'accentContent' => 'oklch(14.354% 0.002 219.75)',
                'neutral' => 'oklch(24.731% 0.02 262.24)',
                'neutralContent' => 'oklch(82.341% 0.007 262.24)',
            ],
            'midnight-aurora' => [
                'base100' => '#101827',
                'base200' => '#0b1220',
                'base300' => '#070c17',
                'baseContent' => '#e5e7eb',
                'primary' => '#38bdf8',
                'primaryContent' => '#04172b',
                'secondary' => '#a78bfa',
                'secondaryContent' => '#1f0f3a',
                'accent' => '#34d399',
                'accentContent' => '#05241c',
                'neutral' => '#1f2937',
                'neutralContent' => '#f3f4f6',
            ],
            'neon-plasma' => [
                'base100' => '#15121c',
                'base200' => '#120e19',
                'base300' => '#0e0a15',
                'baseContent' => '#f3f1f5',
                'primary' => '#ff4ecd',
                'primaryContent' => '#2b041a',
                'secondary' => '#7c9cff',
                'secondaryContent' => '#0b142f',
                'accent' => '#6ee8c5',
                'accentContent' => '#041c14',
                'neutral' => '#2c2140',
                'neutralContent' => '#efe6ff',
            ],
            'cyber-grape' => [
                'base100' => '#201a2a',
                'base200' => '#1b1624',
                'base300' => '#15101e',
                'baseContent' => '#f5f0ff',
                'primary' => '#c084fc',
                'primaryContent' => '#1f0a2f',
                'secondary' => '#38bdf8',
                'secondaryContent' => '#04172b',
                'accent' => '#fbbf24',
                'accentContent' => '#2f1d05',
                'neutral' => '#312342',
                'neutralContent' => '#efe6ff',
            ],
            'velvet-ember' => [
                'base100' => '#28151e',
                'base200' => '#221019',
                'base300' => '#1b0a13',
                'baseContent' => '#fbeff2',
                'primary' => '#fb7185',
                'primaryContent' => '#2f0c0c',
                'secondary' => '#f97316',
                'secondaryContent' => '#2b0d02',
                'accent' => '#34d399',
                'accentContent' => '#05241c',
                'neutral' => '#3b1f28',
                'neutralContent' => '#fbeff2',
            ],
            'ink-cyan' => [
                'base100' => '#0b1720',
                'base200' => '#07131b',
                'base300' => '#041016',
                'baseContent' => '#e5f6ff',
                'primary' => '#22d3ee',
                'primaryContent' => '#062730',
                'secondary' => '#818cf8',
                'secondaryContent' => '#0b142f',
                'accent' => '#fbbf24',
                'accentContent' => '#2f1d05',
                'neutral' => '#102534',
                'neutralContent' => '#e5f6ff',
            ],
            'dusk-rose' => [
                'base100' => '#26171f',
                'base200' => '#201219',
                'base300' => '#1a0c12',
                'baseContent' => '#f9e7ee',
                'primary' => '#ff5fa2',
                'primaryContent' => '#2b041a',
                'secondary' => '#c084fc',
                'secondaryContent' => '#1f0a2f',
                'accent' => '#6ee7b7',
                'accentContent' => '#07221b',
                'neutral' => '#3a2230',
                'neutralContent' => '#f9e7ee',
            ],
            'obsidian-gold' => [
                'base100' => '#1a1a1a',
                'base200' => '#151515',
                'base300' => '#0f0f0f',
                'baseContent' => '#f7f7f7',
                'primary' => '#fbbf24',
                'primaryContent' => '#2f1d05',
                'secondary' => '#f59e0b',
                'secondaryContent' => '#2b1d12',
                'accent' => '#34d399',
                'accentContent' => '#05241c',
                'neutral' => '#2a2a2a',
                'neutralContent' => '#f7f7f7',
            ],
            'deep-space' => [
                'base100' => '#0b1020',
                'base200' => '#070c17',
                'base300' => '#040810',
                'baseContent' => '#e2e8f0',
                'primary' => '#7c9cff',
                'primaryContent' => '#0b142f',
                'secondary' => '#38bdf8',
                'secondaryContent' => '#04172b',
                'accent' => '#ffb86b',
                'accentContent' => '#3a1f0b',
                'neutral' => '#1f2937',
                'neutralContent' => '#f3f4f6',
            ],
            'ocean-night' => [
                'base100' => '#0b1f2a',
                'base200' => '#081824',
                'base300' => '#05131e',
                'baseContent' => '#e8f7ff',
                'primary' => '#38bdf8',
                'primaryContent' => '#04172b',
                'secondary' => '#2dd4bf',
                'secondaryContent' => '#05241c',
                'accent' => '#fbbf24',
                'accentContent' => '#2f1d05',
                'neutral' => '#102534',
                'neutralContent' => '#e8f7ff',
            ],
            'noir-mint' => [
                'base100' => '#0f1b14',
                'base200' => '#0b1510',
                'base300' => '#07110c',
                'baseContent' => '#e9f7f0',
                'primary' => '#34d399',
                'primaryContent' => '#05241c',
                'secondary' => '#2dd4bf',
                'secondaryContent' => '#05241c',
                'accent' => '#fbbf24',
                'accentContent' => '#2f1d05',
                'neutral' => '#1f2937',
                'neutralContent' => '#f3f4f6',
            ],
            'plum-neon' => [
                'base100' => '#1a1024',
                'base200' => '#140c1c',
                'base300' => '#0e0813',
                'baseContent' => '#f5f0ff',
                'primary' => '#c084fc',
                'primaryContent' => '#1f0a2f',
                'secondary' => '#ff4ecd',
                'secondaryContent' => '#2b041a',
                'accent' => '#6ee7b7',
                'accentContent' => '#07221b',
                'neutral' => '#2c2140',
                'neutralContent' => '#efe6ff',
            ],
            'cobalt-flare' => [
                'base100' => '#0b132b',
                'base200' => '#070e20',
                'base300' => '#040817',
                'baseContent' => '#e5e7eb',
                'primary' => '#7c9cff',
                'primaryContent' => '#0b142f',
                'secondary' => '#38bdf8',
                'secondaryContent' => '#04172b',
                'accent' => '#fb7185',
                'accentContent' => '#2f0c0c',
                'neutral' => '#1f2937',
                'neutralContent' => '#f3f4f6',
            ],
            'dusk-marine' => [
                'base100' => '#0f1c2d',
                'base200' => '#0b1624',
                'base300' => '#07101b',
                'baseContent' => '#e8f7ff',
                'primary' => '#22d3ee',
                'primaryContent' => '#062730',
                'secondary' => '#7c9cff',
                'secondaryContent' => '#0b142f',
                'accent' => '#fbbf24',
                'accentContent' => '#2f1d05',
                'neutral' => '#132f3d',
                'neutralContent' => '#d5e4ec',
            ],
            'ember-glow' => [
                'base100' => '#2b1712',
                'base200' => '#24110d',
                'base300' => '#1d0c08',
                'baseContent' => '#fff1ea',
                'primary' => '#ff9067',
                'primaryContent' => '#2e0b05',
                'secondary' => '#fbbf24',
                'secondaryContent' => '#2f1d05',
                'accent' => '#34d399',
                'accentContent' => '#05241c',
                'neutral' => '#3b1f18',
                'neutralContent' => '#fff1ea',
            ],
            'midnight-teal' => [
                'base100' => '#0b1f1e',
                'base200' => '#071716',
                'base300' => '#041110',
                'baseContent' => '#e8f6f5',
                'primary' => '#2dd4bf',
                'primaryContent' => '#05241c',
                'secondary' => '#38bdf8',
                'secondaryContent' => '#04172b',
                'accent' => '#fbbf24',
                'accentContent' => '#2f1d05',
                'neutral' => '#102534',
                'neutralContent' => '#e8f6f5',
            ],
            'aurora-mist' => [
                'base100' => '#0b1422',
                'base200' => '#07101b',
                'base300' => '#040b14',
                'baseContent' => '#e5f6ff',
                'primary' => '#38bdf8',
                'primaryContent' => '#04172b',
                'secondary' => '#c084fc',
                'secondaryContent' => '#1f0a2f',
                'accent' => '#34d399',
                'accentContent' => '#05241c',
                'neutral' => '#1f2937',
                'neutralContent' => '#f3f4f6',
            ],
            'shadow-berry' => [
                'base100' => '#1a1220',
                'base200' => '#140c17',
                'base300' => '#0e0810',
                'baseContent' => '#f3f1f5',
                'primary' => '#a78bfa',
                'primaryContent' => '#1f0f3a',
                'secondary' => '#ff5fa2',
                'secondaryContent' => '#2b041a',
                'accent' => '#6ee7b7',
                'accentContent' => '#07221b',
                'neutral' => '#2c2140',
                'neutralContent' => '#efe6ff',
            ],
            'neon-blush' => [
                'base100' => '#1c0f17',
                'base200' => '#150b11',
                'base300' => '#0f070c',
                'baseContent' => '#fff0f6',
                'primary' => '#ff4ecd',
                'primaryContent' => '#2b041a',
                'secondary' => '#7c9cff',
                'secondaryContent' => '#0b142f',
                'accent' => '#34d399',
                'accentContent' => '#05241c',
                'neutral' => '#2a1b24',
                'neutralContent' => '#fff0f6',
            ],
            'abyss-blue' => [
                'base100' => '#0b1020',
                'base200' => '#070c17',
                'base300' => '#040810',
                'baseContent' => '#e2e8f0',
                'primary' => '#38bdf8',
                'primaryContent' => '#04172b',
                'secondary' => '#818cf8',
                'secondaryContent' => '#0b142f',
                'accent' => '#34d399',
                'accentContent' => '#05241c',
                'neutral' => '#162544',
                'neutralContent' => '#d8dff2',
            ],
            'charcoal-mint' => [
                'base100' => '#1a1f1c',
                'base200' => '#151815',
                'base300' => '#0f110f',
                'baseContent' => '#e9f7f0',
                'primary' => '#34d399',
                'primaryContent' => '#05241c',
                'secondary' => '#2dd4bf',
                'secondaryContent' => '#05241c',
                'accent' => '#fbbf24',
                'accentContent' => '#2f1d05',
                'neutral' => '#22262d',
                'neutralContent' => '#e0e5ec',
            ],
            'galaxy-candy' => [
                'base100' => '#241b33',
                'base200' => '#1d1529',
                'base300' => '#160f20',
                'baseContent' => '#f0e7fb',
                'primary' => '#cf8cff',
                'primaryContent' => '#210f2e',
                'secondary' => '#7de4ff',
                'secondaryContent' => '#081c2b',
                'accent' => '#ff9ac0',
                'accentContent' => '#2f0d1c',
                'neutral' => '#311b4c',
                'neutralContent' => '#eadff6',
            ],
            'violet-storm' => [
                'base100' => '#343c59',
                'base200' => '#3e4664',
                'base300' => '#48506f',
                'baseContent' => '#e8eafb',
                'primary' => '#8da0ff',
                'primaryContent' => '#0b142f',
                'secondary' => '#ff97d0',
                'secondaryContent' => '#2c0c1d',
                'accent' => '#6ee8c5',
                'accentContent' => '#041c14',
                'neutral' => '#162544',
                'neutralContent' => '#d8dff2',
            ],
            'magma-ice' => [
                'base100' => '#3f3748',
                'base200' => '#494254',
                'base300' => '#534c5f',
                'baseContent' => '#f3f1f5',
                'primary' => '#ff817a',
                'primaryContent' => '#2f0c0c',
                'secondary' => '#7be9f4',
                'secondaryContent' => '#072126',
                'accent' => '#ffc96a',
                'accentContent' => '#2f1f05',
                'neutral' => '#352738',
                'neutralContent' => '#ece7ed',
            ],
            'stormy-sea' => [
                'base100' => '#2f4352',
                'base200' => '#394d5d',
                'base300' => '#435668',
                'baseContent' => '#e4eef6',
                'primary' => '#66b6e8',
                'primaryContent' => '#0a1828',
                'secondary' => '#65d6c4',
                'secondaryContent' => '#07221b',
                'accent' => '#f6c869',
                'accentContent' => '#2e1c05',
                'neutral' => '#132f3d',
                'neutralContent' => '#d5e4ec',
            ],
            'lunar-mauve' => [
                'base100' => '#3e3452',
                'base200' => '#483e5e',
                'base300' => '#52486a',
                'baseContent' => '#f1e9f8',
                'primary' => '#c9a1ff',
                'primaryContent' => '#1f0e2e',
                'secondary' => '#ff9dc4',
                'secondaryContent' => '#2c0c1d',
                'accent' => '#84ead7',
                'accentContent' => '#06231a',
                'neutral' => '#311d47',
                'neutralContent' => '#ecdfef',
            ],
            'acid-jungle' => [
                'base100' => '#314134',
                'base200' => '#3b4b3f',
                'base300' => '#45544a',
                'baseContent' => '#e7f6e9',
                'primary' => '#86f05f',
                'primaryContent' => '#10260b',
                'secondary' => '#4fd8c6',
                'secondaryContent' => '#07231d',
                'accent' => '#ffd55f',
                'accentContent' => '#2f1e05',
                'neutral' => '#163615',
                'neutralContent' => '#deecdf',
            ],
            'carbon-ember' => [
                'base100' => '#3f414e',
                'base200' => '#484b57',
                'base300' => '#525462',
                'baseContent' => '#e9eef5',
                'primary' => '#ff7a63',
                'primaryContent' => '#2f0c0a',
                'secondary' => '#6fc8f4',
                'secondaryContent' => '#082332',
                'accent' => '#ffc65a',
                'accentContent' => '#2f1d05',
                'neutral' => '#22262d',
                'neutralContent' => '#e0e5ec',
            ],
        ];

        $palette_source = array_merge($custom_palette, $cache);
        $palette = [];

        foreach ($slugs as $slug) {
            if (!isset($palette_source[$slug])) {
                continue;
            }

            $theme = $palette_source[$slug];

            $palette[$slug] = [
                'base100' => $theme['--color-base-100'] ?? $theme['base100'] ?? '#f3f4f6',
                'base200' => $theme['--color-base-200'] ?? $theme['base200'] ?? '#e5e7eb',
                'base300' => $theme['--color-base-300'] ?? $theme['base300'] ?? '#d1d5db',
                'baseContent' => $theme['--color-base-content'] ?? $theme['baseContent'] ?? '#111827',
                'primary' => $theme['--color-primary'] ?? $theme['primary'] ?? '#570df8',
                'primaryContent' => $theme['--color-primary-content'] ?? $theme['primaryContent'] ?? '#ffffff',
                'secondary' => $theme['--color-secondary'] ?? $theme['secondary'] ?? '#f000b8',
                'secondaryContent' => $theme['--color-secondary-content'] ?? $theme['secondaryContent'] ?? '#ffffff',
                'accent' => $theme['--color-accent'] ?? $theme['accent'] ?? '#37cdbe',
                'accentContent' => $theme['--color-accent-content'] ?? $theme['accentContent'] ?? '#ffffff',
                'neutral' => $theme['--color-neutral'] ?? $theme['neutral'] ?? '#3d4451',
                'neutralContent' => $theme['--color-neutral-content'] ?? $theme['neutralContent'] ?? '#f3f4f6',
            ];
        }

        return $palette;
    }

    public static function outputHeaderScripts(): void
    {
        $value = static::getOptionString('crb_header_scripts', '');
        if ($value === '') {
            return;
        }

        echo $value;
    }

    public static function outputFooterScripts(): void
    {
        $value = static::getOptionString('crb_footer_scripts', '');
        if ($value === '') {
            return;
        }

        echo $value;
    }

    public static function registerMenu(): void
    {
        add_theme_page(
            __('Theme Settings', 'a-ripple-song'),
            __('Theme Settings', 'a-ripple-song'),
            'manage_options',
            static::PAGE_SLUG,
            [static::class, 'renderPage']
        );
    }

    public static function registerSettings(): void
    {
        register_setting(static::SETTINGS_GROUP, static::optionName('crb_light_theme'), [
            'type' => 'string',
            'sanitize_callback' => [static::class, 'sanitizeLightTheme'],
            'default' => 'retro',
        ]);

        register_setting(static::SETTINGS_GROUP, static::optionName('crb_dark_theme'), [
            'type' => 'string',
            'sanitize_callback' => [static::class, 'sanitizeDarkTheme'],
            'default' => 'dim',
        ]);

        register_setting(static::SETTINGS_GROUP, static::optionName('crb_footer_copyright'), [
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => '',
        ]);

        register_setting(static::SETTINGS_GROUP, static::optionName('crb_header_scripts'), [
            'type' => 'string',
            'sanitize_callback' => [static::class, 'sanitizeScripts'],
            'default' => '',
        ]);

        register_setting(static::SETTINGS_GROUP, static::optionName('crb_footer_scripts'), [
            'type' => 'string',
            'sanitize_callback' => [static::class, 'sanitizeScripts'],
            'default' => '',
        ]);

        add_settings_section(
            'aripplesong_theme_settings_general',
            __('General', 'a-ripple-song'),
            '__return_false',
            static::PAGE_SLUG
        );

        add_settings_field(
            static::optionName('crb_light_theme'),
            __('Light Theme', 'a-ripple-song'),
            [static::class, 'renderLightThemeField'],
            static::PAGE_SLUG,
            'aripplesong_theme_settings_general'
        );

        add_settings_field(
            static::optionName('crb_dark_theme'),
            __('Dark Theme', 'a-ripple-song'),
            [static::class, 'renderDarkThemeField'],
            static::PAGE_SLUG,
            'aripplesong_theme_settings_general'
        );

        add_settings_field(
            static::optionName('crb_footer_copyright'),
            __('Footer Copyright', 'a-ripple-song'),
            [static::class, 'renderFooterCopyrightField'],
            static::PAGE_SLUG,
            'aripplesong_theme_settings_general'
        );

        add_settings_section(
            'aripplesong_theme_settings_scripts',
            __('Header Scripts', 'a-ripple-song'),
            '__return_false',
            static::PAGE_SLUG
        );

        add_settings_field(
            static::optionName('crb_header_scripts'),
            __('Header Scripts', 'a-ripple-song'),
            [static::class, 'renderHeaderScriptsField'],
            static::PAGE_SLUG,
            'aripplesong_theme_settings_scripts'
        );

        add_settings_field(
            static::optionName('crb_footer_scripts'),
            __('Footer Scripts', 'a-ripple-song'),
            [static::class, 'renderFooterScriptsField'],
            static::PAGE_SLUG,
            'aripplesong_theme_settings_scripts'
        );

        add_settings_section(
            'aripplesong_theme_settings_social',
            __('Social Links', 'a-ripple-song'),
            '__return_false',
            static::PAGE_SLUG
        );

        foreach (SocialLinks::getPlatforms() as $key => $platform) {
            $label = is_array($platform) && isset($platform['label']) && is_string($platform['label']) ? $platform['label'] : $key;
            $setting_key = static::optionName('crb_social_' . $key);

            register_setting(static::SETTINGS_GROUP, $setting_key, [
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default' => '',
            ]);

            add_settings_field(
                $setting_key,
                $label,
                [static::class, 'renderSocialLinkField'],
                static::PAGE_SLUG,
                'aripplesong_theme_settings_social',
                ['key' => $key]
            );
        }
    }

    public static function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Theme Settings', 'a-ripple-song') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields(static::SETTINGS_GROUP);
        do_settings_sections(static::PAGE_SLUG);
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public static function renderLightThemeField(): void
    {
        $current = static::getLightTheme();
        $name = static::optionName('crb_light_theme');

        echo '<select name="' . esc_attr($name) . '">';
        foreach (static::getDaisyUiLightThemes() as $slug => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($slug),
                selected($current, $slug, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }

    public static function renderDarkThemeField(): void
    {
        $current = static::getDarkTheme();
        $name = static::optionName('crb_dark_theme');

        echo '<select name="' . esc_attr($name) . '">';
        foreach (static::getDaisyUiDarkThemes() as $slug => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($slug),
                selected($current, $slug, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }

    public static function renderFooterCopyrightField(): void
    {
        $name = static::optionName('crb_footer_copyright');
        $value = static::getOptionString('crb_footer_copyright', '');

        printf(
            '<textarea name="%s" rows="2" class="large-text">%s</textarea>',
            esc_attr($name),
            esc_textarea($value)
        );
    }

    public static function renderHeaderScriptsField(): void
    {
        $name = static::optionName('crb_header_scripts');
        $value = static::getOptionString('crb_header_scripts', '');

        printf(
            '<textarea name="%s" rows="6" class="large-text code">%s</textarea>',
            esc_attr($name),
            esc_textarea($value)
        );
    }

    public static function renderFooterScriptsField(): void
    {
        $name = static::optionName('crb_footer_scripts');
        $value = static::getOptionString('crb_footer_scripts', '');

        printf(
            '<textarea name="%s" rows="6" class="large-text code">%s</textarea>',
            esc_attr($name),
            esc_textarea($value)
        );
    }

    /**
     * @param array{key:string} $args
     */
    public static function renderSocialLinkField(array $args): void
    {
        $key = isset($args['key']) && is_string($args['key']) ? $args['key'] : '';
        if ($key === '') {
            return;
        }

        $name = static::optionName('crb_social_' . $key);
        $value = static::getOptionString('crb_social_' . $key, '');

        printf(
            '<input type="url" name="%s" value="%s" class="regular-text" />',
            esc_attr($name),
            esc_attr($value)
        );
    }

    public static function sanitizeLightTheme(mixed $value): string
    {
        $slug = is_string($value) ? sanitize_key(wp_unslash($value)) : '';
        $allowed = array_keys(static::getDaisyUiLightThemes());

        return in_array($slug, $allowed, true) ? $slug : 'retro';
    }

    public static function sanitizeDarkTheme(mixed $value): string
    {
        $slug = is_string($value) ? sanitize_key(wp_unslash($value)) : '';
        $allowed = array_keys(static::getDaisyUiDarkThemes());

        return in_array($slug, $allowed, true) ? $slug : 'dim';
    }

    public static function sanitizeScripts(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $value = wp_unslash($value);

        if (!current_user_can('unfiltered_html')) {
            return wp_kses_post($value);
        }

        return $value;
    }

    private static function optionName(string $key): string
    {
        return '_' . ltrim($key, '_');
    }
}

ThemeSettings::boot();
