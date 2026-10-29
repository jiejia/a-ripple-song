<?php

namespace Jiejia\ARippleSong\Settings;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\Abstracts\SettingAbstract;

/**
 * Subscribe links settings page: the show's page on each podcast platform, stored in the ars_subscription_settings option.
 */
class SubscriptionSetting extends SettingAbstract
{
    public string $slug = 'subscription_settings';

    public function title(): string
    {
        return __('Subscribe Links', 'a-ripple-song');
    }

    /**
     * Return subscribe link field definitions: one optional URL field per platform, grouped by separators.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        $fields = [];

        foreach ($this->groups() as $group) {
            $fields[] = [
                'type' => 'separator',
                'label' => $group['label'],
            ];

            foreach ($group['platforms'] as $key => [$label, $placeholder]) {
                $fields[] = [
                    'type' => 'url',
                    'key' => $key,
                    'label' => $label,
                    'width' => 'half',
                    'placeholder' => $placeholder,
                ];
            }
        }

        return $fields;
    }

    /**
     * Return the filled-in links in settings page order, for themes to print subscribe buttons.
     *
     * @return array<int,array{key:string,label:string,url:string}>
     */
    public function links(): array
    {
        $links = [];

        foreach ($this->groups() as $group) {
            foreach ($group['platforms'] as $key => [$label]) {
                $url = (string) $this->value($key);

                if ($url !== '') {
                    $links[] = ['key' => $key, 'label' => $label, 'url' => $url];
                }
            }
        }

        return $links;
    }

    /**
     * Return the platforms by group: key => [label, placeholder showing the platform's show URL].
     *
     * @return array<int,array{label:string,platforms:array<string,array{0:string,1:string}>}>
     */
    private function groups(): array
    {
        return [
            [
                'label' => __('Major Platforms', 'a-ripple-song'),
                'platforms' => [
                    'apple_podcasts' => ['Apple Podcasts', 'https://podcasts.apple.com/podcast/id…'],
                    'spotify' => ['Spotify', 'https://open.spotify.com/show/…'],
                    'youtube' => ['YouTube', 'https://www.youtube.com/@…'],
                    'youtube_music' => ['YouTube Music', 'https://music.youtube.com/playlist?list=…'],
                    'amazon_music' => ['Amazon Music', 'https://music.amazon.com/podcasts/…'],
                    'audible' => ['Audible', 'https://www.audible.com/podcast/…'],
                    'iheartradio' => ['iHeartRadio', 'https://www.iheart.com/podcast/…'],
                    'pandora' => ['Pandora', 'https://www.pandora.com/podcast/…'],
                    'deezer' => ['Deezer', 'https://www.deezer.com/show/…'],
                    'tunein' => ['TuneIn', 'https://tunein.com/podcasts/…'],
                ],
            ],
            [
                'label' => __('Podcast Apps', 'a-ripple-song'),
                'platforms' => [
                    'pocket_casts' => ['Pocket Casts', 'https://pca.st/…'],
                    'overcast' => ['Overcast', 'https://overcast.fm/itunes…'],
                    'castro' => ['Castro', 'https://castro.fm/podcast/…'],
                    'castbox' => ['Castbox', 'https://castbox.fm/channel/…'],
                    'podcast_addict' => ['Podcast Addict', 'https://podcastaddict.com/podcast/…'],
                    'player_fm' => ['Player FM', 'https://player.fm/series/…'],
                    'podbean' => ['Podbean', 'https://www.podbean.com/podcast-detail/…'],
                    'goodpods' => ['Goodpods', 'https://goodpods.com/podcasts/…'],
                    'snipd' => ['Snipd', 'https://share.snipd.com/show/…'],
                    'fountain' => ['Fountain', 'https://fountain.fm/show/…'],
                    'podverse' => ['Podverse', 'https://podverse.fm/podcast/…'],
                ],
            ],
            [
                'label' => __('Chinese Platforms', 'a-ripple-song'),
                'platforms' => [
                    'xiaoyuzhou' => [__('Xiaoyuzhou', 'a-ripple-song'), 'https://www.xiaoyuzhoufm.com/podcast/…'],
                    'ximalaya' => [__('Ximalaya', 'a-ripple-song'), 'https://www.ximalaya.com/album/…'],
                    'netease_music' => [__('NetEase Cloud Music', 'a-ripple-song'), 'https://music.163.com/#/djradio?id=…'],
                    'qq_music' => [__('QQ Music', 'a-ripple-song'), 'https://y.qq.com/n/ryqq/…'],
                    'lizhi' => [__('Lizhi FM', 'a-ripple-song'), 'https://www.lizhi.fm/…'],
                    'qingting' => [__('Qingting FM', 'a-ripple-song'), 'https://www.qingting.fm/channels/…'],
                    'bilibili' => [__('Bilibili', 'a-ripple-song'), 'https://space.bilibili.com/…'],
                ],
            ],
            [
                'label' => __('Directories & Smart Links', 'a-ripple-song'),
                'platforms' => [
                    'podcast_index' => ['Podcast Index', 'https://podcastindex.org/podcast/…'],
                    'podchaser' => ['Podchaser', 'https://www.podchaser.com/podcasts/…'],
                    'pod_link' => ['pod.link', 'https://pod.link/…'],
                ],
            ],
        ];
    }
}
