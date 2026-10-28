<?php

namespace Jiejia\ARippleSong\Player;

defined('ABSPATH') || exit;

/**
 * Selectable looks of the global player.
 *
 * Each skin has a template (resources/templates/player/skins/{value}.php) and a stylesheet
 * (resources/dist/skins/{value}.css). All but Default were converted from the
 * ~/workspace/podcast theme prototypes.
 */
enum PlayerSkin: string
{
    case Default = 'default';
    case NightSignal = 'night-signal';
    case Broadcast = 'broadcast';
    case EveningPaper = 'evening-paper';
    case Pulse = 'pulse';
    case Cassette = 'cassette';
    case Aurora = 'aurora';
    case Terminal = 'terminal';
    case Zine = 'zine';
    case Soft = 'soft';
    case Circle = 'circle';
    case Coral = 'coral';
    case Wisteria = 'wisteria';

    /**
     * Return the name shown in the settings.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Default => __('Default: plain bar', 'a-ripple-song'),
            self::NightSignal => __('Night Signal: dark tech, neon green', 'a-ripple-song'),
            self::Broadcast => __('Broadcast: Swiss brutalist, ON AIR light', 'a-ripple-song'),
            self::EveningPaper => __('Evening Paper: newspaper, serif', 'a-ripple-song'),
            self::Pulse => __('Pulse: dark app, electric blue', 'a-ripple-song'),
            self::Cassette => __('Cassette: 70s tape deck, VU meters', 'a-ripple-song'),
            self::Aurora => __('Aurora: frosted glass capsule', 'a-ripple-song'),
            self::Terminal => __('Terminal: CRT command line', 'a-ripple-song'),
            self::Zine => __('Zine: risograph collage', 'a-ripple-song'),
            self::Soft => __('Soft: neumorphic radio, volume knob', 'a-ripple-song'),
            self::Circle => __('Circle: social feed card', 'a-ripple-song'),
            self::Coral => __('Coral: listening dashboard', 'a-ripple-song'),
            self::Wisteria => __('Wisteria: soft purple mobile app', 'a-ripple-song'),
        };
    }

    /**
     * Return the Google Fonts stylesheet the prototype uses, if any.
     *
     * @return string
     */
    public function fontsUrl(): string
    {
        $families = match ($this) {
            self::Default => '',
            self::NightSignal => 'family=JetBrains+Mono:wght@400;500&family=Noto+Sans+SC:wght@400;500;700&family=Space+Grotesk:wght@500;600;700',
            self::Broadcast => 'family=Archivo:wght@400;600;800;900&family=IBM+Plex+Mono:wght@400;600&family=Noto+Sans+SC:wght@400;700;900',
            self::EveningPaper => 'family=Instrument+Serif:ital@0;1&family=Noto+Sans+SC:wght@400;500;700&family=Noto+Serif+SC:wght@400;600;900',
            self::Pulse => 'family=Noto+Sans+SC:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800',
            self::Cassette => 'family=Long+Cang&family=Noto+Sans+SC:wght@400;500;700&family=Rubik:wght@400;500;700;800&family=Shrikhand&family=Space+Mono:wght@400;700&family=ZCOOL+KuaiLe',
            self::Aurora => 'family=Noto+Sans+SC:wght@300;400;500;700&family=Outfit:wght@200;300;400;500;600',
            self::Terminal => 'family=JetBrains+Mono:wght@400;700&family=Noto+Sans+SC:wght@400;700&family=VT323',
            self::Zine => 'family=Bricolage+Grotesque:opsz,wght@12..96,800&family=Caveat:wght@600&family=Ma+Shan+Zheng&family=Noto+Sans+SC:wght@400;500;700&family=Space+Grotesk:wght@400;500;700&family=ZCOOL+QingKe+HuangYou',
            self::Soft => 'family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;700;800&family=Noto+Sans+SC:wght@400;500;700;800',
            self::Circle => 'family=Noto+Sans+SC:wght@400;500;600;700&family=Poppins:wght@400;500;600;700',
            self::Coral => 'family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Noto+Sans+SC:wght@400;500;600;700',
            self::Wisteria => 'family=Noto+Sans+SC:wght@400;500;600;700&family=Urbanist:wght@400;500;600;700;800',
        };

        return $families !== '' ? 'https://fonts.googleapis.com/css2?' . $families . '&display=swap' : '';
    }

    /**
     * Return the template name for Template::renderThemeable().
     *
     * @return string
     */
    public function template(): string
    {
        return 'player/skins/' . $this->value;
    }

    /**
     * Return the built stylesheet, relative to the plugin directory.
     *
     * @return string
     */
    public function stylesheet(): string
    {
        return 'resources/dist/skins/' . $this->value . '.css';
    }

    /**
     * Return skin labels keyed by value, for a select field.
     *
     * @return array<string,string>
     */
    public static function options(): array
    {
        return array_column(array_map(static fn (self $skin): array => [$skin->value, $skin->label()], self::cases()), 1, 0);
    }
}
