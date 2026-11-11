<?php

namespace Jiejia\ARippleSong\Episodes;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\CPTs\PodcastCPT;

/**
 * Reads an episode's transcript file (the "Transcript" field): WebVTT, SRT, Podcasting 2.0 JSON or
 * plain text, for themes that print the transcript: Transcript::forPost($post). Other formats (PDF,
 * HTML) come back without cues, to be linked.
 */
final class Transcript
{
    /**
     * Cues kept from very long transcripts.
     */
    private const MAX_CUES = 3000;

    /**
     * Return the transcript's URL and its cues.
     *
     * @param \WP_Post $post Episode.
     * @return array{url:string,cues:array<int,array{start:int|null,speaker:string,text:string}>}
     */
    public static function forPost(\WP_Post $post): array
    {
        $url = (string) get_post_meta($post->ID, (new PodcastCPT())->fieldMetaKey('episode_transcript'), true);
        $extension = strtolower(pathinfo((string) wp_parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        if ($url === '' || ! in_array($extension, ['vtt', 'srt', 'json', 'txt'], true)) {
            return ['url' => $url, 'cues' => []];
        }

        $body = EpisodeFile::read($url, $post->post_modified_gmt);

        return ['url' => $url, 'cues' => $body === '' ? [] : self::parse($body, $extension)];
    }

    /**
     * Parse a transcript, merging consecutive lines of the same speaker.
     *
     * @param string $body File contents.
     * @param string $format vtt, srt, json or txt.
     * @return array<int,array{start:int|null,speaker:string,text:string}>
     */
    public static function parse(string $body, string $format): array
    {
        $body = str_replace(["\r\n", "\r"], "\n", (string) preg_replace('/^\xEF\xBB\xBF/', '', $body));
        $cues = match ($format) {
            'json' => self::json($body),
            'vtt', 'srt' => self::timed($body),
            default => self::text($body),
        };

        return array_slice(self::merge($cues), 0, self::MAX_CUES);
    }

    /**
     * @return array<int,array{start:int|null,speaker:string,text:string}>
     */
    private static function json(string $body): array
    {
        $data = json_decode($body, true);
        $cues = [];

        foreach (is_array($data) && is_array($data['segments'] ?? null) ? $data['segments'] : [] as $segment) {
            if (is_array($segment) && trim((string) ($segment['body'] ?? '')) !== '') {
                $cues[] = [
                    'start' => isset($segment['startTime']) ? (int) floor((float) $segment['startTime']) : null,
                    'speaker' => sanitize_text_field((string) ($segment['speaker'] ?? '')),
                    'text' => sanitize_text_field((string) $segment['body']),
                ];
            }
        }

        return $cues;
    }

    /**
     * WebVTT and SRT: blocks of an optional id, a "start --> end" line and text lines.
     *
     * @return array<int,array{start:int|null,speaker:string,text:string}>
     */
    private static function timed(string $body): array
    {
        $cues = [];

        foreach (preg_split('/\n{2,}/', trim($body)) ?: [] as $block) {
            $lines = explode("\n", trim($block));
            $timing = null;

            foreach ($lines as $index => $line) {
                if (str_contains($line, '-->')) {
                    $timing = $index;
                    break;
                }
            }

            if ($timing === null) {
                continue;
            }

            $text = trim(implode(' ', array_slice($lines, $timing + 1)));
            $speaker = '';

            // WebVTT voice span: <v Speaker Name>text</v>
            if (preg_match('/<v(?:\.[^\s>]+)?\s+([^>]+)>/', $text, $match)) {
                $speaker = trim($match[1]);
            }

            $text = trim(html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES, 'UTF-8'));

            if ($text !== '') {
                $cues[] = ['start' => self::seconds(trim(explode('-->', $lines[$timing])[0])), 'speaker' => sanitize_text_field($speaker), 'text' => sanitize_text_field($text)];
            }
        }

        return $cues;
    }

    /**
     * Plain text: one cue per paragraph, "Speaker: text" lines keep their speaker.
     *
     * @return array<int,array{start:int|null,speaker:string,text:string}>
     */
    private static function text(string $body): array
    {
        $cues = [];

        foreach (preg_split('/\n{2,}/', trim($body)) ?: [] as $paragraph) {
            $paragraph = trim((string) preg_replace('/\s+/u', ' ', $paragraph));
            $speaker = '';

            if (preg_match('/^([^:：]{1,24})[:：]\s*(.+)$/u', $paragraph, $match)) {
                [$speaker, $paragraph] = [trim($match[1]), $match[2]];
            }

            if ($paragraph !== '') {
                $cues[] = ['start' => null, 'speaker' => sanitize_text_field($speaker), 'text' => sanitize_text_field($paragraph)];
            }
        }

        return $cues;
    }

    /**
     * Join consecutive cues of the same speaker into one, so captions read as paragraphs.
     *
     * @param array<int,array{start:int|null,speaker:string,text:string}> $cues Cues.
     * @return array<int,array{start:int|null,speaker:string,text:string}>
     */
    private static function merge(array $cues): array
    {
        $merged = [];

        foreach ($cues as $cue) {
            $last = array_key_last($merged);

            // Speakerless captions stay separate: each keeps its own timestamp.
            if ($last !== null && $cue['speaker'] !== '' && $merged[$last]['speaker'] === $cue['speaker']) {
                $merged[$last]['text'] .= ' ' . $cue['text'];
                continue;
            }

            $merged[] = $cue;
        }

        return $merged;
    }

    /**
     * Convert "01:02:03.456", "02:03,456" or "02:03" to whole seconds.
     *
     * @param string $time Timestamp.
     * @return int
     */
    private static function seconds(string $time): int
    {
        $parts = array_map('floatval', explode(':', str_replace(',', '.', $time)));
        $seconds = 0.0;

        foreach ($parts as $part) {
            $seconds = $seconds * 60 + $part;
        }

        return (int) floor($seconds);
    }
}
