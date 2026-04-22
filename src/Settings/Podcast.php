<?php

namespace ARippleSong\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Podcast Settings page (Carbon Fields theme options).
 *
 * @package    ARippleSong
 * @subpackage ARippleSong/includes
 */
class Podcast {

	/**
	 * Settings landing page slug (Carbon Fields page file).
	 */
	private const SETTINGS_PAGE_FILE = 'ars_settings.php';

	/**
	 * Main settings page slug (Carbon Fields page file).
	 */
	private const PODCAST_SETTINGS_PAGE_FILE = 'ars_podcast_settings.php';

	/**
	 * ID for the top-level Carbon Fields menu container.
	 */
	private $menu_container = null;

	/**
	 * Legacy compatibility hook for the old Carbon Fields settings page.
	 */
	public function registerMenuContainer() {
		return null;
	}

	/**
	 * Legacy compatibility hook for the old Carbon Fields settings page.
	 */
	public function registerFields() {
		return;
	}

	/**
	 * Hide the empty landing page submenu item for the top-level menu.
	 */
	public function removeLandingSubmenuItem() {
		remove_submenu_page( self::SETTINGS_PAGE_FILE, self::SETTINGS_PAGE_FILE );
	}

	/**
	 * Redirect the top-level menu landing page to the first real settings page.
	 */
	public function maybeRedirectSettingsLandingPage() {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading admin page slug for internal redirect.
		$input = wp_unslash( $_GET );
		$page  = isset( $input['page'] ) ? sanitize_key( (string) $input['page'] ) : '';

		if ( $page !== self::SETTINGS_PAGE_FILE ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PODCAST_SETTINGS_PAGE_FILE ) );
		exit;
	}

	/**
	 * Ensure Carbon Fields "image" fields using value_type=url can still preview external URLs.
	 *
	 * @param array      $attachment_metadata
	 * @param int|string $id
	 * @param string     $type
	 * @return array
	 */
	public function previewExternalCoverUrl( $attachment_metadata, $id, $type ) {
		if ( $type !== 'url' ) {
			return $attachment_metadata;
		}

		$url = isset( $attachment_metadata['thumb_url'] ) ? $attachment_metadata['thumb_url'] : '';
		if ( ! is_string( $url ) || $url === '' ) {
			return $attachment_metadata;
		}

		if ( ! preg_match( '~^https?://~i', $url ) ) {
			return $attachment_metadata;
		}

		$path     = wp_parse_url( $url, PHP_URL_PATH );
		$fileName = is_string( $path ) && $path !== '' ? wp_basename( $path ) : wp_basename( $url );

		$fileTypeInfo = wp_check_filetype( $fileName );
		$mime         = isset( $fileTypeInfo['type'] ) ? $fileTypeInfo['type'] : '';

		$attachment_metadata['id']        = -1;
		$attachment_metadata['file_url']  = $url;
		$attachment_metadata['thumb_url'] = $url;
		$attachment_metadata['file_name'] = $fileName;

		if ( is_string( $mime ) && strpos( $mime, 'image/' ) === 0 ) {
			$attachment_metadata['filetype']  = $fileTypeInfo;
			$attachment_metadata['file_type'] = 'image';
		}

		return $attachment_metadata;
	}

	/**
	 * Validate podcast cover field value before saving.
	 *
	 * @param mixed $save
	 * @param mixed $value
	 * @param mixed $field
	 * @return mixed
	 */
	public function validateCoverFieldValue( $save, $value, $field ) {
		if ( ! is_object( $field ) || ! method_exists( $field, 'get_base_name' ) ) {
			return $save;
		}

		$field_name = $field->get_base_name();

		// Carbon Fields prefixes field names with _.
		if ( $field_name !== 'crb_podcast_cover' && $field_name !== '_crb_podcast_cover' ) {
			return $save;
		}

		if ( empty( $value ) ) {
			return $save;
		}

		$validation_result = $this->validateCoverImage( $value );

		if ( is_wp_error( $validation_result ) ) {
			$user_id = get_current_user_id();
			set_transient( 'crb_podcast_cover_error_' . $user_id, $validation_result->get_error_message(), 120 );
			return false;
		}

		delete_transient( 'crb_podcast_cover_error_' . get_current_user_id() );
		return $save;
	}

	/**
	 * Validate podcast cover on REST API save request.
	 *
	 * @param mixed            $result
	 * @param \WP_REST_Server  $server
	 * @param \WP_REST_Request $request
	 * @return mixed
	 */
	public function validateCoverOnRestSave( $result, $server, $request ) {
		$route = $request->get_route();
		if ( strpos( $route, '/carbon-fields/' ) === false ) {
			return $result;
		}

		$method = $request->get_method();
		if ( ! in_array( $method, array( 'POST', 'PUT' ), true ) ) {
			return $result;
		}

		$params = $request->get_json_params();
		if ( empty( $params ) ) {
			$params = $request->get_body_params();
		}
		if ( empty( $params ) ) {
			$body   = $request->get_body();
			$params = json_decode( $body, true ) ?: array();
		}

		if ( ! is_array( $params ) ) {
			return $result;
		}

		$cover_url = $this->findCoverUrlInData( $params );
		if ( empty( $cover_url ) ) {
			return $result;
		}

		$validation_result = $this->validateCoverImage( $cover_url );
		if ( is_wp_error( $validation_result ) ) {
			return new \WP_Error(
				'podcast_cover_validation_failed',
				$validation_result->get_error_message(),
				array( 'status' => 400 )
			);
		}

		return $result;
	}

	/**
	 * Find podcast cover URL in request data (supports various data structures).
	 *
	 * @param array $data
	 * @return string|null
	 */
	private function findCoverUrlInData( $data ) {
		$field_keys = array( '_crb_podcast_cover', 'crb_podcast_cover' );

		foreach ( $field_keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$normalized = $this->normalizeCoverUrlFromValue( $data[ $key ] );
				if ( null !== $normalized ) {
					return $normalized;
				}
			}
		}

		if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {
			foreach ( $data['fields'] as $field ) {
				if ( ! is_array( $field ) ) {
					continue;
				}
				$name = $field['name'] ?? ( $field['base_name'] ?? ( $field['field_name'] ?? '' ) );
				if ( in_array( $name, $field_keys, true ) && array_key_exists( 'value', $field ) ) {
					$normalized = $this->normalizeCoverUrlFromValue( $field['value'] );
					if ( null !== $normalized ) {
						return $normalized;
					}
				}
			}
		}

		foreach ( $data as $value ) {
			if ( is_array( $value ) ) {
				$found = $this->findCoverUrlInData( $value );
				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * Display validation errors as admin notices.
	 */
	public function displayCoverValidationErrors() {
		$user_id = get_current_user_id();
		$error   = get_transient( 'crb_podcast_cover_error_' . $user_id );

		if ( ! $error ) {
			return;
		}

		printf(
			'<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Podcast Cover validation failed:', 'a-ripple-song' ),
			esc_html( $error )
		);

		delete_transient( 'crb_podcast_cover_error_' . $user_id );
	}

	/**
	 * Validate cover image from URL (local or remote).
	 *
	 * @param string $url
	 * @return true|\WP_Error
	 */
	public function validateCoverImage( $url ) {
		$url = $this->normalizeCoverUrlFromValue( $url );
		if ( null === $url ) {
			return new \WP_Error( 'invalid_url', __( 'Podcast Cover URL is invalid.', 'a-ripple-song' ) );
		}

		$max_bytes      = 512 * 1024;
		$min_dimension  = 1400;
		$max_dimension  = 3000;
		$allowed_mimes  = array( 'image/jpeg', 'image/png' );
		$local_file_path = $this->resolveLocalFilePath( $url );

		if ( null !== $local_file_path && file_exists( $local_file_path ) ) {
			return $this->validateLocalCoverFile( $local_file_path, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes );
		}

		if ( ! preg_match( '~^https?://~i', $url ) ) {
			return new \WP_Error( 'invalid_url', __( 'Podcast Cover URL is invalid.', 'a-ripple-song' ) );
		}

		return $this->validateRemoteCoverUrl( $url, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes );
	}

	/**
	 * Normalize a cover "value" to a usable URL string.
	 *
	 * Carbon Fields can submit either attachment IDs or URLs depending on the field configuration.
	 * This also normalizes protocol-relative and root-relative URLs.
	 *
	 * @param mixed $value
	 * @return string|null
	 */
	private function normalizeCoverUrlFromValue( $value ) {
		if ( is_array( $value ) ) {
			foreach ( array( 'url', 'file_url', 'value', 'src' ) as $key ) {
				if ( isset( $value[ $key ] ) && is_string( $value[ $key ] ) ) {
					$normalized = $this->normalizeCoverUrlFromValue( $value[ $key ] );
					if ( null !== $normalized ) {
						return $normalized;
					}
				}
			}

			foreach ( array( 'id', 'attachment_id' ) as $key ) {
				if ( isset( $value[ $key ] ) ) {
					$normalized = $this->normalizeCoverUrlFromValue( $value[ $key ] );
					if ( null !== $normalized ) {
						return $normalized;
					}
				}
			}

			return null;
		}

		if ( is_int( $value ) || ( is_string( $value ) && ctype_digit( $value ) ) ) {
			$attachment_id = (int) $value;
			if ( $attachment_id > 0 ) {
				$url = wp_get_attachment_url( $attachment_id );
				if ( is_string( $url ) && $url !== '' ) {
					return $this->normalizeCoverUrlFromValue( $url );
				}
			}
			return null;
		}

		if ( ! is_string( $value ) ) {
			return null;
		}

		$url = trim( $value );
		if ( $url === '' ) {
			return null;
		}

		// Accept protocol-relative URLs.
		if ( strpos( $url, '//' ) === 0 ) {
			$url = ( is_ssl() ? 'https:' : 'http:' ) . $url;
		}

		// Accept root-relative URLs (e.g. /wp-content/uploads/...).
		if ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
			$url = home_url( $url );
		}

		return $url;
	}

	/**
	 * Try to resolve URL to a local file path.
	 *
	 * @param string $url
	 * @return string|null
	 */
	private function resolveLocalFilePath( $url ) {
		if ( function_exists( 'attachment_url_to_postid' ) ) {
			$url_for_id = $url;
			if ( strpos( $url_for_id, '?' ) !== false ) {
				$url_for_id = (string) preg_replace( '~\\?.*$~', '', $url_for_id );
			}

			$attachment_id = attachment_url_to_postid( $url_for_id );
			if ( $attachment_id ) {
				$attached_file = get_attached_file( $attachment_id );
				if ( is_string( $attached_file ) && $attached_file !== '' ) {
					$attached_file_real = realpath( $attached_file );
					if ( $attached_file_real !== false ) {
						return wp_normalize_path( $attached_file_real );
					}
				}
			}
		}

		$upload_dir   = wp_get_upload_dir();
		$basedir      = isset( $upload_dir['basedir'] ) ? (string) $upload_dir['basedir'] : '';
		$basedir_real = $basedir !== '' ? realpath( $basedir ) : false;
		$basedir_real = $basedir_real !== false ? wp_normalize_path( $basedir_real ) : false;

		if ( $basedir_real && isset( $upload_dir['baseurl'] ) && strpos( $url, $upload_dir['baseurl'] ) === 0 ) {
			$candidate      = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
			$candidate_real = realpath( $candidate );
			if ( $candidate_real !== false ) {
				$candidate_real = wp_normalize_path( $candidate_real );
				if ( strpos( $candidate_real, $basedir_real . '/' ) === 0 || $candidate_real === $basedir_real ) {
					return $candidate_real;
				}
			}
		}

		$uploads_url_path = isset( $upload_dir['baseurl'] ) ? wp_parse_url( $upload_dir['baseurl'], PHP_URL_PATH ) : null;
		$url_path         = wp_parse_url( $url, PHP_URL_PATH );

		if (
			$basedir_real
			&& is_string( $uploads_url_path ) && $uploads_url_path !== ''
			&& is_string( $url_path ) && $url_path !== ''
			&& strpos( $url_path, $uploads_url_path ) === 0
		) {
			$relative = ltrim( substr( $url_path, strlen( $uploads_url_path ) ), '/' );
			$relative = $relative !== '' ? rawurldecode( $relative ) : '';
			if ( $relative !== '' ) {
				$candidate      = trailingslashit( (string) $upload_dir['basedir'] ) . $relative;
				$candidate_real = realpath( $candidate );
				if ( $candidate_real !== false ) {
					$candidate_real = wp_normalize_path( $candidate_real );
					if ( strpos( $candidate_real, $basedir_real . '/' ) === 0 || $candidate_real === $basedir_real ) {
						return $candidate_real;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Validate a local cover file.
	 */
		private function validateLocalCoverFile( $file_path, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes ) {
			$file_size = @filesize( $file_path );
			if ( is_int( $file_size ) && $file_size > $max_bytes ) {
				return new \WP_Error(
					'file_too_large',
					sprintf(
						/* translators: 1: file size, 2: maximum allowed size */
						__( 'Podcast Cover file is too large (%1$s). Please compress it to under %2$s.', 'a-ripple-song' ),
						size_format( $file_size ),
						size_format( $max_bytes )
					)
			);
		}

		$image_info = @getimagesize( $file_path );
		if ( ! $image_info ) {
			return new \WP_Error( 'invalid_image', __( 'Podcast Cover is not a valid image.', 'a-ripple-song' ) );
		}

		return $this->validateImageDimensions( $image_info, $min_dimension, $max_dimension, $allowed_mimes );
	}

	/**
	 * Validate a remote cover URL.
	 */
		private function validateRemoteCoverUrl( $url, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes ) {
			if ( function_exists( 'wp_http_validate_url' ) && ! wp_http_validate_url( $url ) ) {
				return new \WP_Error( 'invalid_url', __( 'Podcast Cover URL is invalid.', 'a-ripple-song' ) );
			}

		$head_fn       = function_exists( 'wp_safe_remote_head' ) ? 'wp_safe_remote_head' : 'wp_remote_head';
		$head_response = $head_fn(
			$url,
			array(
				'timeout'     => 10,
				'redirection' => 5,
			)
		);

		if ( ! is_wp_error( $head_response ) ) {
			$content_length = wp_remote_retrieve_header( $head_response, 'content-length' );
				if ( ! empty( $content_length ) && (int) $content_length > $max_bytes ) {
					return new \WP_Error(
						'file_too_large',
						sprintf(
							/* translators: 1: file size, 2: maximum allowed size */
							__( 'Podcast Cover file is too large (%1$s). Please compress it to under %2$s.', 'a-ripple-song' ),
							size_format( (int) $content_length ),
							size_format( $max_bytes )
						)
				);
			}
		}

			$temp_file = download_url( $url, 30 );
			if ( is_wp_error( $temp_file ) ) {
				return new \WP_Error(
					'download_failed',
					sprintf(
						/* translators: %s: error message */
						__( 'Could not download Podcast Cover for validation: %s', 'a-ripple-song' ),
						$temp_file->get_error_message()
					)
				);
			}

			$result = $this->validateLocalCoverFile( $temp_file, $max_bytes, $min_dimension, $max_dimension, $allowed_mimes );
			wp_delete_file( $temp_file );

			return $result;
		}

	/**
	 * Validate image dimensions and format.
	 *
	 * @param array $image_info
	 * @param int   $min_dimension
	 * @param int   $max_dimension
	 * @param array $allowed_mimes
	 * @return true|\WP_Error
	 */
	private function validateImageDimensions( $image_info, $min_dimension, $max_dimension, $allowed_mimes ) {
		$width  = isset( $image_info[0] ) ? (int) $image_info[0] : 0;
		$height = isset( $image_info[1] ) ? (int) $image_info[1] : 0;
		$mime   = isset( $image_info['mime'] ) ? $image_info['mime'] : '';

		if ( ! in_array( $mime, $allowed_mimes, true ) ) {
			return new \WP_Error( 'invalid_format', __( 'Podcast Cover must be JPG or PNG.', 'a-ripple-song' ) );
		}

			if ( $width !== $height ) {
				return new \WP_Error(
					'not_square',
					sprintf(
						/* translators: 1: image width in pixels, 2: image height in pixels */
						__( 'Podcast Cover must be square. Current dimensions: %1$d × %2$d px.', 'a-ripple-song' ),
						$width,
						$height
					)
			);
		}

			if ( $width < $min_dimension ) {
				return new \WP_Error(
					'too_small',
					sprintf(
						/* translators: 1: minimum required dimension in pixels, 2: image width in pixels, 3: image height in pixels */
						__( 'Podcast Cover resolution is too small. Minimum: %1$d × %1$d px. Current: %2$d × %3$d px.', 'a-ripple-song' ),
						$min_dimension,
						$width,
						$height
					)
				);
			}

			if ( $width > $max_dimension ) {
				return new \WP_Error(
					'too_large',
					sprintf(
						/* translators: 1: maximum allowed dimension in pixels, 2: image width in pixels, 3: image height in pixels */
						__( 'Podcast Cover resolution is too large. Maximum: %1$d × %1$d px. Current: %2$d × %3$d px.', 'a-ripple-song' ),
						$max_dimension,
						$width,
						$height
					)
				);
		}

		return true;
	}

	/**
	 * Podcast language options.
	 *
	 * @return array<string,string>
	 */
	private function getPodcastLanguageOptions() {
		return array(
			'en-US' => 'en-US',
			'en-GB' => 'en-GB',
			'en-AU' => 'en-AU',
			'en-CA' => 'en-CA',
			'zh-CN' => 'zh-CN',
			'zh-TW' => 'zh-TW',
			'ja-JP' => 'ja-JP',
			'ko-KR' => 'ko-KR',
			'fr-FR' => 'fr-FR',
			'de-DE' => 'de-DE',
			'es-ES' => 'es-ES',
			'es-MX' => 'es-MX',
			'pt-BR' => 'pt-BR',
			'ru-RU' => 'ru-RU',
		);
	}

	/**
	 * Get the podcast feed URL with proper permalink structure handling.
	 *
	 * @return string
	 */
	public function getPodcastFeedUrl() {
		$permalink_structure = get_option( 'permalink_structure' );

		if ( empty( $permalink_structure ) ) {
			return home_url( '/?feed=podcast' );
		}

		if ( strpos( (string) $permalink_structure, '/index.php/' ) === 0 ) {
			return home_url( '/index.php/feed/podcast/' );
		}

		return home_url( '/feed/podcast/' );
	}

	/**
	 * Simplified Apple Podcasts categories.
	 *
	 * @return array<string,string>
	 */
	private function getItunesCategories() {
		return array(
			'Arts'                         => 'Arts',
			'Arts::Books'                  => 'Arts → Books',
			'Arts::Design'                 => 'Arts → Design',
			'Arts::Fashion & Beauty'       => 'Arts → Fashion & Beauty',
			'Arts::Food'                   => 'Arts → Food',
			'Arts::Performing Arts'        => 'Arts → Performing Arts',
			'Arts::Visual Arts'            => 'Arts → Visual Arts',
			'Business'                     => 'Business',
			'Business::Careers'            => 'Business → Careers',
			'Business::Entrepreneurship'   => 'Business → Entrepreneurship',
			'Business::Investing'          => 'Business → Investing',
			'Business::Management'         => 'Business → Management',
			'Business::Marketing'          => 'Business → Marketing',
			'Business::Non-Profit'         => 'Business → Non-Profit',
			'Comedy'                       => 'Comedy',
			'Comedy::Comedy Interviews'    => 'Comedy → Comedy Interviews',
			'Comedy::Improv'               => 'Comedy → Improv',
			'Comedy::Stand-Up'             => 'Comedy → Stand-Up',
			'Education'                    => 'Education',
			'Education::Courses'           => 'Education → Courses',
			'Education::How To'            => 'Education → How To',
			'Education::Language Learning' => 'Education → Language Learning',
			'Education::Self-Improvement'  => 'Education → Self-Improvement',
			'Fiction'                      => 'Fiction',
			'Fiction::Comedy Fiction'      => 'Fiction → Comedy Fiction',
			'Fiction::Drama'               => 'Fiction → Drama',
			'Fiction::Science Fiction'     => 'Fiction → Science Fiction',
			'Government'                   => 'Government',
			'History'                      => 'History',
			'Health & Fitness'             => 'Health & Fitness',
			'Health & Fitness::Alternative Health' => 'Health & Fitness → Alternative Health',
			'Health & Fitness::Fitness'    => 'Health & Fitness → Fitness',
			'Health & Fitness::Medicine'   => 'Health & Fitness → Medicine',
			'Health & Fitness::Mental Health' => 'Health & Fitness → Mental Health',
			'Health & Fitness::Nutrition'  => 'Health & Fitness → Nutrition',
			'Health & Fitness::Sexuality'  => 'Health & Fitness → Sexuality',
			'Kids & Family'                => 'Kids & Family',
			'Kids & Family::Education for Kids' => 'Kids & Family → Education for Kids',
			'Kids & Family::Parenting'     => 'Kids & Family → Parenting',
			'Kids & Family::Pets & Animals' => 'Kids & Family → Pets & Animals',
			'Kids & Family::Stories for Kids' => 'Kids & Family → Stories for Kids',
			'Leisure'                      => 'Leisure',
			'Leisure::Animation & Manga'   => 'Leisure → Animation & Manga',
			'Leisure::Automotive'          => 'Leisure → Automotive',
			'Leisure::Aviation'            => 'Leisure → Aviation',
			'Leisure::Crafts'              => 'Leisure → Crafts',
			'Leisure::Games'               => 'Leisure → Games',
			'Leisure::Hobbies'             => 'Leisure → Hobbies',
			'Leisure::Home & Garden'       => 'Leisure → Home & Garden',
			'Leisure::Video Games'         => 'Leisure → Video Games',
			'Music'                        => 'Music',
			'Music::Music Commentary'      => 'Music → Music Commentary',
			'Music::Music History'         => 'Music → Music History',
			'Music::Music Interviews'      => 'Music → Music Interviews',
			'News'                         => 'News',
			'News::Business News'          => 'News → Business News',
			'News::Daily News'             => 'News → Daily News',
			'News::Entertainment News'     => 'News → Entertainment News',
			'News::News Commentary'        => 'News → News Commentary',
			'News::Politics'               => 'News → Politics',
			'News::Sports News'            => 'News → Sports News',
			'News::Tech News'              => 'News → Tech News',
			'Religion & Spirituality'      => 'Religion & Spirituality',
			'Religion & Spirituality::Buddhism' => 'Religion & Spirituality → Buddhism',
			'Religion & Spirituality::Christianity' => 'Religion & Spirituality → Christianity',
			'Religion & Spirituality::Hinduism' => 'Religion & Spirituality → Hinduism',
			'Religion & Spirituality::Islam' => 'Religion & Spirituality → Islam',
			'Religion & Spirituality::Judaism' => 'Religion & Spirituality → Judaism',
			'Religion & Spirituality::Religion' => 'Religion & Spirituality → Religion',
			'Religion & Spirituality::Spirituality' => 'Religion & Spirituality → Spirituality',
			'Science'                      => 'Science',
			'Science::Astronomy'           => 'Science → Astronomy',
			'Science::Chemistry'           => 'Science → Chemistry',
			'Science::Earth Sciences'      => 'Science → Earth Sciences',
			'Science::Life Sciences'       => 'Science → Life Sciences',
			'Science::Mathematics'         => 'Science → Mathematics',
			'Science::Natural Sciences'    => 'Science → Natural Sciences',
			'Science::Nature'              => 'Science → Nature',
			'Science::Physics'             => 'Science → Physics',
			'Society & Culture'            => 'Society & Culture',
			'Society & Culture::Documentary' => 'Society & Culture → Documentary',
			'Society & Culture::Personal Journals' => 'Society & Culture → Personal Journals',
			'Society & Culture::Philosophy' => 'Society & Culture → Philosophy',
			'Society & Culture::Places & Travel' => 'Society & Culture → Places & Travel',
			'Society & Culture::Relationships' => 'Society & Culture → Relationships',
			'Sports'                       => 'Sports',
			'Sports::Baseball'             => 'Sports → Baseball',
			'Sports::Basketball'           => 'Sports → Basketball',
			'Sports::Cricket'              => 'Sports → Cricket',
			'Sports::Fantasy Sports'       => 'Sports → Fantasy Sports',
			'Sports::Football'             => 'Sports → Football',
			'Sports::Golf'                 => 'Sports → Golf',
			'Sports::Hockey'               => 'Sports → Hockey',
			'Sports::Rugby'                => 'Sports → Rugby',
			'Sports::Running'              => 'Sports → Running',
			'Sports::Soccer'               => 'Sports → Soccer',
			'Sports::Swimming'             => 'Sports → Swimming',
			'Sports::Tennis'               => 'Sports → Tennis',
			'Sports::Volleyball'           => 'Sports → Volleyball',
			'Sports::Wilderness'           => 'Sports → Wilderness',
			'Sports::Wrestling'            => 'Sports → Wrestling',
			'Technology'                   => 'Technology',
			'True Crime'                   => 'True Crime',
			'TV & Film'                    => 'TV & Film',
			'TV & Film::After Shows'       => 'TV & Film → After Shows',
			'TV & Film::Film History'      => 'TV & Film → Film History',
			'TV & Film::Film Interviews'   => 'TV & Film → Film Interviews',
			'TV & Film::Film Reviews'      => 'TV & Film → Film Reviews',
			'TV & Film::TV Reviews'        => 'TV & Film → TV Reviews',
		);
	}
}
