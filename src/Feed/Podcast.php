<?php

namespace ARippleSong\Feed;

use ARippleSong\Core\LegacyMeta;
use ARippleSong\PostTypes\Episode;
use ARippleSong\Settings\Podcast as PodcastSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register /feed/podcast for podcast directories (Apple Podcasts / Spotify / etc).
 *
 * Ported from the previous theme implementation and adapted for this plugin.
 *
 * @package    ARippleSong
 * @subpackage ARippleSong/includes
 */
class Podcast {

	/**
	 * Register the podcast feed endpoint.
	 */
	public function registerFeed() {
		add_feed( 'podcast', array( $this, 'renderFeed' ) );
	}

	/**
	 * Fix archive query being incorrectly identified as a feed.
	 *
	 * @param \WP_Query $query
	 */
	public function fixPodcastArchiveQuery( $query ) {
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		if ( ! $query->is_feed || $query->get( 'feed' ) !== 'podcast' ) {
			return;
		}

		if ( $this->isValidPodcastFeedUrl() ) {
			return;
		}

		$query->is_feed = false;
		$query->set( 'feed', '' );
		unset( $query->query_vars['feed'] );

		global $wp;
		if ( isset( $wp ) && $wp instanceof \WP ) {
			unset( $wp->query_vars['feed'] );
		}

		if ( post_type_exists( Episode::POST_TYPE ) ) {
			$query->is_post_type_archive = true;
			$query->is_archive           = true;
			$query->set( 'post_type', Episode::POST_TYPE );
		}
	}

	/**
	 * Check if current URL is a valid podcast feed URL.
	 *
	 * @return bool
	 */
	private function isValidPodcastFeedUrl() {
		$request_uri_raw = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$request_url     = esc_url_raw( home_url( $request_uri_raw ) );
		$path            = wp_parse_url( $request_url, PHP_URL_PATH );
		$query           = wp_parse_url( $request_url, PHP_URL_QUERY );

		if ( is_string( $path ) && preg_match( '~(?:feed/podcast|podcast/feed)/?$~', $path ) ) {
			return true;
		}

		if ( is_string( $query ) && preg_match( '~(?:^|&)feed=podcast(?:&|$)~', $query ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Force stable headers for the podcast feed, including for HEAD requests.
	 */
	public function forcePodcastFeedHeaders() {
		$isPodcastFeed = ( function_exists( 'is_feed' ) && is_feed( 'podcast' ) ) || $this->isValidPodcastFeedUrl();
		if ( ! $isPodcastFeed ) {
			return;
		}

		header( 'Content-Type: application/rss+xml; charset=UTF-8', true );
		header( 'X-Content-Type-Options: nosniff', true );
	}

	/**
	 * Prevent a non-feed URL from rendering RSS.
	 */
	public function preventPodcastSlugFromRenderingFeed() {
		if ( ! function_exists( 'is_feed' ) || ! is_feed( 'podcast' ) ) {
			return;
		}

		if ( $this->isValidPodcastFeedUrl() ) {
			return;
		}

		global $wp_query;
		$wp_query->is_feed = false;
		$wp_query->set( 'feed', '' );
		unset( $wp_query->query_vars['feed'] );

		remove_all_actions( 'do_feed_podcast' );

		if ( post_type_exists( Episode::POST_TYPE ) ) {
			$wp_query->is_post_type_archive = true;
			$wp_query->is_archive           = true;
			$wp_query->set( 'post_type', Episode::POST_TYPE );

			add_filter(
				'template_include',
				static function ( $template ) {
					$new_template = get_post_type_archive_template();
					if ( $new_template ) {
						return $new_template;
					}
					$archive = get_archive_template();
					return $archive ? $archive : $template;
				},
				999
			);
		}
	}

	/**
	 * One-time rewrite flush so `/feed/podcast/` starts working after deployment.
	 */
	public function maybeFlushRewriteRules() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$flag = 'aripplesong_podcast_feed_rewrite_flushed_v1';
		if ( get_option( $flag ) ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( $flag, '1', 'no' );
	}

	/**
	 * Prevent WordPress canonical redirects from rewriting the feed URL.
	 *
	 * @param string|false $redirect_url
	 * @param string       $requested_url
	 * @return string|false
	 */
	public function preventCanonicalRedirectForPodcastFeed( $redirect_url, $requested_url ) {
		if ( function_exists( 'is_feed' ) && is_feed( 'podcast' ) ) {
			return false;
		}

		$path = wp_parse_url( $requested_url, PHP_URL_PATH );
		if ( is_string( $path ) && preg_match( '~/(?:feed/podcast|podcast/feed)/?$~', $path ) ) {
			return false;
		}

		$query = wp_parse_url( $requested_url, PHP_URL_QUERY );
		if ( is_string( $query ) && preg_match( '~(?:^|&)feed=podcast(?:&|$)~', $query ) ) {
			return false;
		}

		return $redirect_url;
	}

	/**
	 * Whether rewrite rules include the custom `podcast` feed endpoint.
	 *
	 * @return bool
	 */
	private function prettyPodcastFeedIsRegistered() {
		$rules = get_option( 'rewrite_rules' );
		if ( ! is_array( $rules ) ) {
			return false;
		}

		foreach ( array_keys( $rules ) as $regex ) {
			if ( strpos( $regex, 'podcast' ) !== false && strpos( $regex, 'feed' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Canonical feed URL (prefer pretty permalink, fallback to query form).
	 *
	 * @return string
	 */
	private function getCanonicalFeedUrl() {
		$permalink_structure = get_option( 'permalink_structure' );

		if ( empty( $permalink_structure ) ) {
			return add_query_arg( 'feed', 'podcast', home_url( '/' ) );
		}

		if ( $this->prettyPodcastFeedIsRegistered() ) {
			if ( is_string( $permalink_structure ) && strpos( $permalink_structure, '/index.php/' ) === 0 ) {
				return home_url( '/index.php/feed/podcast/' );
			}

			return home_url( '/feed/podcast/' );
		}

		return add_query_arg( 'feed', 'podcast', home_url( '/' ) );
	}

	/**
	 * Prefer the attachment URL when the media was selected from the WP Media Library.
	 *
	 * @param int    $postId
	 * @param string $urlMetaKey
	 * @return string
	 */
	private function resolveMediaUrl( $postId, $urlMetaKey ) {
		$url = (string) $this->getEpisodeMetaValue( $postId, $urlMetaKey, '' );

		$attachmentId = (int) $this->getEpisodeMetaValue( $postId, $urlMetaKey . '_id', 0 );
		if ( $attachmentId > 0 ) {
			$attachmentUrl = wp_get_attachment_url( $attachmentId );
			if ( is_string( $attachmentUrl ) && $attachmentUrl !== '' ) {
				return $attachmentUrl;
			}
		}

		if ( $url !== '' && filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$maybeId = attachment_url_to_postid( $url );
			if ( $maybeId ) {
				$attachmentUrl = wp_get_attachment_url( $maybeId );
				if ( is_string( $attachmentUrl ) && $attachmentUrl !== '' ) {
					return $attachmentUrl;
				}
			}

			$maybePostId = url_to_postid( $url );
			if ( $maybePostId && get_post_type( $maybePostId ) === 'attachment' ) {
				$attachmentUrl = wp_get_attachment_url( $maybePostId );
				if ( is_string( $attachmentUrl ) && $attachmentUrl !== '' ) {
					return $attachmentUrl;
				}
			}
		}

		return $url;
	}

	/**
	 * Get Episode Details values from native post meta.
	 *
	 * @param int    $post_id
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	private function getEpisodeMetaValue( $post_id, $key, $default ) {
		if ( in_array( (string) $key, array( 'members', 'guests', 'episode_soundbites' ), true ) ) {
			return LegacyMeta::getPostMetaArray( $post_id, $key, is_array( $default ) ? $default : array() );
		}

		return LegacyMeta::getPostMetaValue( $post_id, $key, $default );
	}

	/**
	 * Encode non-ASCII characters in the URL path for better compatibility.
	 *
	 * @param string $url
	 * @return string
	 */
	private function encodeUrlPathForRss( $url ) {
		$url = trim( (string) $url );
		if ( $url === '' ) {
			return '';
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return $url;
		}

		$scheme   = isset( $parts['scheme'] ) ? (string) $parts['scheme'] : '';
		$host     = (string) $parts['host'];
		$port     = isset( $parts['port'] ) ? (int) $parts['port'] : null;
		$user     = isset( $parts['user'] ) ? (string) $parts['user'] : '';
		$pass     = isset( $parts['pass'] ) ? (string) $parts['pass'] : '';
		$path     = isset( $parts['path'] ) ? (string) $parts['path'] : '';
		$query    = isset( $parts['query'] ) ? (string) $parts['query'] : '';
		$fragment = isset( $parts['fragment'] ) ? (string) $parts['fragment'] : '';

		if ( $path !== '' ) {
			$segments = explode( '/', $path );
			foreach ( $segments as $i => $segment ) {
				if ( $segment === '' ) {
					continue;
				}
				$segments[ $i ] = rawurlencode( rawurldecode( $segment ) );
			}
			$path = implode( '/', $segments );
		}

		$rebuilt = '';
		if ( $scheme !== '' ) {
			$rebuilt .= $scheme . '://';
		}

		if ( $user !== '' ) {
			$rebuilt .= $user;
			if ( $pass !== '' ) {
				$rebuilt .= ':' . $pass;
			}
			$rebuilt .= '@';
		}

		$rebuilt .= $host;
		if ( $port ) {
			$rebuilt .= ':' . $port;
		}

		$rebuilt .= $path;

		if ( $query !== '' ) {
			$rebuilt .= '?' . $query;
		}

		if ( $fragment !== '' ) {
			$rebuilt .= '#' . $fragment;
		}

		return $rebuilt;
	}

	/**
	 * Best-effort transcript MIME type from URL.
	 *
	 * @param string $url
	 * @return string
	 */
	private function guessTranscriptType( $url ) {
		$path = (string) ( wp_parse_url( $url, PHP_URL_PATH ) ?? '' );
		$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		switch ( $ext ) {
			case 'txt':
				return 'text/plain';
			case 'html':
			case 'htm':
				return 'text/html';
			case 'vtt':
				return 'text/vtt';
			case 'srt':
				return 'application/srt';
			case 'json':
				return 'application/json';
			case 'pdf':
				return 'application/pdf';
			default:
				return 'text/html';
		}
	}

	/**
	 * Normalize stored values into user IDs.
	 *
	 * @param mixed $raw
	 * @return array<int,int>
	 */
	private function normalizeUserIds( $raw ) {
		$ids = array();

		if ( is_array( $raw ) ) {
			foreach ( $raw as $key => $value ) {
				if ( is_numeric( $key ) ) {
					$ids[] = (int) $key;
				}
				if ( is_numeric( $value ) ) {
					$ids[] = (int) $value;
				}
				if ( is_array( $value ) && isset( $value['id'] ) && is_numeric( $value['id'] ) ) {
					$ids[] = (int) $value['id'];
				}
				if ( is_string( $value ) && strpos( $value, ':' ) !== false ) {
					$parts = explode( ':', $value );
					$maybe = end( $parts );
					if ( is_numeric( $maybe ) ) {
						$ids[] = (int) $maybe;
					}
				}
			}
		} elseif ( is_numeric( $raw ) ) {
			$ids[] = (int) $raw;
		} elseif ( is_string( $raw ) && strpos( $raw, ':' ) !== false ) {
			$parts = explode( ':', $raw );
			$maybe = end( $parts );
			if ( is_numeric( $maybe ) ) {
				$ids[] = (int) $maybe;
			}
		}

		$ids = array_filter(
			array_unique( $ids ),
			static function ( $id ) {
				return $id > 0;
			}
		);

		return array_values( $ids );
	}

	/**
	 * Build Podcasting 2.0 person entries for a role.
	 *
	 * @param mixed  $rawIds
	 * @param string $role
	 * @return array<int,array{name:string,role:string,href:string,img:string}>
	 */
	private function buildPersonEntries( $rawIds, $role ) {
		$ids = $this->normalizeUserIds( $rawIds );
		if ( empty( $ids ) ) {
			return array();
		}

		$people = array();
		foreach ( $ids as $user_id ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				continue;
			}

			$name = $user->display_name ? $user->display_name : ( $user->user_nicename ? $user->user_nicename : $user->user_login );
			if ( $name === '' ) {
				continue;
			}

			$people[] = array(
				'name' => $name,
				'role' => $role,
				'href' => $user->user_url ? $user->user_url : '',
				'img'  => get_avatar_url( $user_id, array( 'size' => 300 ) ) ? get_avatar_url( $user_id, array( 'size' => 300 ) ) : '',
			);
		}

		return $people;
	}

	/**
	 * Get episode-level people for Podcasting 2.0 person tags.
	 *
	 * @param int $post_id
	 * @return array
	 */
	private function getEpisodePeople( $post_id ) {
		$members = $this->buildPersonEntries( $this->getEpisodeMetaValue( $post_id, 'members', array() ), 'host' );
		$guests  = $this->buildPersonEntries( $this->getEpisodeMetaValue( $post_id, 'guests', array() ), 'guest' );

		$people = array_merge( $members, $guests );
		if ( empty( $people ) ) {
			return array();
		}

		$deduped = array();
		foreach ( $people as $person ) {
			$key = strtolower( $person['name'] ) . '|' . $person['role'];
			if ( isset( $deduped[ $key ] ) ) {
				continue;
			}
			$deduped[ $key ] = $person;
		}

		return array_values( $deduped );
	}

	/**
	 * Normalize <itunes:explicit> values to Apple-accepted values.
	 *
	 * @param mixed  $value
	 * @param string $fallback
	 * @return string
	 */
	private function normalizeItunesExplicit( $value, $fallback = 'false' ) {
		$normalized = strtolower( trim( (string) $value ) );

		if ( in_array( $normalized, array( 'yes', 'true', '1', 'explicit' ), true ) ) {
			return 'true';
		}

		if ( in_array( $normalized, array( 'no', 'false', '0', 'clean' ), true ) ) {
			return 'false';
		}

		return $fallback;
	}

	/**
	 * Normalize the RSS 2.0 <language> value to ISO 639-1 (two-letter) code.
	 *
	 * Some feed validators incorrectly require ISO 639-1 instead of RFC 1766/BCP47.
	 * Example: "en-US" => "en".
	 *
	 * @param mixed  $value
	 * @param string $fallback
	 * @return string
	 */
	private function normalizeRssLanguageIso6391( $value, $fallback = 'en' ) {
		$raw = strtolower( trim( (string) $value ) );
		if ( $raw === '' ) {
			return $fallback;
		}

		$raw = str_replace( '_', '-', $raw );
		$primary = explode( '-', $raw )[0] ?? '';

		if ( preg_match( '/^[a-z]{2}$/', $primary ) ) {
			return $primary;
		}

		return $fallback;
	}

	/**
	 * Format a timestamp in a strict RFC 822-style date string using UTC offset.
	 *
	 * @param int $timestamp
	 * @return string
	 */
	private function formatRfc2822Gmt( $timestamp ) {
		$timestamp = (int) $timestamp;
		if ( $timestamp <= 0 ) {
			$timestamp = time();
		}
		// RSS 2.0 historically references RFC 822 (2-digit year). Some validators enforce this strictly.
		return gmdate( 'D, d M y H:i:s O', $timestamp );
	}

	/**
	 * Normalize enclosure MIME type based on URL/path.
	 *
	 * @param string $audio_url
	 * @param string $audio_mime
	 * @return string
	 */
	private function normalizeEnclosureMime( $audio_url, $audio_mime ) {
		$audio_mime = trim( (string) $audio_mime );
		$path       = (string) wp_parse_url( (string) $audio_url, PHP_URL_PATH );
		$ext        = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		if ( $ext === 'm4a' ) {
			return 'audio/x-m4a';
		}

		return $audio_mime;
	}

	/**
	 * Format seconds to HH:MM:SS.
	 *
	 * @param int $seconds
	 * @return string
	 */
	private function formatDuration( $seconds ) {
		$seconds = max( 0, (int) $seconds );
		$h       = floor( $seconds / 3600 );
		$m       = floor( ( $seconds % 3600 ) / 60 );
		$s       = $seconds % 60;

		if ( $h > 0 ) {
			return sprintf( '%02d:%02d:%02d', $h, $m, $s );
		}

		return sprintf( '%02d:%02d', $m, $s );
	}

	/**
	 * Remove excerpt suffixes like "&hellip; Continued" from RSS summary fields.
	 *
	 * @param string $text
	 * @return string
	 */
	private function sanitizeRssSummary( $text ) {
		$text = wp_strip_all_tags( (string) $text, true );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = preg_replace( '/\\s+/u', ' ', $text ) ?? $text;
		$text = preg_replace( '/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/u', '', $text ) ?? $text;
		$text = str_replace( array( '**', '__' ), '', $text );

		$text = preg_replace(
			'/\\s*(?:\\[\\s*)?(?:&hellip;|…|\\.{3})(?:\\s*\\])?\\s*(?:continued|continue\\s+reading|read\\s+more|\\x{7EE7}\\x{7EED}\\x{9605}\\x{8BFB}|\\x{7E7C}\\x{7E8C}\\x{95B1}\\x{8B80}|\\x{9605}\\x{8BFB}\\x{66F4}\\x{591A})\\s*$/iu',
			'',
			$text
		) ?? $text;

		$text = preg_replace(
			'/\\s*(?:continued|continue\\s+reading|read\\s+more|\\x{7EE7}\\x{7EED}\\x{9605}\\x{8BFB}|\\x{7E7C}\\x{7E8C}\\x{95B1}\\x{8B80}|\\x{9605}\\x{8BFB}\\x{66F4}\\x{591A})\\s*$/iu',
			'',
			$text
		) ?? $text;

		return trim( $text );
	}

	/**
	 * Escape the CDATA terminator so we can safely wrap text in a CDATA section.
	 *
	 * @param string $text
	 * @return string
	 */
	private function escapeCdata( $text ) {
		return str_replace( ']]>', ']]]]><![CDATA[>', (string) $text );
	}

	/**
	 * Ensure the channel description isn't trivially short (some validators require >= 50 chars).
	 *
	 * @param string $description
	 * @param string $title
	 * @param string $subtitle
	 * @param string $author
	 * @param string $feed_url
	 * @param string $site_url
	 * @return string
	 */
		private function ensureMinChannelDescription( $description, $title, $subtitle, $author, $feed_url, $site_url ) {
		$description = trim( (string) $description );

		$plain = wp_strip_all_tags( $description, true );
		$plain = html_entity_decode( (string) $plain, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$len   = function_exists( 'mb_strlen' ) ? (int) mb_strlen( $plain, 'UTF-8' ) : (int) strlen( $plain );

		if ( $len >= 50 ) {
			return $description;
		}

		$title    = trim( (string) $title );
		$subtitle = trim( (string) $subtitle );
		$author   = trim( (string) $author );
		$feed_url = trim( (string) $feed_url );
		$site_url = trim( (string) $site_url );

		$header = $title;
		if ( $subtitle !== '' ) {
			$header = $title !== '' ? ( $title . ' — ' . $subtitle ) : $subtitle;
		}

			$addon_parts = array();
			if ( $header !== '' ) {
				$addon_parts[] = $header . '.';
			}
			if ( $author !== '' ) {
				/* translators: %s: host/author name */
				$addon_parts[] = sprintf( __( 'Hosted by %s.', 'a-ripple-song' ), $author );
			}
			if ( $feed_url !== '' ) {
				/* translators: %s: podcast feed URL */
				$addon_parts[] = sprintf( __( 'Subscribe: %s', 'a-ripple-song' ), $feed_url );
			} elseif ( $site_url !== '' ) {
				/* translators: %s: site URL */
				$addon_parts[] = sprintf( __( 'Website: %s', 'a-ripple-song' ), $site_url );
			}

		$addon = trim( implode( ' ', $addon_parts ) );
		if ( $addon === '' ) {
			return $description;
		}

		return trim( $description !== '' ? ( $description . ' ' . $addon ) : $addon );
	}

	/**
	 * Render the podcast RSS feed.
	 */
	public function renderFeed() {
		if ( ! $this->isValidPodcastFeedUrl() ) {
			return;
		}

		header( 'Content-Type: application/rss+xml; charset=UTF-8' );
		status_header( 200 );
		nocache_headers();

		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';
		if ( strtoupper( $request_method ) === 'HEAD' ) {
			exit;
		}

		$site_url          = home_url( '/' );
		$feed_url          = $this->getCanonicalFeedUrl();

		$settings = PodcastSettings::getSettings();

		$channel_title       = (string) $settings['title'];
		$channel_subtitle    = (string) $settings['subtitle'];
		$channel_description = (string) $settings['description'];
		$channel_author      = (string) $settings['author'];
		$channel_owner_name  = (string) $settings['owner_name'];
		$channel_owner_email = (string) $settings['owner_email'];
		$channel_cover       = $this->encodeUrlPathForRss( (string) $settings['cover'] );
		$default_item_image  = $channel_cover;

		$channel_description = $this->ensureMinChannelDescription(
			(string) $channel_description,
			(string) $channel_title,
			(string) $channel_subtitle,
			(string) $channel_author,
			(string) $feed_url,
			(string) $site_url
		);

		if ( $default_item_image === '' ) {
			$site_icon = get_site_icon_url( 1400 );
			if ( is_string( $site_icon ) && $site_icon !== '' ) {
				$default_item_image = $this->encodeUrlPathForRss( $site_icon );
			}
		}

		$channel_explicit          = $this->normalizeItunesExplicit( (string) $settings['explicit'], 'false' );
		$channel_language_raw      = (string) $settings['language'];
		$channel_language          = $this->normalizeRssLanguageIso6391( $channel_language_raw, 'en' );
		$channel_category_primary   = (string) $settings['category_primary'];
		$channel_category_secondary = (string) $settings['category_secondary'];
		$channel_copyright          = (string) $settings['copyright'];
		$podcast_locked             = (string) $settings['locked'];
		$podcast_locked_owner       = (string) $settings['locked_owner'];
		$podcast_guid               = (string) $settings['guid'];
		$itunes_type                = (string) $settings['itunes_type'];
		$itunes_title               = (string) $settings['itunes_title'];
		$itunes_block               = (string) $settings['itunes_block'];
		$itunes_complete            = (string) $settings['itunes_complete'];
		$itunes_new_feed_url        = (string) $settings['itunes_new_feed_url'];
		$generator                  = (string) $settings['generator'];
		$apple_verify_code          = (string) $settings['apple_verify'];
		$podcast_funding            = (array) $settings['funding'];

		$query = new \WP_Query(
			array(
				'post_type'      => Episode::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$last_build_date = $this->formatRfc2822Gmt( time() );
		if ( ! empty( $query->posts ) ) {
			$latest_post_id = (int) $query->posts[0]->ID;
			$latest_ts      = (int) get_post_time( 'U', true, $latest_post_id, false );
			if ( $latest_ts > 0 ) {
				$last_build_date = $this->formatRfc2822Gmt( $latest_ts );
			}
		}

		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		?>
<rss version="2.0"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:podcast="https://podcastindex.org/namespace/1.0">
    <channel>
        <title><?php echo esc_html( $channel_title ); ?></title>
        <link><?php echo esc_url( $site_url ); ?></link>
	        <atom:link href="<?php echo esc_url( $feed_url ); ?>" rel="self" type="application/rss+xml" />
	        <language><?php echo esc_html( $channel_language ); ?></language>
	        <description><![CDATA[<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for CDATA by escape_cdata().
			echo $this->escapeCdata( (string) $channel_description );
			?>]]></description>
	        <itunes:summary><![CDATA[<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for CDATA by escape_cdata().
			echo $this->escapeCdata( (string) $channel_description );
			?>]]></itunes:summary>
        <itunes:subtitle><?php echo esc_html( (string) $channel_subtitle ); ?></itunes:subtitle>
        <itunes:author><?php echo esc_html( (string) $channel_author ); ?></itunes:author>
        <itunes:explicit><?php echo esc_html( (string) $channel_explicit ); ?></itunes:explicit>
        <lastBuildDate><?php echo esc_html( $last_build_date ); ?></lastBuildDate>
        <itunes:owner>
            <itunes:name><?php echo esc_html( (string) $channel_owner_name ); ?></itunes:name>
            <itunes:email><?php echo esc_html( (string) $channel_owner_email ); ?></itunes:email>
        </itunes:owner>
        <?php if ( $channel_cover ) : ?>
        <itunes:image href="<?php echo esc_url( $channel_cover ); ?>" />
        <?php endif; ?>
        <?php if ( $channel_category_primary ) : ?>
        <?php
			$primary_parts = explode( '::', (string) $channel_category_primary );
			?>
        <itunes:category text="<?php echo esc_attr( $primary_parts[0] ); ?>">
            <?php if ( isset( $primary_parts[1] ) ) : ?>
            <itunes:category text="<?php echo esc_attr( $primary_parts[1] ); ?>" />
            <?php endif; ?>
        </itunes:category>
        <?php endif; ?>
        <?php if ( $channel_category_secondary ) : ?>
        <?php
			$secondary_parts = explode( '::', (string) $channel_category_secondary );
			?>
        <itunes:category text="<?php echo esc_attr( $secondary_parts[0] ); ?>">
            <?php if ( isset( $secondary_parts[1] ) ) : ?>
            <itunes:category text="<?php echo esc_attr( $secondary_parts[1] ); ?>" />
            <?php endif; ?>
        </itunes:category>
        <?php endif; ?>
        <?php if ( $channel_copyright ) : ?>
        <copyright><?php echo esc_html( (string) $channel_copyright ); ?></copyright>
        <?php endif; ?>
        <?php if ( $itunes_type ) : ?>
        <itunes:type><?php echo esc_html( (string) $itunes_type ); ?></itunes:type>
        <?php endif; ?>
        <?php if ( $itunes_title ) : ?>
        <itunes:title><?php echo esc_html( (string) $itunes_title ); ?></itunes:title>
        <?php endif; ?>
        <?php if ( $itunes_block === 'yes' ) : ?>
        <itunes:block>yes</itunes:block>
        <?php endif; ?>
        <?php if ( $itunes_complete === 'yes' ) : ?>
        <itunes:complete>yes</itunes:complete>
        <?php endif; ?>
        <?php if ( $itunes_new_feed_url ) : ?>
        <itunes:new-feed-url><?php echo esc_url( (string) $itunes_new_feed_url ); ?></itunes:new-feed-url>
        <?php endif; ?>
        <?php if ( $podcast_locked === 'yes' ) : ?>
        <podcast:locked owner="<?php echo esc_attr( (string) $podcast_locked_owner ); ?>">yes</podcast:locked>
        <?php endif; ?>
        <?php if ( $podcast_guid ) : ?>
        <podcast:guid><?php echo esc_html( (string) $podcast_guid ); ?></podcast:guid>
        <?php endif; ?>
        <?php if ( $apple_verify_code ) : ?>
        <podcast:txt purpose="applepodcastsverify"><?php echo esc_html( (string) $apple_verify_code ); ?></podcast:txt>
        <?php endif; ?>
        <?php if ( $generator ) : ?>
        <generator><?php echo esc_html( (string) $generator ); ?></generator>
        <?php endif; ?>
        <?php if ( is_array( $podcast_funding ) && ! empty( $podcast_funding ) ) : ?>
            <?php foreach ( $podcast_funding as $fund ) : ?>
                <?php
					if ( ! is_array( $fund ) ) {
						continue;
					}
					$url   = isset( $fund['url'] ) ? trim( (string) $fund['url'] ) : '';
					$label = isset( $fund['label'] ) ? trim( (string) $fund['label'] ) : '';
					if ( $url === '' ) {
						continue;
					}
					?>
        <podcast:funding url="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></podcast:funding>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php
		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) :
				$query->the_post();
				$post_id = (int) get_the_ID();

				$audio_url       = $this->encodeUrlPathForRss( $this->resolveMediaUrl( $post_id, 'audio_file' ) );
				$audio_length    = (string) $this->getEpisodeMetaValue( $post_id, 'audio_length', '' );
				$audio_mime_raw  = (string) $this->getEpisodeMetaValue( $post_id, 'audio_mime', '' );
				$duration        = (int) $this->getEpisodeMetaValue( $post_id, 'duration', 0 );
				$duration_seconds = $duration > 0 ? (string) $duration : '';

				$episode_explicit_raw = (string) $this->getEpisodeMetaValue( $post_id, 'episode_explicit', '' );
				$episode_explicit     = $this->normalizeItunesExplicit( $episode_explicit_raw, (string) $channel_explicit );
				$episode_type     = (string) $this->getEpisodeMetaValue( $post_id, 'episode_type', '' );
				$episode_number   = (int) $this->getEpisodeMetaValue( $post_id, 'episode_number', 0 );
				$season_number    = (int) $this->getEpisodeMetaValue( $post_id, 'season_number', 0 );
				$episode_author   = (string) $this->getEpisodeMetaValue( $post_id, 'episode_author', '' );
				if ( $episode_author === '' ) {
					$episode_author = (string) $channel_author;
				}

				$episode_image = $this->encodeUrlPathForRss( $this->resolveMediaUrl( $post_id, 'episode_image' ) );
				if ( $episode_image === '' ) {
					$episode_image = $default_item_image;
				}

				$episode_transcript = $this->encodeUrlPathForRss( $this->resolveMediaUrl( $post_id, 'episode_transcript' ) );
				$episode_itunes_title = (string) $this->getEpisodeMetaValue( $post_id, 'itunes_title', '' );
				$episode_subtitle     = (string) $this->getEpisodeMetaValue( $post_id, 'episode_subtitle', '' );
				$episode_summary      = (string) $this->getEpisodeMetaValue( $post_id, 'episode_summary', '' );

				$episode_guid       = (string) $this->getEpisodeMetaValue( $post_id, 'episode_guid', '' );
				$episode_permalink  = (string) get_permalink( $post_id );
				if ( $episode_guid === '' ) {
					$episode_guid = $episode_permalink;
				}

				$episode_block = (string) $this->getEpisodeMetaValue( $post_id, 'episode_block', '' );
				$episode_people = $this->getEpisodePeople( $post_id );

				$transcript_url = $episode_transcript;
				$episode_chapters_url = $this->encodeUrlPathForRss( $this->resolveMediaUrl( $post_id, 'episode_chapters' ) );
				$episode_chapters_type = (string) ( $this->getEpisodeMetaValue( $post_id, 'episode_chapters_type', '' ) ?: 'application/json+chapters' );
				$episode_soundbites = $this->getEpisodeMetaValue( $post_id, 'episode_soundbites', array() );

				$item_summary = $episode_summary ? $episode_summary : get_the_excerpt();
				$item_summary = $this->sanitizeRssSummary( (string) $item_summary );

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter hook.
					$content_html = apply_filters( 'the_content', get_the_content( null, false, $post_id ) );
					$content_html = str_replace( ']]>', ']]]]><![CDATA[>', (string) $content_html );
				$pub_ts       = (int) get_post_time( 'U', true, $post_id, false );
				$pub_date     = $this->formatRfc2822Gmt( $pub_ts );
				$audio_mime   = $this->normalizeEnclosureMime( (string) $audio_url, (string) $audio_mime_raw );
				?>
        <item>
            <title><?php echo esc_html( get_the_title() ); ?></title>
            <link><?php echo esc_url( get_permalink() ); ?></link>
            <guid isPermaLink="<?php echo esc_attr( $episode_guid === $episode_permalink ? 'true' : 'false' ); ?>"><?php echo esc_html( $episode_guid ); ?></guid>
            <pubDate><?php echo esc_html( $pub_date ); ?></pubDate>
	            <description><![CDATA[<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for CDATA by escape_cdata().
					echo $this->escapeCdata( (string) $item_summary );
					?>]]></description>
	            <itunes:summary><![CDATA[<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped for CDATA by escape_cdata().
					echo $this->escapeCdata( (string) $item_summary );
					?>]]></itunes:summary>
	            <?php if ( $content_html ) : ?>
	            <content:encoded><![CDATA[<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML content is sanitized by core filters; wrapped in CDATA.
					echo $this->escapeCdata( (string) $content_html );
					?>]]></content:encoded>
	            <?php endif; ?>
            <?php if ( $audio_url ) : ?>
            <enclosure url="<?php echo esc_url( $audio_url ); ?>" length="<?php echo esc_attr( $audio_length ); ?>" type="<?php echo esc_attr( $audio_mime ); ?>" />
            <?php endif; ?>
            <?php if ( $duration_seconds ) : ?>
            <itunes:duration><?php echo esc_html( $duration_seconds ); ?></itunes:duration>
            <?php endif; ?>
            <itunes:explicit><?php echo esc_html( $episode_explicit ); ?></itunes:explicit>
            <itunes:author><?php echo esc_html( $episode_author ); ?></itunes:author>
            <?php if ( $episode_itunes_title !== '' ) : ?>
            <itunes:title><?php echo esc_html( (string) $episode_itunes_title ); ?></itunes:title>
            <?php endif; ?>
            <?php if ( $episode_subtitle ) : ?>
            <itunes:subtitle><?php echo esc_html( (string) $episode_subtitle ); ?></itunes:subtitle>
            <?php endif; ?>
            <?php if ( $episode_image ) : ?>
            <itunes:image href="<?php echo esc_url( $episode_image ); ?>" />
            <?php endif; ?>
	            <?php if ( ! empty( $episode_people ) ) : ?>
	                <?php foreach ( $episode_people as $person ) : ?>
	            <podcast:person<?php
						if ( ! empty( $person['role'] ) ) {
							echo ' role="' . esc_attr( $person['role'] ) . '"';
						}
						if ( ! empty( $person['href'] ) ) {
							echo ' href="' . esc_url( $person['href'] ) . '"';
						}
						if ( ! empty( $person['img'] ) ) {
							echo ' img="' . esc_url( $person['img'] ) . '"';
						}
						?>><?php echo esc_html( $person['name'] ); ?></podcast:person>
	                <?php endforeach; ?>
	            <?php endif; ?>
            <?php if ( $transcript_url ) : ?>
            <podcast:transcript url="<?php echo esc_url( $transcript_url ); ?>" type="<?php echo esc_attr( $this->guessTranscriptType( $transcript_url ) ); ?>" />
            <?php endif; ?>
            <?php if ( $episode_chapters_url && ( preg_match( '~^https://~i', $episode_chapters_url ) || preg_match( '~^https?://localhost(?::\\d+)?/~i', $episode_chapters_url ) ) ) : ?>
            <podcast:chapters url="<?php echo esc_url( $episode_chapters_url ); ?>" type="<?php echo esc_attr( (string) $episode_chapters_type ); ?>" />
            <?php endif; ?>
            <?php
				if ( is_array( $episode_soundbites ) && ! empty( $episode_soundbites ) ) :
					foreach ( $episode_soundbites as $soundbite ) :
						if ( ! is_array( $soundbite ) ) {
							continue;
						}
						$start    = isset( $soundbite['start_time'] ) ? (float) $soundbite['start_time'] : null;
						$dur      = isset( $soundbite['duration'] ) ? (float) $soundbite['duration'] : null;
						if ( null === $start || null === $dur || $start < 0 || $dur <= 0 ) {
							continue;
						}
						$sb_title = trim( (string) ( $soundbite['title'] ?? '' ) );
						?>
            <?php if ( $sb_title !== '' ) : ?>
            <podcast:soundbite startTime="<?php echo esc_attr( $start ); ?>" duration="<?php echo esc_attr( $dur ); ?>"><?php echo esc_html( $sb_title ); ?></podcast:soundbite>
            <?php else : ?>
            <podcast:soundbite startTime="<?php echo esc_attr( $start ); ?>" duration="<?php echo esc_attr( $dur ); ?>" />
            <?php endif; ?>
            <?php
					endforeach;
				endif;
				?>
            <?php if ( $episode_number > 0 ) : ?>
            <itunes:episode><?php echo esc_html( (int) $episode_number ); ?></itunes:episode>
            <?php endif; ?>
            <?php if ( $season_number > 0 ) : ?>
            <itunes:season><?php echo esc_html( (int) $season_number ); ?></itunes:season>
            <?php endif; ?>
            <itunes:episodeType><?php echo esc_html( $episode_type ? $episode_type : 'full' ); ?></itunes:episodeType>
            <?php if ( $episode_block === 'yes' ) : ?>
            <itunes:block>yes</itunes:block>
            <?php endif; ?>
        </item>
				<?php
			endwhile;
			wp_reset_postdata();
		endif;
		?>
    </channel>
</rss>
		<?php
		exit;
	}
}
