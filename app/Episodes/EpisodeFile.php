<?php

namespace Jiejia\ARippleSong\Episodes;

defined('ABSPATH') || exit;

use Jiejia\ARippleSong\App;

/**
 * Reads small text files attached to episodes (chapters JSON, transcripts), cached per URL and episode version.
 * Files in the media library are read from disk; a URL on another site is downloaded by the server, since the
 * editor entered it for this episode.
 */
final class EpisodeFile
{
    /**
     * Largest file read, in bytes.
     */
    private const MAX_BYTES = 2 * MB_IN_BYTES;

    /**
     * Return a file's contents; uploads are read from disk, other URLs over HTTP.
     *
     * @param string $url File URL.
     * @param string $version Changes whenever the cache must be ignored, e.g. the episode's modified time.
     * @return string Empty when the file cannot be read.
     */
    public static function read(string $url, string $version): string
    {
        if ($url === '' || ! wp_http_validate_url($url)) {
            return '';
        }

        $key = App::PREFIX . 'episode_file_' . md5($url . '|' . $version);
        $cached = get_transient($key);

        if (is_string($cached)) {
            return $cached;
        }

        $body = self::local($url) ?? self::remote($url);
        set_transient($key, $body, $body === '' ? HOUR_IN_SECONDS : 12 * HOUR_IN_SECONDS);

        return $body;
    }

    /**
     * Read a file from the uploads directory.
     *
     * @param string $url File URL.
     * @return string|null Null when the URL is not an upload.
     */
    private static function local(string $url): ?string
    {
        $uploads = wp_get_upload_dir();
        $base = (string) $uploads['baseurl'];

        if ($base === '' || ! str_starts_with(set_url_scheme($url, 'http'), set_url_scheme($base, 'http') . '/')) {
            return null;
        }

        $path = (string) realpath($uploads['basedir'] . substr(set_url_scheme($url, 'http'), strlen(set_url_scheme($base, 'http'))));

        if ($path === '' || ! str_starts_with($path, (string) realpath($uploads['basedir'])) || ! is_readable($path) || filesize($path) > self::MAX_BYTES) {
            return '';
        }

        return (string) file_get_contents($path);
    }

    /**
     * Download a file.
     *
     * @param string $url File URL.
     * @return string
     */
    private static function remote(string $url): string
    {
        $response = wp_safe_remote_get($url, ['timeout' => 5, 'limit_response_size' => self::MAX_BYTES]);

        return is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 ? '' : (string) wp_remote_retrieve_body($response);
    }
}
