<?php

namespace Jiejia\ARippleSong\Libraries\Fields;

defined('ABSPATH') || exit;

/**
 * Facts about a media file read from its first bytes: size, real type and PNG transparency.
 *
 * Media library files are read from disk, other URLs with one ranged GET request.
 * Facts that cannot be read are null (size, alpha) or "" (MIME type).
 */
final readonly class FileProbe
{
    /**
     * Bytes read from the start of a file; PNG transparency is declared before the image data.
     */
    private const HEAD_BYTES = 65536;

    private const PNG_SIGNATURE = "\x89PNG\r\n\x1a\n";

    /**
     * @param int|null $size File size in bytes.
     * @param string $mime MIME type sniffed from the file's bytes, else the declared one.
     * @param bool|null $hasAlpha Whether the image can be transparent; null when unknown or not an image.
     */
    public function __construct(
        public ?int $size = null,
        public string $mime = '',
        public ?bool $hasAlpha = null,
    ) {
    }

    /**
     * Probe a file once per request.
     *
     * @param string $url File URL.
     * @return self
     */
    public static function fromUrl(string $url): self
    {
        static $cache = [];

        return $cache[$url] ??= self::read($url);
    }

    /**
     * Short label of a MIME type for messages, e.g. "image/jpeg" => "JPG".
     *
     * @param string $mime MIME type.
     * @return string
     */
    public static function label(string $mime): string
    {
        $subtype = strtoupper((string) substr(strrchr($mime, '/') ?: '', 1));

        return $subtype === 'JPEG' ? 'JPG' : $subtype;
    }

    /**
     * Read a media library file from disk, or the start of any other URL.
     *
     * @param string $url File URL.
     * @return self
     */
    private static function read(string $url): self
    {
        $attachmentId = attachment_url_to_postid($url);

        if ($attachmentId > 0) {
            $mime = (string) get_post_mime_type($attachmentId);
            $path = (string) get_attached_file($attachmentId);

            if ($path === '' || ! is_readable($path)) {
                return new self(mime: $mime);
            }

            $meta = wp_get_attachment_metadata($attachmentId);
            $size = is_array($meta) && ! empty($meta['filesize']) ? (int) $meta['filesize'] : (int) filesize($path);

            return self::fromBytes((string) file_get_contents($path, false, null, 0, self::HEAD_BYTES), $size, $mime);
        }

        // limit_response_size stops the download when a server ignores the Range header.
        $response = wp_safe_remote_get($url, [
            'timeout' => 5,
            'headers' => ['Range' => 'bytes=0-' . (self::HEAD_BYTES - 1)],
            'limit_response_size' => self::HEAD_BYTES,
        ]);
        $status = (int) wp_remote_retrieve_response_code($response);

        if ($status !== 200 && $status !== 206) {
            return new self();
        }

        // 206: "bytes 0-65535/830000" holds the total; 200: Content-Length is the whole file.
        $range = wp_remote_retrieve_header($response, 'content-range');
        $length = wp_remote_retrieve_header($response, 'content-length');
        $size = match (true) {
            is_string($range) && preg_match('#/(\d+)$#', $range, $match) === 1 => (int) $match[1],
            $status === 200 && is_numeric($length) => (int) $length,
            default => null,
        };
        $type = wp_remote_retrieve_header($response, 'content-type');

        return self::fromBytes(
            wp_remote_retrieve_body($response),
            $size,
            is_string($type) ? strtolower(trim((string) strtok($type, ';'))) : ''
        );
    }

    /**
     * Build the facts from the first bytes of a file.
     *
     * @param string $bytes Start of the file.
     * @param int|null $size File size in bytes.
     * @param string $declaredMime MIME type from the media library or the Content-Type header.
     * @return self
     */
    private static function fromBytes(string $bytes, ?int $size, string $declaredMime): self
    {
        $mime = self::sniff($bytes) ?: $declaredMime;

        return new self(
            size: $size !== null && $size > 0 ? $size : null,
            mime: $mime,
            hasAlpha: match ($mime) {
                'image/png' => self::pngHasAlpha($bytes),
                'image/jpeg' => false,
                default => null,
            },
        );
    }

    /**
     * Detect common image types by their magic bytes.
     *
     * @param string $bytes Start of the file.
     * @return string MIME type, or "" when unknown.
     */
    private static function sniff(string $bytes): string
    {
        return match (true) {
            str_starts_with($bytes, "\xFF\xD8\xFF") => 'image/jpeg',
            str_starts_with($bytes, self::PNG_SIGNATURE) => 'image/png',
            str_starts_with($bytes, 'GIF8') => 'image/gif',
            str_starts_with($bytes, 'RIFF') && substr($bytes, 8, 4) === 'WEBP' => 'image/webp',
            default => '',
        };
    }

    /**
     * Whether a PNG has an alpha channel (color type 4 or 6) or a tRNS transparency chunk.
     *
     * @param string $bytes Start of the PNG file.
     * @return bool|null Null when the chunks before the image data were not all read.
     */
    private static function pngHasAlpha(string $bytes): ?bool
    {
        if (strlen($bytes) < 26) {
            return null;
        }

        // IHDR is always the first chunk; its color type is byte 25 of the file.
        if (in_array(ord($bytes[25]), [4, 6], true)) {
            return true;
        }

        // Each chunk: 4-byte length, 4-byte type, data, 4-byte CRC.
        for ($offset = 8; $offset + 8 <= strlen($bytes); $offset += 12 + $length) {
            $length = unpack('N', substr($bytes, $offset, 4))[1];
            $type = substr($bytes, $offset + 4, 4);

            if ($type === 'tRNS') {
                return true;
            }

            if ($type === 'IDAT' || $type === 'IEND') {
                return false;
            }
        }

        return null;
    }
}
