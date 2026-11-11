<?php

namespace Jiejia\ARippleSong\Episodes;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\CPTs\PodcastCPT;

/**
 * Reads an episode's Podcasting 2.0 chapters file (the "Chapters" field), for themes that show a
 * table of contents: Chapters::forPost($post).
 *
 * @see https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/examples/chapters/jsonChapters.md
 */
final class Chapters
{
    /**
     * Return the episode's chapters, each ending where the next one starts.
     *
     * @param \WP_Post $post Episode.
     * @return array<int,array{start:int,end:int,title:string,url:string}>
     */
    public static function forPost(\WP_Post $post): array
    {
        $podcast = new PodcastCPT();
        $body = EpisodeFile::read((string) get_post_meta($post->ID, $podcast->fieldMetaKey('episode_chapters'), true), $post->post_modified_gmt);
        $duration = max(0, (int) get_post_meta($post->ID, $podcast->fieldMetaKey('duration'), true));

        return $body === '' ? [] : self::parse($body, $duration);
    }

    /**
     * Parse a JSON chapters document.
     *
     * @param string $json Chapters JSON.
     * @param int $duration Episode duration in seconds, the end of the last chapter.
     * @return array<int,array{start:int,end:int,title:string,url:string}>
     */
    public static function parse(string $json, int $duration): array
    {
        $data = json_decode($json, true);
        $chapters = [];

        foreach (is_array($data) && is_array($data['chapters'] ?? null) ? $data['chapters'] : [] as $chapter) {
            // "toc": false marks chapters meant for apps only (e.g. artwork changes), not the table of contents.
            if (! is_array($chapter) || ($chapter['toc'] ?? true) === false || trim((string) ($chapter['title'] ?? '')) === '') {
                continue;
            }

            $chapters[] = [
                'start' => max(0, (int) floor((float) ($chapter['startTime'] ?? 0))),
                'end' => 0,
                'title' => sanitize_text_field((string) $chapter['title']),
                'url' => esc_url_raw((string) ($chapter['url'] ?? '')),
            ];
        }

        usort($chapters, static fn (array $a, array $b): int => $a['start'] <=> $b['start']);

        foreach ($chapters as $index => &$chapter) {
            $chapter['end'] = $chapters[$index + 1]['start'] ?? max($duration, $chapter['start'] + 1);
        }

        return $chapters;
    }
}
