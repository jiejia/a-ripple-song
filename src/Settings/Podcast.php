<?php

namespace ARippleSong\Settings;

use ARippleSong\Constants\BaseConstant;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Native podcast settings page.
 */
class Podcast {

	/**
	 * Top-level menu slug.
	 */
	private const MENU_SLUG = BaseConstant::PLUGIN_SLUG;

	/**
	 * Settings page slug.
	 */
	private const PAGE_SLUG = BaseConstant::PLUGIN_SLUG . '-settings';

	/**
	 * Single option name used for all podcast settings.
	 */
	public const OPTION_NAME = BaseConstant::PREFIX . '_podcast_settings';

	/**
	 * Form action slug.
	 */
	public const SAVE_ACTION = BaseConstant::PREFIX . '_podcast_save';

	/**
	 * Nonce action.
	 */
	private const NONCE_ACTION = BaseConstant::PREFIX . '_save';

	/**
	 * Nonce field name.
	 */
	private const NONCE_FIELD = BaseConstant::PREFIX . '_nonce';

	/**
	 * Notice nonce action.
	 */
	private const NOTICE_NONCE_ACTION = BaseConstant::PREFIX . '_notice';

	/**
	 * Transient prefix for save notices.
	 */
	private const NOTICE_PREFIX = BaseConstant::PREFIX . '_notices_';

	/**
	 * Query argument used to identify a completed save.
	 */
	private const SAVED_QUERY_ARG = BaseConstant::PREFIX . '_saved';

	/**
	 * Query argument carrying the notice nonce.
	 */
	private const NOTICE_NONCE_QUERY_ARG = BaseConstant::PREFIX . '_notice_nonce';

	/**
	 * Register the admin menu.
	 */
	public function registerMenuPage() {
		add_menu_page(
			__( 'A Ripple Song', 'a-ripple-song' ),
			__( 'A Ripple Song', 'a-ripple-song' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'renderLandingPage' ),
			'dashicons-admin-settings',
			60
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Podcast Settings', 'a-ripple-song' ),
			__( 'Podcast Settings', 'a-ripple-song' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'renderSettingsPage' )
		);

		add_action( 'admin_menu', array( $this, 'removeDuplicateLandingPage' ), 999 );
		add_action( 'admin_notices', array( $this, 'displayNotices' ) );
	}

	/**
	 * Render the landing menu page.
	 */
	public function renderLandingPage() {
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
		exit;
	}

	/**
	 * Remove the duplicate landing submenu item.
	 */
	public function removeDuplicateLandingPage() {
		remove_submenu_page( self::MENU_SLUG, self::MENU_SLUG );
	}

	/**
	 * Handle settings save requests.
	 */
	public function handleSave() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to manage these settings.', 'a-ripple-song' ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$input = array();
		if ( isset( $_POST[ self::OPTION_NAME ] ) ) {
			$posted_settings = map_deep( wp_unslash( $_POST[ self::OPTION_NAME ] ), 'sanitize_text_field' );
			$input           = is_array( $posted_settings ) ? $posted_settings : array();
		}

		$result = $this->sanitizeSettings( $input );

		update_option( self::OPTION_NAME, $result['settings'], false );

		if ( ! empty( $result['errors'] ) ) {
			set_transient( self::NOTICE_PREFIX . get_current_user_id(), $result['errors'], 60 );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                    => self::PAGE_SLUG,
					self::SAVED_QUERY_ARG      => '1',
					self::NOTICE_NONCE_QUERY_ARG => wp_create_nonce( self::NOTICE_NONCE_ACTION ),
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render admin notices.
	 */
	public function displayNotices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$saved_notice = isset( $_GET[ self::SAVED_QUERY_ARG ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::SAVED_QUERY_ARG ] ) ) : '';
		$notice_nonce = isset( $_GET[ self::NOTICE_NONCE_QUERY_ARG ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::NOTICE_NONCE_QUERY_ARG ] ) ) : '';
		if ( $saved_notice === '1' && wp_verify_nonce( $notice_nonce, self::NOTICE_NONCE_ACTION ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Podcast settings saved.', 'a-ripple-song' ) . '</p></div>';
		}

		$errors = get_transient( self::NOTICE_PREFIX . get_current_user_id() );
		if ( ! is_array( $errors ) || empty( $errors ) ) {
			return;
		}

		foreach ( $errors as $error ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( (string) $error ) . '</p></div>';
		}

		delete_transient( self::NOTICE_PREFIX . get_current_user_id() );
	}

	/**
	 * Render the settings page.
	 */
	public function renderSettingsPage() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$values = $this->getCurrentSettings();
		?>
		<div class="wrap" id="ars-podcast-settings" data-ars-admin-form="settings">
			<h1><?php echo esc_html__( 'Podcast Settings', 'a-ripple-song' ); ?></h1>
			<p class="description"><?php echo esc_html__( 'Configure the channel metadata and feed tags for /feed/podcast.', 'a-ripple-song' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( self::SAVE_ACTION ); ?>" />

				<table class="form-table" role="presentation">
					<tbody>
						<?php $this->renderReadonlyRow( __( 'Podcast RSS URL', 'a-ripple-song' ), $this->getPodcastFeedUrl(), __( 'Your podcast RSS feed URL. Click to select and copy.', 'a-ripple-song' ) ); ?>
						<?php $this->renderTextRow( 'title', __( 'Podcast Title', 'a-ripple-song' ), $values['title'], __( 'Required. If empty, falls back to site title.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderTextRow( 'subtitle', __( 'Podcast Subtitle', 'a-ripple-song' ), $values['subtitle'], __( 'Short tagline shown in some apps.', 'a-ripple-song' ) ); ?>
						<?php $this->renderTextareaRow( 'description', __( 'Podcast Description', 'a-ripple-song' ), $values['description'], __( 'Required. Plain text description of the show.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderTextRow( 'author', __( 'Podcast Author (itunes:author)', 'a-ripple-song' ), $values['author'], __( 'Required. Displayed as show author in directories.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderTextRow( 'owner_name', __( 'Owner Name', 'a-ripple-song' ), $values['owner_name'], __( 'Required. For <itunes:owner><itunes:name>.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderEmailRow( 'owner_email', __( 'Owner Email', 'a-ripple-song' ), $values['owner_email'], __( 'Required. For <itunes:owner><itunes:email>. Use a monitored inbox.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderMediaRow( 'cover', __( 'Podcast Cover (1400–3000px square)', 'a-ripple-song' ), $values['cover'], __( 'Required. Square JPG/PNG between 1400–3000px for <itunes:image>. Apple recommends keeping the file under 512KB.', 'a-ripple-song' ), 'image', true ); ?>
						<?php $this->renderSelectRow( 'explicit', __( 'Default Explicit Flag', 'a-ripple-song' ), $values['explicit'], array( 'clean' => __( 'clean (no explicit content)', 'a-ripple-song' ), 'explicit' => __( 'explicit', 'a-ripple-song' ) ), __( 'Required. Single-episode value can override.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderSelectRow( 'language', __( 'Language (RFC 5646)', 'a-ripple-song' ), $values['language'], $this->getPodcastLanguageOptions(), __( 'Required. Typically en-US, zh-CN, etc.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderSelectRow( 'category_primary', __( 'Primary Category (Apple Podcasts)', 'a-ripple-song' ), $values['category_primary'], array( '' => __( '(not set)', 'a-ripple-song' ) ) + $this->getItunesCategories(), __( 'Required by Apple Podcasts. Choose at least a primary category.', 'a-ripple-song' ), true ); ?>
						<?php $this->renderSelectRow( 'category_secondary', __( 'Secondary Category (optional)', 'a-ripple-song' ), $values['category_secondary'], array( '' => __( '(not set)', 'a-ripple-song' ) ) + $this->getItunesCategories(), __( 'Optional. Some directories support a second category.', 'a-ripple-song' ) ); ?>
						<?php $this->renderTextRow( 'copyright', __( 'Copyright (optional)', 'a-ripple-song' ), $values['copyright'], __( 'Optional. For <copyright>.', 'a-ripple-song' ) ); ?>
						<?php $this->renderSelectRow( 'itunes_type', __( 'iTunes Type (itunes:type)', 'a-ripple-song' ), $values['itunes_type'], array( '' => __( '(not set)', 'a-ripple-song' ), 'episodic' => __( 'episodic', 'a-ripple-song' ), 'serial' => __( 'serial', 'a-ripple-song' ) ), __( 'Optional. Apple Podcasts: episodic or serial.', 'a-ripple-song' ) ); ?>
						<?php $this->renderTextRow( 'itunes_title', __( 'iTunes Title (optional)', 'a-ripple-song' ), $values['itunes_title'], __( 'Optional. Use only if you need a separate Apple-facing title.', 'a-ripple-song' ) ); ?>
						<?php $this->renderSelectRow( 'itunes_block', __( 'iTunes Block (itunes:block)', 'a-ripple-song' ), $values['itunes_block'], array( 'no' => __( 'no', 'a-ripple-song' ), 'yes' => __( 'yes', 'a-ripple-song' ) ), __( 'Optional. yes = hide this show in Apple Podcasts.', 'a-ripple-song' ) ); ?>
						<?php $this->renderSelectRow( 'itunes_complete', __( 'iTunes Complete (itunes:complete)', 'a-ripple-song' ), $values['itunes_complete'], array( 'no' => __( 'no', 'a-ripple-song' ), 'yes' => __( 'yes', 'a-ripple-song' ) ), __( 'Optional. yes = this show is complete (no more episodes).', 'a-ripple-song' ) ); ?>
						<?php $this->renderTextRow( 'itunes_new_feed_url', __( 'iTunes New Feed URL (itunes:new-feed-url)', 'a-ripple-song' ), $values['itunes_new_feed_url'], __( 'Optional. Only for moving your show to a new RSS feed URL.', 'a-ripple-song' ) ); ?>
						<?php $this->renderSelectRow( 'locked', __( 'podcast:locked', 'a-ripple-song' ), $values['locked'], array( 'yes' => __( 'yes (recommended, prevents unauthorized moves)', 'a-ripple-song' ), 'no' => __( 'no', 'a-ripple-song' ) ), __( 'Podcasting 2.0: lock feed to this publisher.', 'a-ripple-song' ) ); ?>
						<?php $this->renderEmailRow( 'locked_owner', __( 'podcast:locked owner (optional)', 'a-ripple-song' ), $values['locked_owner'], __( 'Optional. Podcasting 2.0: email used to verify ownership during moves.', 'a-ripple-song' ) ); ?>
						<?php $this->renderTextRow( 'guid', __( 'podcast:guid (optional)', 'a-ripple-song' ), $values['guid'], __( 'Podcasting 2.0 GUID. If empty, feed will use site URL as fallback.', 'a-ripple-song' ) ); ?>
						<?php $this->renderTextRow( 'apple_verify', __( 'Apple Podcasts Verify Code (podcast:txt purpose="applepodcastsverify")', 'a-ripple-song' ), $values['apple_verify'], __( 'Optional. Used by Apple Podcasts to verify feed ownership.', 'a-ripple-song' ) ); ?>
						<?php $this->renderFundingField( $values['funding'] ); ?>
						<?php $this->renderTextRow( 'generator', __( 'Generator (optional)', 'a-ripple-song' ), $values['generator'], __( 'Optional. If empty, generator tag will not be included.', 'a-ripple-song' ) ); ?>
					</tbody>
				</table>

				<?php submit_button( __( 'Save Settings', 'a-ripple-song' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Return default podcast settings.
	 *
	 * @return array<string,mixed>
	 */
	private static function getDefaultSettings() {
		return array(
			'title'              => get_bloginfo( 'name' ),
			'subtitle'           => '',
			'description'        => get_bloginfo( 'description' ),
			'author'             => get_bloginfo( 'name' ),
			'owner_name'         => get_bloginfo( 'name' ),
			'owner_email'        => get_bloginfo( 'admin_email' ),
			'cover'              => '',
			'explicit'           => 'clean',
			'language'           => get_bloginfo( 'language' ) ?: 'en-US',
			'category_primary'   => '',
			'category_secondary' => '',
			'copyright'          => '',
			'itunes_type'        => '',
			'itunes_title'       => '',
			'itunes_block'       => 'no',
			'itunes_complete'    => 'no',
			'itunes_new_feed_url' => '',
			'locked'             => 'yes',
			'locked_owner'       => '',
			'guid'               => home_url( '/' ),
			'apple_verify'       => '',
			'funding'            => array(),
			'generator'          => '',
		);
	}

	/**
	 * Return the saved podcast settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function getSettings() {
		$settings = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return array_merge( self::getDefaultSettings(), $settings );
	}

	/**
	 * Return one podcast setting value.
	 *
	 * @param string $key Setting key without a plugin prefix.
	 * @param mixed  $default Default value used when the setting does not exist.
	 * @return mixed
	 */
	public static function getSetting( $key, $default = null ) {
		$settings = self::getSettings();

		return array_key_exists( (string) $key, $settings ) ? $settings[ (string) $key ] : $default;
	}

	/**
	 * Collect current values for the settings form.
	 *
	 * @return array<string,mixed>
	 */
	private function getCurrentSettings() {
		return self::getSettings();
	}

	/**
	 * Sanitize incoming settings.
	 *
	 * @param array $input Raw request data.
	 * @return array{settings:array<string,mixed>,errors:array<int,string>}
	 */
	private function sanitizeSettings( $input ) {
		$current = $this->getCurrentSettings();
		$settings = $current;
		$errors   = array();

		$settings['title']       = sanitize_text_field( (string) ( $input['title'] ?? $current['title'] ) );
		$settings['subtitle']    = sanitize_text_field( (string) ( $input['subtitle'] ?? $current['subtitle'] ) );
		$settings['description'] = sanitize_textarea_field( (string) ( $input['description'] ?? $current['description'] ) );
		$settings['author']      = sanitize_text_field( (string) ( $input['author'] ?? $current['author'] ) );
		$settings['owner_name']  = sanitize_text_field( (string) ( $input['owner_name'] ?? $current['owner_name'] ) );
		$settings['owner_email'] = sanitize_email( (string) ( $input['owner_email'] ?? $current['owner_email'] ) );
		$settings['cover']       = sanitize_text_field( (string) ( $input['cover'] ?? $current['cover'] ) );
		$explicit = (string) ( $input['explicit'] ?? $current['explicit'] );
		if ( ! in_array( $explicit, array( 'clean', 'explicit' ), true ) ) {
			$explicit = 'clean';
		}
		$settings['explicit'] = $explicit;
		$settings['language'] = sanitize_text_field( (string) ( $input['language'] ?? $current['language'] ) );
		$settings['category_primary'] = sanitize_text_field( (string) ( $input['category_primary'] ?? $current['category_primary'] ) );
		$settings['category_secondary'] = sanitize_text_field( (string) ( $input['category_secondary'] ?? $current['category_secondary'] ) );
		$settings['copyright'] = sanitize_text_field( (string) ( $input['copyright'] ?? $current['copyright'] ) );
		$itunes_type = (string) ( $input['itunes_type'] ?? $current['itunes_type'] );
		if ( ! in_array( $itunes_type, array( '', 'episodic', 'serial' ), true ) ) {
			$itunes_type = '';
		}
		$settings['itunes_type'] = $itunes_type;
		$settings['itunes_title'] = sanitize_text_field( (string) ( $input['itunes_title'] ?? $current['itunes_title'] ) );
		$itunes_block = (string) ( $input['itunes_block'] ?? $current['itunes_block'] );
		if ( ! in_array( $itunes_block, array( 'no', 'yes' ), true ) ) {
			$itunes_block = 'no';
		}
		$settings['itunes_block'] = $itunes_block;
		$itunes_complete = (string) ( $input['itunes_complete'] ?? $current['itunes_complete'] );
		if ( ! in_array( $itunes_complete, array( 'no', 'yes' ), true ) ) {
			$itunes_complete = 'no';
		}
		$settings['itunes_complete'] = $itunes_complete;
		$settings['itunes_new_feed_url'] = esc_url_raw( (string) ( $input['itunes_new_feed_url'] ?? $current['itunes_new_feed_url'] ) );
		$locked = (string) ( $input['locked'] ?? $current['locked'] );
		if ( ! in_array( $locked, array( 'yes', 'no' ), true ) ) {
			$locked = 'yes';
		}
		$settings['locked'] = $locked;
		$settings['locked_owner'] = sanitize_email( (string) ( $input['locked_owner'] ?? $current['locked_owner'] ) );
		$settings['guid']         = esc_url_raw( (string) ( $input['guid'] ?? $current['guid'] ) );
		$settings['apple_verify'] = sanitize_text_field( (string) ( $input['apple_verify'] ?? $current['apple_verify'] ) );
		$settings['generator']    = sanitize_text_field( (string) ( $input['generator'] ?? $current['generator'] ) );
		$settings['funding']      = $this->sanitizeFundingRows( $input['funding'] ?? array() );

		if ( $settings['cover'] === '' ) {
			$errors[] = __( 'Podcast Cover is required.', 'a-ripple-song' );
		} else {
			$validation = $this->validateCoverImage( $settings['cover'] );
			if ( is_wp_error( $validation ) ) {
				$errors[] = $validation->get_error_message();
				$settings['cover'] = (string) $current['cover'];
			}
		}

		if ( $settings['owner_email'] === '' ) {
			$errors[] = __( 'Owner Email is required.', 'a-ripple-song' );
			$settings['owner_email'] = (string) $current['owner_email'];
		}

		return array(
			'settings' => $settings,
			'errors'   => $errors,
		);
	}

	/**
	 * Sanitize funding rows.
	 *
	 * @param mixed $rows Raw rows.
	 * @return array<int,array{url:string,label:string}>
	 */
	private function sanitizeFundingRows( $rows ) {
		$clean = array();

		if ( ! is_array( $rows ) ) {
			return $clean;
		}

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$url   = esc_url_raw( (string) ( $row['url'] ?? '' ) );
			$label = sanitize_text_field( (string) ( $row['label'] ?? '' ) );
			if ( $url === '' ) {
				continue;
			}

			$clean[] = array(
				'url'   => $url,
				'label' => $label,
			);
		}

		return $clean;
	}

	/**
	 * Render a readonly row.
	 */
	private function renderReadonlyRow( $label, $value, $help = '' ) {
		$this->renderFieldRowStart( $label, $help );
		?>
		<input type="text" class="regular-text" value="<?php echo esc_attr( (string) $value ); ?>" readonly onclick="this.select();" />
		<?php
		$this->renderFieldRowEnd();
	}

	/**
	 * Render a text row.
	 */
	private function renderTextRow( $key, $label, $value, $help = '', $required = false ) {
		$this->renderFieldRowStart( $label, $help, $required );
		?>
		<input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" <?php echo $required ? 'required aria-required="true"' : ''; ?> />
		<?php
		$this->renderFieldRowEnd();
	}

	/**
	 * Render an email row.
	 */
	private function renderEmailRow( $key, $label, $value, $help = '', $required = false ) {
		$this->renderFieldRowStart( $label, $help, $required );
		?>
		<input type="email" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" <?php echo $required ? 'required aria-required="true"' : ''; ?> />
		<?php
		$this->renderFieldRowEnd();
	}

	/**
	 * Render a textarea row.
	 */
	private function renderTextareaRow( $key, $label, $value, $help = '', $required = false ) {
		$this->renderFieldRowStart( $label, $help, $required );
		?>
		<textarea class="large-text" rows="4" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]" <?php echo $required ? 'required aria-required="true"' : ''; ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
		<?php
		$this->renderFieldRowEnd();
	}

	/**
	 * Render a select row.
	 */
	private function renderSelectRow( $key, $label, $value, $options, $help = '', $required = false ) {
		$this->renderFieldRowStart( $label, $help, $required );
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]" <?php echo $required ? 'required aria-required="true"' : ''; ?>>
			<?php foreach ( $options as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( (string) $option_label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		$this->renderFieldRowEnd();
	}

	/**
	 * Render a media URL row.
	 */
	private function renderMediaRow( $key, $label, $value, $help = '', $mode = 'transcript', $required = false ) {
		$this->renderFieldRowStart( $label, $help, $required );
		$input_type = $mode === 'image' ? 'hidden' : 'url';
		?>
		<div class="ars-media-field">
			<input type="<?php echo esc_attr( $input_type ); ?>" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" placeholder="https://" data-ars-media-uploader="<?php echo esc_attr( $mode ); ?>" <?php if ( $required && $input_type !== 'hidden' ) : ?>required aria-required="true"<?php endif; ?> />
		</div>
		<?php
		$this->renderFieldRowEnd();
	}

	/**
	 * Render a repeatable funding field.
	 */
	private function renderFundingField( $rows ) {
		$rows = is_array( $rows ) ? array_values( $rows ) : array();
		if ( empty( $rows ) ) {
			$rows = array(
				array(
					'url'   => '',
					'label' => '',
				),
			);
		}
		?>
		<tr>
			<th scope="row">
				<label><?php echo esc_html__( 'Podcasting 2.0 Funding Links (podcast:funding)', 'a-ripple-song' ); ?></label>
			</th>
			<td>
				<p class="description"><?php echo esc_html__( 'Optional. If empty, no podcast:funding tags will be generated. URLs should be https.', 'a-ripple-song' ); ?></p>
				<div class="ars-repeatable-field" data-ars-repeatable-field="funding">
					<div class="ars-repeatable-field__rows" data-ars-repeatable-rows>
						<?php foreach ( $rows as $row ) : ?>
							<div class="ars-repeatable-field__row">
								<div class="ars-repeatable-field__grid">
									<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[funding][][url]" value="<?php echo esc_attr( (string) ( $row['url'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'URL', 'a-ripple-song' ); ?>" />
									<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[funding][][label]" value="<?php echo esc_attr( (string) ( $row['label'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'Label', 'a-ripple-song' ); ?>" />
								</div>
								<button type="button" class="button-link-delete" data-ars-repeatable-remove><?php echo esc_html__( 'Delete', 'a-ripple-song' ); ?></button>
							</div>
						<?php endforeach; ?>
					</div>
					<template data-ars-repeatable-template>
						<div class="ars-repeatable-field__row">
							<div class="ars-repeatable-field__grid">
								<input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[funding][][url]" value="" placeholder="<?php echo esc_attr__( 'URL', 'a-ripple-song' ); ?>" />
								<input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[funding][][label]" value="" placeholder="<?php echo esc_attr__( 'Label', 'a-ripple-song' ); ?>" />
							</div>
							<button type="button" class="button-link-delete" data-ars-repeatable-remove><?php echo esc_html__( 'Delete', 'a-ripple-song' ); ?></button>
						</div>
					</template>
					<p><button type="button" class="button" data-ars-repeatable-add><?php echo esc_html__( '+ Add Item', 'a-ripple-song' ); ?></button></p>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render a field row start.
	 */
	private function renderFieldRowStart( $label, $help = '', $required = false ) {
		?>
		<tr>
			<th scope="row">
				<label>
					<?php echo esc_html( (string) $label ); ?>
					<?php if ( $required ) : ?>
						<span class="ars-required-marker" aria-hidden="true">*</span>
					<?php endif; ?>
				</label>
			</th>
			<td>
				<?php if ( $help !== '' ) : ?>
					<p class="description"><?php echo esc_html( (string) $help ); ?></p>
				<?php endif; ?>
		<?php
	}

	/**
	 * Render a field row end.
	 */
	private function renderFieldRowEnd() {
		?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Override the parent language options helper.
	 *
	 * @return array<string,string>
	 */
	private function getPodcastFeedUrl() {
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
	 * Validate cover image from URL (local or remote).
	 *
	 * @param string $url Cover URL or attachment reference.
	 * @return true|\WP_Error
	 */
	private function validateCoverImage( $url ) {
		$url = $this->normalizeCoverUrlFromValue( $url );
		if ( null === $url ) {
			return new \WP_Error( 'invalid_url', __( 'Podcast Cover URL is invalid.', 'a-ripple-song' ) );
		}

		$max_bytes       = 512 * 1024;
		$min_dimension   = 1400;
		$max_dimension   = 3000;
		$allowed_mimes   = array( 'image/jpeg', 'image/png' );
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
	 * @param mixed $value Cover value.
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

		if ( strpos( $url, '//' ) === 0 ) {
			$url = ( is_ssl() ? 'https:' : 'http:' ) . $url;
		}

		if ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
			$url = home_url( $url );
		}

		return $url;
	}

	/**
	 * Try to resolve URL to a local file path.
	 *
	 * @param string $url Cover URL.
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
	 *
	 * @param string $file_path Local file path.
	 * @param int $max_bytes Maximum file size.
	 * @param int $min_dimension Minimum image dimension.
	 * @param int $max_dimension Maximum image dimension.
	 * @param array $allowed_mimes Allowed MIME types.
	 * @return true|\WP_Error
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
	 *
	 * @param string $url Remote URL.
	 * @param int $max_bytes Maximum file size.
	 * @param int $min_dimension Minimum image dimension.
	 * @param int $max_dimension Maximum image dimension.
	 * @param array $allowed_mimes Allowed MIME types.
	 * @return true|\WP_Error
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
	 * @param array $image_info Image info from getimagesize.
	 * @param int   $min_dimension Minimum image dimension.
	 * @param int   $max_dimension Maximum image dimension.
	 * @param array $allowed_mimes Allowed MIME types.
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
	 * Override the parent category helper.
	 *
	 * @return array<string,string>
	 */
	private function getItunesCategories() {
		return array(
			'Arts' => 'Arts',
			'Arts::Books' => 'Arts → Books',
			'Arts::Design' => 'Arts → Design',
			'Arts::Fashion & Beauty' => 'Arts → Fashion & Beauty',
			'Arts::Food' => 'Arts → Food',
			'Arts::Performing Arts' => 'Arts → Performing Arts',
			'Arts::Visual Arts' => 'Arts → Visual Arts',
			'Business' => 'Business',
			'Business::Careers' => 'Business → Careers',
			'Business::Entrepreneurship' => 'Business → Entrepreneurship',
			'Business::Investing' => 'Business → Investing',
			'Business::Management' => 'Business → Management',
			'Business::Marketing' => 'Business → Marketing',
			'Business::Non-Profit' => 'Business → Non-Profit',
			'Comedy' => 'Comedy',
			'Comedy::Comedy Interviews' => 'Comedy → Comedy Interviews',
			'Comedy::Improv' => 'Comedy → Improv',
			'Comedy::Stand-Up' => 'Comedy → Stand-Up',
			'Education' => 'Education',
			'Education::Courses' => 'Education → Courses',
			'Education::How To' => 'Education → How To',
			'Education::Language Learning' => 'Education → Language Learning',
			'Education::Self-Improvement' => 'Education → Self-Improvement',
			'Fiction' => 'Fiction',
			'Fiction::Comedy Fiction' => 'Fiction → Comedy Fiction',
			'Fiction::Drama' => 'Fiction → Drama',
			'Fiction::Science Fiction' => 'Fiction → Science Fiction',
			'Government' => 'Government',
			'History' => 'History',
			'Health & Fitness' => 'Health & Fitness',
			'Health & Fitness::Alternative Health' => 'Health & Fitness → Alternative Health',
			'Health & Fitness::Fitness' => 'Health & Fitness → Fitness',
			'Health & Fitness::Medicine' => 'Health & Fitness → Medicine',
			'Health & Fitness::Mental Health' => 'Health & Fitness → Mental Health',
			'Health & Fitness::Nutrition' => 'Health & Fitness → Nutrition',
			'Health & Fitness::Sexuality' => 'Health & Fitness → Sexuality',
			'Kids & Family' => 'Kids & Family',
			'Kids & Family::Education for Kids' => 'Kids & Family → Education for Kids',
			'Kids & Family::Parenting' => 'Kids & Family → Parenting',
			'Kids & Family::Pets & Animals' => 'Kids & Family → Pets & Animals',
			'Kids & Family::Stories for Kids' => 'Kids & Family → Stories for Kids',
			'Leisure' => 'Leisure',
			'Leisure::Animation & Manga' => 'Leisure → Animation & Manga',
			'Leisure::Automotive' => 'Leisure → Automotive',
			'Leisure::Aviation' => 'Leisure → Aviation',
			'Leisure::Crafts' => 'Leisure → Crafts',
			'Leisure::Games' => 'Leisure → Games',
			'Leisure::Hobbies' => 'Leisure → Hobbies',
			'Leisure::Home & Garden' => 'Leisure → Home & Garden',
			'Leisure::Video Games' => 'Leisure → Video Games',
			'Music' => 'Music',
			'Music::Music Commentary' => 'Music → Music Commentary',
			'Music::Music History' => 'Music → Music History',
			'Music::Music Interviews' => 'Music → Music Interviews',
			'News' => 'News',
			'News::Business News' => 'News → Business News',
			'News::Daily News' => 'News → Daily News',
			'News::Entertainment News' => 'News → Entertainment News',
			'News::News Commentary' => 'News → News Commentary',
			'News::Politics' => 'News → Politics',
			'News::Sports News' => 'News → Sports News',
			'News::Tech News' => 'News → Tech News',
			'Religion & Spirituality' => 'Religion & Spirituality',
			'Religion & Spirituality::Buddhism' => 'Religion & Spirituality → Buddhism',
			'Religion & Spirituality::Christianity' => 'Religion & Spirituality → Christianity',
			'Religion & Spirituality::Hinduism' => 'Religion & Spirituality → Hinduism',
			'Religion & Spirituality::Islam' => 'Religion & Spirituality → Islam',
			'Religion & Spirituality::Judaism' => 'Religion & Spirituality → Judaism',
			'Religion & Spirituality::Religion' => 'Religion & Spirituality → Religion',
			'Religion & Spirituality::Spirituality' => 'Religion & Spirituality → Spirituality',
			'Science' => 'Science',
			'Science::Astronomy' => 'Science → Astronomy',
			'Science::Chemistry' => 'Science → Chemistry',
			'Science::Earth Sciences' => 'Science → Earth Sciences',
			'Science::Life Sciences' => 'Science → Life Sciences',
			'Science::Mathematics' => 'Science → Mathematics',
			'Science::Natural Sciences' => 'Science → Natural Sciences',
			'Science::Nature' => 'Science → Nature',
			'Science::Physics' => 'Science → Physics',
			'Society & Culture' => 'Society & Culture',
			'Society & Culture::Documentary' => 'Society & Culture → Documentary',
			'Society & Culture::Personal Journals' => 'Society & Culture → Personal Journals',
			'Society & Culture::Philosophy' => 'Society & Culture → Philosophy',
			'Society & Culture::Places & Travel' => 'Society & Culture → Places & Travel',
			'Society & Culture::Relationships' => 'Society & Culture → Relationships',
			'Sports' => 'Sports',
			'Sports::Baseball' => 'Sports → Baseball',
			'Sports::Basketball' => 'Sports → Basketball',
			'Sports::Cricket' => 'Sports → Cricket',
			'Sports::Fantasy Sports' => 'Sports → Fantasy Sports',
			'Sports::Football' => 'Sports → Football',
			'Sports::Golf' => 'Sports → Golf',
			'Sports::Hockey' => 'Sports → Hockey',
			'Sports::Rugby' => 'Sports → Rugby',
			'Sports::Running' => 'Sports → Running',
			'Sports::Soccer' => 'Sports → Soccer',
			'Sports::Swimming' => 'Sports → Swimming',
			'Sports::Tennis' => 'Sports → Tennis',
			'Sports::Volleyball' => 'Sports → Volleyball',
			'Technology' => 'Technology',
			'True Crime' => 'True Crime',
			'TV & Film' => 'TV & Film',
			'TV & Film::After Shows' => 'TV & Film → After Shows',
			'TV & Film::Film History' => 'TV & Film → Film History',
			'TV & Film::Film Interviews' => 'TV & Film → Film Interviews',
			'TV & Film::Film Reviews' => 'TV & Film → Film Reviews',
			'TV & Film::TV Reviews' => 'TV & Film → TV Reviews',
		);
	}
}
