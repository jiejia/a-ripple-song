<?php

namespace ARippleSong\PostTypes;

use ARippleSong\Core\LegacyMeta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Podcast Episodes (CPT) and taxonomy registration.
 *
 * @package    ARippleSong
 * @subpackage ARippleSong/includes
 */
class Episode {

	/**
	 * Custom post type key.
	 */
	public const POST_TYPE = 'ars_episode';

	/**
	 * Taxonomy key.
	 */
	public const TAXONOMY = 'ars_episode_category';

	/**
	 * Enable featured image support for the episode post type from the plugin.
	 *
	 * WordPress requires thumbnail theme support in addition to CPT support, so
	 * the plugin registers it here for its own post type only.
	 *
	 * @return void
	 */
	public function enableThumbnailThemeSupport() {
		add_theme_support( 'post-thumbnails', array( self::POST_TYPE ) );
	}

	/**
	 * Register custom post type.
	 */
	public function registerPostType() {
		$this->maybeMigratePostTypeKey();

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => array(
					'name'                  => __( 'Episodes', 'a-ripple-song' ),
					'singular_name'         => __( 'Episode', 'a-ripple-song' ),
					'add_new'               => __( 'Add New Episode', 'a-ripple-song' ),
					'add_new_item'          => __( 'Add New Episode', 'a-ripple-song' ),
					'edit_item'             => __( 'Edit Episode', 'a-ripple-song' ),
					'new_item'              => __( 'New Episode', 'a-ripple-song' ),
					'view_item'             => __( 'View Episode', 'a-ripple-song' ),
					'view_items'            => __( 'View Episodes', 'a-ripple-song' ),
					'search_items'          => __( 'Search Episodes', 'a-ripple-song' ),
					'not_found'             => __( 'No episodes found', 'a-ripple-song' ),
					'not_found_in_trash'    => __( 'No episodes found in Trash', 'a-ripple-song' ),
					'all_items'             => __( 'All Episodes', 'a-ripple-song' ),
					'menu_name'             => __( 'ARS Episodes', 'a-ripple-song' ),
					'name_admin_bar'        => __( 'Episode', 'a-ripple-song' ),
					'item_published'        => __( 'Episode published.', 'a-ripple-song' ),
					'item_updated'          => __( 'Episode updated.', 'a-ripple-song' ),
					'item_reverted_to_draft' => __( 'Episode reverted to draft.', 'a-ripple-song' ),
					'item_scheduled'        => __( 'Episode scheduled.', 'a-ripple-song' ),
				),
				'public'             => true,
				'has_archive'        => true,
				'menu_icon'          => 'dashicons-microphone',
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'trackbacks' ),
				'taxonomies'         => array( 'post_tag' ),
				'show_in_rest'       => true,
				'show_in_nav_menus'  => true,
				'rewrite'            => array( 'slug' => 'podcasts' ),
				'menu_position'      => 5,
			)
		);

		$this->maybeFlushRewriteRulesAfterPostTypeRename();
	}

	/**
	 * Register metric meta fields for views and plays.
	 *
	 * Views are available on every public post type, while play counts are
	 * limited to podcast episodes.
	 *
	 * @return void
	 */
	public function registerMetricMetaFields() {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'names'
		);

		foreach ( $post_types as $post_type ) {
			register_post_meta(
				$post_type,
				'_views_count',
				array(
					'type'         => 'integer',
					'single'       => true,
					'default'      => 0,
					'show_in_rest' => false,
					'auth_callback' => static function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}

		register_post_meta(
			self::POST_TYPE,
			'_play_count',
			array(
				'type'         => 'integer',
				'single'       => true,
				'default'      => 0,
				'show_in_rest' => false,
				'auth_callback' => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}

	/**
	 * One-time migration from the old CPT key (`ars_episodes`) to the new key (`ars_episode`).
	 */
	private function maybeMigratePostTypeKey() {
		$flag = 'ars_podcast_migrated_v2_post_type_key';
		if ( get_option( $flag ) ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time post type key migration.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s",
				self::POST_TYPE,
				'ars_episodes'
			)
		);

		update_option( $flag, '1', 'no' );
	}

	/**
	 * Flush rewrite rules once after the CPT key rename so existing sites pick up new rules.
	 */
	private function maybeFlushRewriteRulesAfterPostTypeRename() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$flag = 'ars_podcast_rewrite_flushed_v2_post_type_key';
		if ( get_option( $flag ) ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( $flag, '1', 'no' );
	}

	/**
	 * Ensure metric defaults exist after a post is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param \WP_Post $post   Saved post object.
	 * @return void
	 */
	public function ensureMetricDefaults( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! metadata_exists( 'post', $post_id, '_views_count' ) ) {
			update_post_meta( $post_id, '_views_count', 0 );
		}

		if ( $post->post_type === self::POST_TYPE && ! metadata_exists( 'post', $post_id, '_play_count' ) ) {
			update_post_meta( $post_id, '_play_count', 0 );
		}
	}

	/**
	 * Default comment status to open for new episodes.
	 *
	 * @param array $data
	 * @param array $postarr
	 * @return array
	 */
	public function setDefaultCommentStatus( $data, $postarr ) {
		if ( isset( $data['post_type'] ) && $data['post_type'] === self::POST_TYPE && empty( $postarr['ID'] ) ) {
			$data['comment_status'] = 'open';
			$data['ping_status']    = 'open';
		}

		return $data;
	}
}

/**
 * Episode save hooks (auto-fill audio meta, defaults, admin notices).
 *
 * @package    ARippleSong
 * @subpackage ARippleSong/includes
 */
class EpisodeSave {

	/**
	 * Meta key used for storing last audio meta extraction error.
	 */
	private const AUDIO_META_LAST_ERROR_KEY = '_podcast_audio_meta_last_error';

	/**
	 * Persist the last audio meta error in a structured way so we can translate it on display.
	 *
	 * @param int    $post_id
	 * @param string $code
	 * @param string $detail
	 */
	private function setAudioMetaLastError( $post_id, $code, $detail = '' ) {
		update_post_meta(
			$post_id,
			self::AUDIO_META_LAST_ERROR_KEY,
			array(
				'code'   => (string) $code,
				'detail' => (string) $detail,
			)
		);
	}

	/**
	 * Clear the last audio meta error.
	 *
	 * @param int $post_id
	 */
	private function clearAudioMetaLastError( $post_id ) {
		delete_post_meta( $post_id, self::AUDIO_META_LAST_ERROR_KEY );
	}

	/**
	 * Post meta container saved callback.
	 *
	 * @param int   $post_id
	 * @param mixed $container
	 */
	public function onPostMetaSaved( $post_id, $container ) {
		if ( get_post_type( $post_id ) !== Episode::POST_TYPE ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$this->autoFillAudioMeta( $post_id );
	}

	/**
	 * Auto calculate audio meta after save.
	 */
	private function autoFillAudioMeta( $post_id ) {
		$auto_meta = $this->calculateAudioMeta( $post_id );

		if ( ! empty( $auto_meta['duration'] ) ) {
			$this->setEpisodeFieldValue( $post_id, 'duration', (int) $auto_meta['duration'] );
		}

		if ( ! empty( $auto_meta['length'] ) ) {
			$this->setEpisodeFieldValue( $post_id, 'audio_length', (int) $auto_meta['length'] );
		}

		if ( ! empty( $auto_meta['mime'] ) ) {
			$this->setEpisodeFieldValue( $post_id, 'audio_mime', (string) $auto_meta['mime'] );
		}
	}

	/**
	 * Persist an Episode Details field using native post meta.
	 *
	 * @param int         $post_id
	 * @param string      $key
	 * @param string|int  $value
	 * @return void
	 */
	private function setEpisodeFieldValue( $post_id, $key, $value ) {
		update_post_meta( $post_id, '_' . ltrim( (string) $key, '_' ), $value );
	}

	/**
	 * Calculate podcast audio metadata (duration, length, mime) via getID3.
	 *
	 * @param int    $post_id
	 * @param string $audio_url
	 * @return array{duration:int|null,length:int|null,mime:string|null}
	 */
	private function calculateAudioMeta( $post_id, $audio_url = '' ) {
		$result = array(
			'duration' => null,
			'length'   => null,
			'mime'     => null,
		);

		if ( $audio_url === '' ) {
			$audio_url = $this->getEpisodeFieldValue( $post_id, 'audio_file' );
		}

		if ( $audio_url === '' ) {
			return $result;
		}

		$last_error_code   = null;
		$last_error_detail = '';

		if ( ! class_exists( 'getID3' ) ) {
			$maybe = ABSPATH . WPINC . '/ID3/getid3.php';
			if ( file_exists( $maybe ) ) {
				require_once $maybe;
			}
		}

			if ( ! class_exists( 'getID3' ) ) {
				$last_error_code = 'getid3_missing';
				$this->setAudioMetaLastError( $post_id, $last_error_code );
				return $result;
			}

		$upload_dir = wp_get_upload_dir();
		$file_path  = $audio_url;

		if ( filter_var( $audio_url, FILTER_VALIDATE_URL ) ) {
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $audio_url );

			if ( ! file_exists( $file_path ) ) {
				$audio_path       = (string) wp_parse_url( $audio_url, PHP_URL_PATH );
				$audio_path       = $audio_path !== '' ? rawurldecode( $audio_path ) : '';
				$uploads_url_path = (string) wp_parse_url( $upload_dir['baseurl'], PHP_URL_PATH );

				if ( $audio_path !== '' && $uploads_url_path !== '' && strpos( $audio_path, $uploads_url_path ) === 0 ) {
					$relative  = ltrim( substr( $audio_path, strlen( $uploads_url_path ) ), '/' );
					$file_path = trailingslashit( $upload_dir['basedir'] ) . $relative;
				}
			}
		}

		if ( ! file_exists( $file_path ) ) {
			$uploads_basedir      = isset( $upload_dir['basedir'] ) ? (string) $upload_dir['basedir'] : '';
			$uploads_basedir_real = $uploads_basedir !== '' ? realpath( $uploads_basedir ) : false;
			$uploads_basedir_real = $uploads_basedir_real !== false ? wp_normalize_path( $uploads_basedir_real ) : false;

			$url_path = '';

			if ( filter_var( $audio_url, FILTER_VALIDATE_URL ) ) {
				$parsed_url = wp_parse_url( $audio_url );
				$url_path   = isset( $parsed_url['path'] ) ? (string) $parsed_url['path'] : '';
			} elseif ( is_string( $audio_url ) && strpos( $audio_url, '/' ) === 0 ) {
				$url_path = $audio_url;
			}

			if ( $uploads_basedir_real && $url_path !== '' ) {
				$candidate      = ABSPATH . ltrim( rawurldecode( $url_path ), '/' );
				$candidate_real = realpath( $candidate );
				if ( $candidate_real !== false ) {
					$candidate_real = wp_normalize_path( $candidate_real );
					if ( strpos( $candidate_real, $uploads_basedir_real . '/' ) === 0 || $candidate_real === $uploads_basedir_real ) {
						$file_path = $candidate_real;
					}
				}
			}
		}

		if ( ! file_exists( $file_path ) ) {
			if ( $this->isValidHttpUrl( $audio_url ) ) {
				$request_url = $this->encodeUrlForRequest( $audio_url );

					if ( function_exists( 'wp_http_validate_url' ) && ! wp_http_validate_url( $request_url ) ) {
						$last_error_code = 'audio_url_rejected';
						$this->setAudioMetaLastError( $post_id, $last_error_code );
						return $result;
					}

				if ( ! function_exists( 'download_url' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

					$timeout = (int) apply_filters( 'a_ripple_song_podcast_episode_audio_meta_download_timeout', 300, $audio_url, $post_id );
					// Backward compatibility for the original hook name.
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
					$timeout = (int) apply_filters( 'ars_episode_audio_meta_download_timeout', $timeout, $audio_url, $post_id );
					if ( $timeout < 30 ) {
						$timeout = 30;
					}

					$tmp = download_url( $request_url, $timeout );
					if ( is_wp_error( $tmp ) ) {
						$last_error_code   = 'audio_download_failed';
						$last_error_detail = (string) $tmp->get_error_message();
						$this->setAudioMetaLastError( $post_id, $last_error_code, $last_error_detail );
						return $result;
					}

				try {
					$getID3    = new \getID3();
					$file_info = $getID3->analyze( $tmp );

					if ( isset( $file_info['playtime_seconds'] ) ) {
						$result['duration'] = (int) round( $file_info['playtime_seconds'] );
					} else {
						$last_error_code = 'getid3_no_playtime_download';
					}

					$tmp_size = @filesize( $tmp );
					if ( $tmp_size !== false ) {
						$result['length'] = (int) $tmp_size;
					}

					if ( ! empty( $file_info['mime_type'] ) ) {
						$result['mime'] = (string) $file_info['mime_type'];
					}
					} catch ( \Exception $e ) {
						$last_error_code   = 'getid3_error';
						$last_error_detail = (string) $e->getMessage();
					} finally {
						wp_delete_file( $tmp );
					}

				if ( $last_error_code ) {
					$this->setAudioMetaLastError( $post_id, $last_error_code, $last_error_detail );
				} elseif ( ! empty( $result['duration'] ) ) {
					$this->clearAudioMetaLastError( $post_id );
				}

				$result['mime'] = $this->normalizeAudioMimeForUrl( $result['mime'], $audio_url );
				return $result;
			}

				$last_error_code = 'audio_file_missing';
				$this->setAudioMetaLastError( $post_id, $last_error_code );
				return $result;
			}

		try {
			$getID3    = new \getID3();
			$file_info = $getID3->analyze( $file_path );

			if ( isset( $file_info['playtime_seconds'] ) ) {
				$result['duration'] = (int) round( $file_info['playtime_seconds'] );
			} else {
				$last_error_code = 'getid3_no_playtime_local';
			}

			if ( ! empty( $file_info['filesize'] ) ) {
				$result['length'] = (int) $file_info['filesize'];
			}

			if ( ! empty( $file_info['mime_type'] ) ) {
				$result['mime'] = (string) $file_info['mime_type'];
			}
			} catch ( \Exception $e ) {
				$last_error_code   = 'getid3_error';
				$last_error_detail = (string) $e->getMessage();
			}

		if ( $last_error_code ) {
			$this->setAudioMetaLastError( $post_id, $last_error_code, $last_error_detail );
		} elseif ( ! empty( $result['duration'] ) ) {
			$this->clearAudioMetaLastError( $post_id );
		}

		$result['mime'] = $this->normalizeAudioMimeForUrl( $result['mime'], $audio_url, $file_path );
		return $result;
	}

	/**
	 * Normalize detected MIME type based on file extension (notably .m4a).
	 *
	 * Some validators expect .m4a to be declared as audio/x-m4a.
	 *
	 * @param string|null $mime
	 * @param string      $audio_url
	 * @param string      $file_path
	 * @return string|null
	 */
	private function normalizeAudioMimeForUrl( $mime, $audio_url, $file_path = '' ) {
		$mime      = is_string( $mime ) ? trim( $mime ) : '';
		$audio_url = (string) $audio_url;
		$file_path = (string) $file_path;

		$path = (string) wp_parse_url( $audio_url, PHP_URL_PATH );
		if ( $path === '' ) {
			$path = $audio_url;
		}

		$ext = strtolower( (string) pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( $ext === '' && $file_path !== '' ) {
			$ext = strtolower( (string) pathinfo( $file_path, PATHINFO_EXTENSION ) );
		}

		if ( $ext === 'm4a' ) {
			return 'audio/x-m4a';
		}

		return $mime !== '' ? $mime : null;
	}

	/**
	 * Read an Episode Details field from native post meta.
	 *
	 * @param int    $post_id
	 * @param string $key
	 * @return string
	 */
	private function getEpisodeFieldValue( $post_id, $key ) {
		$value = LegacyMeta::getPostMetaValue( $post_id, $key, '' );
		if ( is_string( $value ) ) {
			return $value;
		}
		if ( is_numeric( $value ) ) {
			return (string) $value;
		}

		return '';
	}

	/**
	 * Show last audio meta extraction error on the editor screen.
	 */
	public function showAudioMetaErrorNotice() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== Episode::POST_TYPE ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading current post ID to show an admin notice.
			$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
			if ( $post_id <= 0 ) {
				return;
			}

		$last_error = get_post_meta( $post_id, self::AUDIO_META_LAST_ERROR_KEY, true );
		if ( empty( $last_error ) ) {
			return;
		}

		$message = '';

		if ( is_array( $last_error ) && ! empty( $last_error['code'] ) ) {
			$code   = (string) $last_error['code'];
			$detail = isset( $last_error['detail'] ) ? (string) $last_error['detail'] : '';

				switch ( $code ) {
					case 'getid3_missing':
						/* translators: %d: episode post ID */
						$message = sprintf( __( 'Episode #%d: getID3 not available', 'a-ripple-song' ), $post_id );
						break;
					case 'audio_url_rejected':
						/* translators: %d: episode post ID */
						$message = sprintf( __( 'Episode #%d: audio URL rejected by wp_http_validate_url', 'a-ripple-song' ), $post_id );
						break;
					case 'audio_download_failed':
						/* translators: 1: episode post ID, 2: error message */
						$message = sprintf( __( 'Episode #%1$d: audio download failed - %2$s', 'a-ripple-song' ), $post_id, $detail );
						break;
					case 'getid3_no_playtime_download':
						/* translators: %d: episode post ID */
						$message = sprintf( __( 'Episode #%d: getID3 did not return playtime_seconds for downloaded audio', 'a-ripple-song' ), $post_id );
						break;
					case 'getid3_no_playtime_local':
						/* translators: %d: episode post ID */
						$message = sprintf( __( 'Episode #%d: getID3 did not return playtime_seconds for local file', 'a-ripple-song' ), $post_id );
						break;
					case 'audio_file_missing':
						/* translators: %d: episode post ID */
						$message = sprintf( __( 'Episode #%d: audio file missing for duration/size/mime detection', 'a-ripple-song' ), $post_id );
						break;
					case 'getid3_error':
						/* translators: 1: episode post ID, 2: error message */
						$message = sprintf( __( 'Episode #%1$d: getID3 error - %2$s', 'a-ripple-song' ), $post_id, $detail );
						break;
					default:
						$message = $detail;
						break;
			}
		} else {
			// Backward compatibility: previously stored as a plain string.
			$message = (string) $last_error;
		}

		if ( $message === '' ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' . esc_html( $message ) . '</p></div>';
	}

	private function isValidHttpUrl( $url ) {
		$url = trim( (string) $url );
		if ( $url === '' ) {
			return false;
		}

		$encoded = $this->encodeUrlForRequest( $url );

		if ( function_exists( 'wp_http_validate_url' ) ) {
			return (bool) wp_http_validate_url( $encoded );
		}

		$parts = wp_parse_url( $encoded );
		if ( ! is_array( $parts ) ) {
			return false;
		}

		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
		return in_array( $scheme, array( 'http', 'https' ), true ) && ! empty( $parts['host'] );
	}

	private function encodeUrlForRequest( $url ) {
			$parts = wp_parse_url( $url );
			if ( $parts === false || ! is_array( $parts ) ) {
				return $url;
			}

		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';
		$host   = isset( $parts['host'] ) ? (string) $parts['host'] : '';
		if ( $scheme === '' || $host === '' ) {
			return $url;
		}

		$user = isset( $parts['user'] ) ? (string) $parts['user'] : '';
		$pass = isset( $parts['pass'] ) ? (string) $parts['pass'] : '';
		$auth = '';
		if ( $user !== '' ) {
			$auth = $user;
			if ( $pass !== '' ) {
				$auth .= ':' . $pass;
			}
			$auth .= '@';
		}

		$port = isset( $parts['port'] ) ? ':' . (int) $parts['port'] : '';

		$path = isset( $parts['path'] ) ? (string) $parts['path'] : '';
		if ( $path !== '' ) {
			$segments = explode( '/', $path );
			$segments = array_map(
				static function ( $segment ) {
					return rawurlencode( rawurldecode( (string) $segment ) );
				},
				$segments
			);
			$path = implode( '/', $segments );
		}

		$query    = isset( $parts['query'] ) && $parts['query'] !== '' ? '?' . (string) $parts['query'] : '';
		$fragment = isset( $parts['fragment'] ) && $parts['fragment'] !== '' ? '#' . (string) $parts['fragment'] : '';

		return $scheme . '://' . $auth . $host . $port . $path . $query . $fragment;
	}
}

/**
 * REST API integration (expose episode meta).
 *
 * WordPress REST endpoints only include custom fields under `meta` when those
 * meta keys are registered with `show_in_rest`.
 *
 * @package    ARippleSong
 * @subpackage ARippleSong/includes
 */
class EpisodeRest {

	/**
	 * Register Episode Details meta keys for REST output.
	 */
	public function registerEpisodeMeta() {
		$post_type = Episode::POST_TYPE;

			$this->registerStringMeta( $post_type, '_audio_file', true, true );
		$this->registerIntMeta( $post_type, '_duration' );
		$this->registerIntMeta( $post_type, '_audio_length' );
		$this->registerStringMeta( $post_type, '_audio_mime' );

		$this->registerStringMeta( $post_type, '_episode_explicit' );
		$this->registerStringMeta( $post_type, '_episode_type' );
		$this->registerIntMeta( $post_type, '_episode_number' );
		$this->registerIntMeta( $post_type, '_season_number' );

		$this->registerStringMeta( $post_type, '_episode_author' );
		$this->registerStringMeta( $post_type, '_episode_image', true, true );
		$this->registerStringMeta( $post_type, '_episode_transcript', true, true );
		$this->registerStringMeta( $post_type, '_itunes_title' );
		$this->registerStringMeta( $post_type, '_episode_chapters', true, true );
		$this->registerStringMeta( $post_type, '_episode_chapters_type' );

			$this->registerStringMeta( $post_type, '_episode_subtitle' );
			$this->registerStringMeta( $post_type, '_episode_summary' );
			$this->registerStringMeta( $post_type, '_episode_guid' );
			$this->registerStringMeta( $post_type, '_episode_block' );

			$this->registerArrayMeta(
				$post_type,
				'_members',
				array(
					'type' => 'integer',
				)
			);
			$this->registerArrayMeta(
				$post_type,
				'_guests',
				array(
					'type' => 'integer',
				)
			);
			$this->registerArrayMeta(
				$post_type,
				'_episode_soundbites',
				array(
					'type'       => 'object',
					'properties' => array(
						'start_time' => array( 'type' => 'number' ),
						'duration'   => array( 'type' => 'number' ),
						'title'      => array( 'type' => 'string' ),
					),
				)
			);
		}

	/**
	 * Expose selected Episode Details fields as top-level REST fields (theme parity).
	 *
	 * @return void
	 */
	public function registerEpisodeRestFields() {
		$post_type = Episode::POST_TYPE;

		register_rest_field(
			$post_type,
			'title_text',
			array(
				'get_callback' => static function ( $post, $field_name, $request ) {
					$post_id = 0;
					if ( is_array( $post ) ) {
						if ( isset( $post['id'] ) ) {
							$post_id = (int) $post['id'];
						} elseif ( isset( $post['ID'] ) ) {
							$post_id = (int) $post['ID'];
						}
					} elseif ( is_object( $post ) && isset( $post->ID ) ) {
						$post_id = (int) $post->ID;
					}

					$title = '';
					if ( is_array( $post ) && isset( $post['title']['rendered'] ) ) {
						$title = (string) $post['title']['rendered'];
						$title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
					} elseif ( $post_id > 0 ) {
						$title = (string) get_post_field( 'post_title', $post_id );
					}

					return wp_strip_all_tags( $title, true );
				},
				'schema'       => array(
					'description' => __( 'Episode title (plain text)', 'a-ripple-song' ),
					'type'        => 'string',
				),
			)
		);

		register_rest_field(
			$post_type,
			'audio_file',
			array(
				'get_callback' => static function ( $post, $field_name, $request ) {
					return (string) EpisodeRest::getEpisodeValue( (int) $post['id'], 'audio_file', '' );
				},
				'schema'       => array(
					'description' => __( 'Audio file URL', 'a-ripple-song' ),
					'type'        => 'string',
				),
			)
		);

		register_rest_field(
			$post_type,
			'duration',
			array(
				'get_callback' => static function ( $post, $field_name, $request ) {
					return (int) EpisodeRest::getEpisodeValue( (int) $post['id'], 'duration', 0 );
				},
				'schema'       => array(
					'description' => __( 'Audio duration (seconds)', 'a-ripple-song' ),
					'type'        => 'integer',
				),
			)
		);

		register_rest_field(
			$post_type,
			'episode_transcript',
			array(
				'get_callback' => static function ( $post, $field_name, $request ) {
					return (string) EpisodeRest::getEpisodeValue( (int) $post['id'], 'episode_transcript', '' );
				},
				'schema'       => array(
					'description' => __( 'Episode transcript URL', 'a-ripple-song' ),
					'type'        => 'string',
				),
			)
		);
	}

	/**
	 * Read an episode field from native post meta.
	 *
	 * @param int         $post_id
	 * @param string      $key
	 * @param string|int  $default
	 * @return string|int
	 */
	private static function getEpisodeValue( $post_id, $key, $default ) {
		return LegacyMeta::getPostMetaValue( $post_id, $key, $default );
	}

	private function registerStringMeta( $post_type, $key, $is_url = false, $public_read = false ) {
		$args = array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => $is_url ? 'esc_url_raw' : 'sanitize_text_field',
			'auth_callback'     => '__return_true',
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => 'string',
					'default' => '',
					'context' => array( 'view', 'edit' ),
					'format'  => $is_url ? 'uri' : null,
				),
			),
		);

		// Remove null format key.
		if ( empty( $args['show_in_rest']['schema']['format'] ) ) {
			unset( $args['show_in_rest']['schema']['format'] );
		}

		register_post_meta( $post_type, $key, $args );
	}

	private function registerIntMeta( $post_type, $key ) {
		register_post_meta(
			$post_type,
			$key,
			array(
				'type'              => 'integer',
				'single'            => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => '__return_true',
				'show_in_rest'      => array(
					'schema' => array(
						'type'    => 'integer',
						'default' => 0,
						'context' => array( 'view', 'edit' ),
					),
				),
			)
		);
	}

	private function registerArrayMeta( $post_type, $key, $items_schema ) {
		register_post_meta(
			$post_type,
			$key,
			array(
				'type'          => 'array',
				'single'        => true,
				'auth_callback' => '__return_true',
				'show_in_rest'  => array(
					'schema' => array(
						'type'    => 'array',
						'default' => array(),
						'context' => array( 'view', 'edit' ),
						'items'   => $items_schema,
					),
				),
			)
		);
	}
}

/**
 * Admin upload MIME support for podcast-related media.
 *
 * Ported from the previous theme implementation.
 *
 * @package    ARippleSong
 * @subpackage ARippleSong/admin
 */
class EpisodeMedia {

	/**
	 * Allow additional audio file types to be uploaded.
	 *
	 * @param array $mimes Existing allowed mime types.
	 * @return array
	 */
	public function allowUploadMimes( $mimes ) {
		// Audio files.
		$mimes['mp3'] = 'audio/mpeg';
		$mimes['m4a'] = 'audio/x-m4a';

		return $mimes;
	}

	/**
	 * Fix file type detection for custom mime types.
	 *
	 * WordPress performs additional security checks on file uploads that can
	 * incorrectly reject valid files. This filter ensures our allowed types pass validation.
	 *
	 * @param array  $data File data array containing 'ext', 'type', 'proper_filename'.
	 * @param string $file Full path to the file.
	 * @param string $filename The name of the file.
	 * @param array  $mimes Array of mime types keyed by their file extension.
	 * @return array
	 */
	public function fixFiletypeAndExt( $data, $file, $filename, $mimes ) {
		$ext = strtolower( (string) pathinfo( (string) $filename, PATHINFO_EXTENSION ) );

		$custom_mimes = array(
			'mp3' => 'audio/mpeg',
			'm4a' => 'audio/x-m4a',
		);

		if ( isset( $custom_mimes[ $ext ] ) && ( empty( $data['type'] ) || empty( $data['ext'] ) ) ) {
			$data['ext']  = $ext;
			$data['type'] = $custom_mimes[ $ext ];
		}

		return $data;
	}
}
