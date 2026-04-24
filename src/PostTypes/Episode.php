<?php

namespace ARippleSong\PostTypes;

use ARippleSong\Constants\BaseConstant;
use ARippleSong\Constants\EpisodeConstant;

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
	public const POST_TYPE = EpisodeConstant::POST_TYPE;

	/**
	 * Taxonomy key.
	 */
	public const TAXONOMY = EpisodeConstant::TAXONOMY_SLUG;

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

	/**
	 * Meta box ID.
	 */
	private const META_BOX_ID = 'ars_episode_details';

	/**
	 * Register the episode meta box.
	 */
	public function registerMetaBox() {
		add_meta_box(
			self::META_BOX_ID,
			__( 'Episode Details', 'a-ripple-song' ),
			array( $this, 'renderMetaBox' ),
			Episode::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the episode meta box.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function renderMetaBox( $post ) {
		$values = $this->getFormValues( (int) $post->ID );

		wp_nonce_field( 'ars_episode_details_save', 'ars_episode_details_nonce' );
		?>
		<div id="ars-episode-details-form" class="ars-admin-form" data-ars-admin-form="episode">
			<div class="ars-admin-section">
				<h2><?php echo esc_html__( 'Media', 'a-ripple-song' ); ?></h2>
				<?php $this->renderMediaField( 'audio_file', __( 'Audio File', 'a-ripple-song' ), $values['audio_file'], __( 'Required. Upload an audio file or enter audio file URL (https).', 'a-ripple-song' ), 'audio', true ); ?>
				<?php $this->renderReadonlyField( 'duration', __( 'Duration (seconds)', 'a-ripple-song' ), $values['duration'], __( 'Auto detected from "Audio File" on save.', 'a-ripple-song' ) ); ?>
				<?php $this->renderReadonlyField( 'audio_length', __( 'Audio Length (bytes)', 'a-ripple-song' ), $values['audio_length'], __( 'Auto detected from "Audio File" on save.', 'a-ripple-song' ) ); ?>
				<?php $this->renderReadonlyField( 'audio_mime', __( 'Audio MIME Type', 'a-ripple-song' ), $values['audio_mime'], __( 'Auto detected from "Audio File" on save.', 'a-ripple-song' ) ); ?>
			</div>

			<div class="ars-admin-section">
				<h2><?php echo esc_html__( 'Episode Details', 'a-ripple-song' ); ?></h2>
				<?php $this->renderSelectField( 'episode_explicit', __( 'Explicit', 'a-ripple-song' ), $values['episode_explicit'], array( 'clean' => __( 'clean', 'a-ripple-song' ), 'explicit' => __( 'explicit', 'a-ripple-song' ) ), __( 'Required. clean / explicit.', 'a-ripple-song' ), true ); ?>
				<?php $this->renderSelectField( 'episode_type', __( 'Episode Type', 'a-ripple-song' ), $values['episode_type'], array( 'full' => __( 'full', 'a-ripple-song' ), 'trailer' => __( 'trailer', 'a-ripple-song' ), 'bonus' => __( 'bonus', 'a-ripple-song' ) ), __( 'Required. full / trailer / bonus.', 'a-ripple-song' ), true ); ?>
				<?php $this->renderNumberField( 'episode_number', __( 'Episode Number', 'a-ripple-song' ), $values['episode_number'], __( 'Optional but recommended. Integer.', 'a-ripple-song' ) ); ?>
				<?php $this->renderNumberField( 'season_number', __( 'Season Number', 'a-ripple-song' ), $values['season_number'], __( 'Optional. Integer.', 'a-ripple-song' ) ); ?>
				<?php $this->renderTextField( 'episode_author', __( 'Episode Author (override)', 'a-ripple-song' ), $values['episode_author'], __( 'Optional. Overrides channel author for this episode.', 'a-ripple-song' ) ); ?>
				<?php $this->renderMediaField( 'episode_image', __( 'Episode Cover (square)', 'a-ripple-song' ), $values['episode_image'], __( 'Optional. Square 1400–3000px. Overrides channel cover.', 'a-ripple-song' ), 'image' ); ?>
				<?php $this->renderMediaField( 'episode_transcript', __( 'Transcript (optional)', 'a-ripple-song' ), $values['episode_transcript'], __( 'Optional. Upload a transcript file (vtt/srt/txt/pdf) or enter a transcript URL (https).', 'a-ripple-song' ), 'transcript' ); ?>
				<?php $this->renderTextField( 'itunes_title', __( 'iTunes Title (optional)', 'a-ripple-song' ), $values['itunes_title'], __( 'Optional. Apple Podcasts: overrides the episode title for <itunes:title>.', 'a-ripple-song' ) ); ?>
				<?php $this->renderMediaField( 'episode_chapters', __( 'Chapters (Podcasting 2.0)', 'a-ripple-song' ), $values['episode_chapters'], __( 'Optional. Provide a chapters JSON URL/file for <podcast:chapters>.', 'a-ripple-song' ), 'chapters' ); ?>
				<?php $this->renderTextField( 'episode_chapters_type', __( 'Chapters MIME Type', 'a-ripple-song' ), $values['episode_chapters_type'], __( 'Optional. Defaults to application/json+chapters.', 'a-ripple-song' ) ); ?>
				<?php $this->renderTextareaField( 'episode_subtitle', __( 'Subtitle', 'a-ripple-song' ), $values['episode_subtitle'], __( 'Optional. Short subtitle for iTunes.', 'a-ripple-song' ) ); ?>
				<?php $this->renderTextareaField( 'episode_summary', __( 'Summary', 'a-ripple-song' ), $values['episode_summary'], __( 'Optional. Plain text summary for iTunes.', 'a-ripple-song' ) ); ?>
				<?php $this->renderTextField( 'episode_guid', __( 'Custom GUID (optional)', 'a-ripple-song' ), $values['episode_guid'], __( 'Optional. If empty, feed uses WP permalink as GUID.', 'a-ripple-song' ) ); ?>
				<?php $this->renderSelectField( 'episode_block', __( 'iTunes Block', 'a-ripple-song' ), $values['episode_block'], array( 'no' => __( 'no', 'a-ripple-song' ), 'yes' => __( 'yes', 'a-ripple-song' ) ), __( 'Optional. yes = hide this episode in Apple Podcasts.', 'a-ripple-song' ) ); ?>
				<?php $this->renderRepeatableSoundbitesField( $values['episode_soundbites'] ); ?>
			</div>

			<div class="ars-admin-section">
				<h2><?php echo esc_html__( 'People', 'a-ripple-song' ); ?></h2>
				<?php $this->renderUserMultiSelectField( 'members', __( 'Members', 'a-ripple-song' ), $values['members'], __( 'Select episode members (administrators, authors, editors).', 'a-ripple-song' ) ); ?>
				<?php $this->renderUserMultiSelectField( 'guests', __( 'Guests', 'a-ripple-song' ), $values['guests'], __( 'Select episode guests (contributors).', 'a-ripple-song' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the episode meta box.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function saveMetaBox( $post_id ) {
		if ( get_post_type( $post_id ) !== Episode::POST_TYPE ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['ars_episode_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ars_episode_details_nonce'] ) ), 'ars_episode_details_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$input = array();
		if ( isset( $_POST['ars_episode_details'] ) ) {
			$posted_details = map_deep( wp_unslash( $_POST['ars_episode_details'] ), 'sanitize_text_field' );
			$input          = is_array( $posted_details ) ? $posted_details : array();
		}

		$this->saveFormValues( $post_id, $input );
	}

	/**
	 * Collect episode values for rendering.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string,mixed>
	 */
	private function getFormValues( $post_id ) {
		$default_members = $this->getDefaultMembersValue();

		return array(
			'audio_file'           => (string) self::getStoredPostMetaValue( $post_id, 'audio_file', '' ),
			'duration'             => (int) self::getStoredPostMetaValue( $post_id, 'duration', 0 ),
			'audio_length'         => (int) self::getStoredPostMetaValue( $post_id, 'audio_length', 0 ),
			'audio_mime'           => (string) self::getStoredPostMetaValue( $post_id, 'audio_mime', 'audio/mpeg' ),
			'episode_explicit'     => (string) self::getStoredPostMetaValue( $post_id, 'episode_explicit', 'clean' ),
			'episode_type'         => (string) self::getStoredPostMetaValue( $post_id, 'episode_type', 'full' ),
			'episode_number'       => (int) self::getStoredPostMetaValue( $post_id, 'episode_number', 0 ),
			'season_number'        => (int) self::getStoredPostMetaValue( $post_id, 'season_number', 0 ),
			'episode_author'       => (string) self::getStoredPostMetaValue( $post_id, 'episode_author', '' ),
			'episode_image'        => (string) self::getStoredPostMetaValue( $post_id, 'episode_image', '' ),
			'episode_transcript'    => (string) self::getStoredPostMetaValue( $post_id, 'episode_transcript', '' ),
			'itunes_title'         => (string) self::getStoredPostMetaValue( $post_id, 'itunes_title', '' ),
			'episode_chapters'     => (string) self::getStoredPostMetaValue( $post_id, 'episode_chapters', '' ),
			'episode_chapters_type' => (string) self::getStoredPostMetaValue( $post_id, 'episode_chapters_type', 'application/json+chapters' ),
			'episode_soundbites'   => self::getStoredPostMetaArray( $post_id, 'episode_soundbites', array() ),
			'episode_subtitle'     => (string) self::getStoredPostMetaValue( $post_id, 'episode_subtitle', '' ),
			'episode_summary'      => (string) self::getStoredPostMetaValue( $post_id, 'episode_summary', '' ),
			'episode_guid'         => (string) self::getStoredPostMetaValue( $post_id, 'episode_guid', '' ),
			'episode_block'        => (string) self::getStoredPostMetaValue( $post_id, 'episode_block', 'no' ),
			'members'              => self::getStoredPostMetaArray( $post_id, 'members', $default_members ),
			'guests'               => self::getStoredPostMetaArray( $post_id, 'guests', array() ),
		);
	}

	/**
	 * Save cleaned values.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $input Raw request data.
	 * @return void
	 */
	private function saveFormValues( $post_id, $input ) {
		$this->saveStringMeta( $post_id, 'audio_file', $input['audio_file'] ?? '' );
		$this->saveIntMeta( $post_id, 'duration', $input['duration'] ?? 0 );
		$this->saveIntMeta( $post_id, 'audio_length', $input['audio_length'] ?? 0 );
		$this->saveStringMeta( $post_id, 'audio_mime', $input['audio_mime'] ?? 'audio/mpeg' );
		$this->saveSelectMeta( $post_id, 'episode_explicit', $input['episode_explicit'] ?? 'clean', array( 'clean', 'explicit' ), 'clean' );
		$this->saveSelectMeta( $post_id, 'episode_type', $input['episode_type'] ?? 'full', array( 'full', 'trailer', 'bonus' ), 'full' );
		$this->saveIntMeta( $post_id, 'episode_number', $input['episode_number'] ?? 0 );
		$this->saveIntMeta( $post_id, 'season_number', $input['season_number'] ?? 0 );
		$this->saveStringMeta( $post_id, 'episode_author', $input['episode_author'] ?? '' );
		$this->saveStringMeta( $post_id, 'episode_image', $input['episode_image'] ?? '' );
		$this->saveStringMeta( $post_id, 'episode_transcript', $input['episode_transcript'] ?? '' );
		$this->saveStringMeta( $post_id, 'itunes_title', $input['itunes_title'] ?? '' );
		$this->saveStringMeta( $post_id, 'episode_chapters', $input['episode_chapters'] ?? '' );
		$this->saveStringMeta( $post_id, 'episode_chapters_type', $input['episode_chapters_type'] ?? 'application/json+chapters' );
		$this->saveRepeatableMeta( $post_id, 'episode_soundbites', $input['episode_soundbites'] ?? array() );
		$this->saveStringMeta( $post_id, 'episode_subtitle', $input['episode_subtitle'] ?? '' );
		$this->saveStringMeta( $post_id, 'episode_summary', $input['episode_summary'] ?? '' );
		$this->saveStringMeta( $post_id, 'episode_guid', $input['episode_guid'] ?? '' );
		$this->saveSelectMeta( $post_id, 'episode_block', $input['episode_block'] ?? 'no', array( 'no', 'yes' ), 'no' );
		$this->saveUsersMeta( $post_id, 'members', $input['members'] ?? array() );
		$this->saveUsersMeta( $post_id, 'guests', $input['guests'] ?? array() );
	}

	/**
	 * Render a text field row.
	 */
	private function renderTextField( $key, $label, $value, $help = '', $required = false ) {
		$this->renderFieldStart( $label, $help, $required );
		?>
		<input type="text" class="regular-text" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" <?php echo $required ? 'required aria-required="true"' : ''; ?> />
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render a readonly field row.
	 */
	private function renderReadonlyField( $key, $label, $value, $help = '' ) {
		$this->renderFieldStart( $label, $help );
		?>
		<input type="text" class="regular-text" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" readonly />
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render a number field row.
	 */
	private function renderNumberField( $key, $label, $value, $help = '' ) {
		$this->renderFieldStart( $label, $help );
		?>
		<input type="number" class="small-text" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" min="0" step="1" />
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render a textarea field row.
	 */
	private function renderTextareaField( $key, $label, $value, $help = '', $required = false ) {
		$this->renderFieldStart( $label, $help, $required );
		?>
		<textarea class="large-text" rows="4" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" <?php echo $required ? 'required aria-required="true"' : ''; ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render a select field row.
	 */
	private function renderSelectField( $key, $label, $value, $options, $help = '', $required = false ) {
		$this->renderFieldStart( $label, $help, $required );
		?>
		<select name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" <?php echo $required ? 'required aria-required="true"' : ''; ?>>
			<?php foreach ( $options as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( (string) $option_label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render a media URL field row.
	 */
	private function renderMediaField( $key, $label, $value, $help = '', $mode = 'transcript', $required = false ) {
		$this->renderFieldStart( $label, $help, $required );
		$input_type = $mode === 'image' ? 'hidden' : 'url';
		?>
		<div class="ars-media-field">
			<input type="<?php echo esc_attr( $input_type ); ?>" class="regular-text" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" placeholder="https://" data-ars-media-uploader="<?php echo esc_attr( $mode ); ?>" <?php if ( $required && $input_type !== 'hidden' ) : ?>required aria-required="true"<?php endif; ?> />
		</div>
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render a multi-select users field row.
	 */
	private function renderUserMultiSelectField( $key, $label, $selected_ids, $help = '' ) {
		$this->renderFieldStart( $label, $help );
		$users = get_users(
			array(
				'orderby' => 'display_name',
				'order'   => 'ASC',
				'fields'  => array( 'ID', 'display_name', 'user_login' ),
				'number'  => 500,
			)
		);
		$selected_ids = array_map( 'intval', (array) $selected_ids );
		?>
		<div class="ars-user-multiselect-field">
			<select multiple="multiple" size="6" name="ars_episode_details[<?php echo esc_attr( $key ); ?>][]" data-ars-user-multiselect>
				<?php foreach ( $users as $user ) : ?>
					<?php $label_text = $user->display_name ? $user->display_name : $user->user_login; ?>
					<option value="<?php echo esc_attr( (string) $user->ID ); ?>" <?php selected( in_array( (int) $user->ID, $selected_ids, true ) ); ?>><?php echo esc_html( $label_text ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button ars-user-multiselect-field__clear" data-ars-user-multiselect-clear><?php echo esc_html__( 'Clear selection', 'a-ripple-song' ); ?></button>
		</div>
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render soundbite repeatable rows.
	 */
	private function renderRepeatableSoundbitesField( $rows ) {
		$rows = is_array( $rows ) ? array_values( $rows ) : array();
		if ( empty( $rows ) ) {
			$rows = array(
				array(
					'start_time' => '',
					'duration'   => '',
					'title'      => '',
				),
			);
		}
		?>
		<div class="ars-repeatable-field" data-ars-repeatable-field="soundbites">
			<div class="ars-repeatable-field__header">
				<h3><?php echo esc_html__( 'Soundbites (Podcasting 2.0)', 'a-ripple-song' ); ?></h3>
				<p class="description"><?php echo esc_html__( 'Optional. Adds one or more <podcast:soundbite> tags.', 'a-ripple-song' ); ?></p>
			</div>
			<div class="ars-repeatable-field__rows" data-ars-repeatable-rows>
				<?php foreach ( $rows as $row ) : ?>
					<div class="ars-repeatable-field__row">
						<div class="ars-repeatable-field__grid">
							<input type="number" step="0.01" min="0" name="ars_episode_details[episode_soundbites][][start_time]" value="<?php echo esc_attr( (string) ( $row['start_time'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'Start Time (seconds)', 'a-ripple-song' ); ?>" />
							<input type="number" step="0.01" min="0.01" name="ars_episode_details[episode_soundbites][][duration]" value="<?php echo esc_attr( (string) ( $row['duration'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'Duration (seconds)', 'a-ripple-song' ); ?>" />
							<input type="text" name="ars_episode_details[episode_soundbites][][title]" value="<?php echo esc_attr( (string) ( $row['title'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'Title (optional)', 'a-ripple-song' ); ?>" />
						</div>
						<button type="button" class="button-link-delete" data-ars-repeatable-remove><?php echo esc_html__( 'Delete', 'a-ripple-song' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
			<template data-ars-repeatable-template>
				<div class="ars-repeatable-field__row">
					<div class="ars-repeatable-field__grid">
						<input type="number" step="0.01" min="0" name="ars_episode_details[episode_soundbites][][start_time]" value="" placeholder="<?php echo esc_attr__( 'Start Time (seconds)', 'a-ripple-song' ); ?>" />
						<input type="number" step="0.01" min="0.01" name="ars_episode_details[episode_soundbites][][duration]" value="" placeholder="<?php echo esc_attr__( 'Duration (seconds)', 'a-ripple-song' ); ?>" />
						<input type="text" name="ars_episode_details[episode_soundbites][][title]" value="" placeholder="<?php echo esc_attr__( 'Title (optional)', 'a-ripple-song' ); ?>" />
					</div>
					<button type="button" class="button-link-delete" data-ars-repeatable-remove><?php echo esc_html__( 'Delete', 'a-ripple-song' ); ?></button>
				</div>
			</template>
			<p><button type="button" class="button" data-ars-repeatable-add><?php echo esc_html__( '+ Add Item', 'a-ripple-song' ); ?></button></p>
		</div>
		<?php
	}

	/**
	 * Render a field wrapper start.
	 */
	private function renderFieldStart( $label, $help = '', $required = false ) {
		?>
		<div class="ars-admin-field">
			<label class="ars-admin-field__label">
				<?php echo esc_html( (string) $label ); ?>
				<?php if ( $required ) : ?>
					<span class="ars-required-marker" aria-hidden="true">*</span>
				<?php endif; ?>
			</label>
			<?php if ( $help !== '' ) : ?>
				<p class="description"><?php echo esc_html( (string) $help ); ?></p>
			<?php endif; ?>
		<?php
	}

	/**
	 * Close a field wrapper.
	 */
	private function renderFieldEnd() {
		?>
		</div>
		<?php
	}

	/**
	 * Persist a text value.
	 */
	private function saveStringMeta( $post_id, $key, $value ) {
		update_post_meta( $post_id, '_' . ltrim( (string) $key, '_' ), sanitize_text_field( (string) $value ) );
	}

	/**
	 * Persist a number value.
	 */
	private function saveIntMeta( $post_id, $key, $value ) {
		update_post_meta( $post_id, '_' . ltrim( (string) $key, '_' ), absint( $value ) );
	}

	/**
	 * Persist a select value.
	 */
	private function saveSelectMeta( $post_id, $key, $value, $allowed, $default ) {
		$value = sanitize_text_field( (string) $value );
		if ( ! in_array( $value, $allowed, true ) ) {
			$value = $default;
		}

		update_post_meta( $post_id, '_' . ltrim( (string) $key, '_' ), $value );
	}

	/**
	 * Persist repeatable rows.
	 */
	private function saveRepeatableMeta( $post_id, $key, $rows ) {
		$clean_rows = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$start_time = isset( $row['start_time'] ) ? (float) $row['start_time'] : 0.0;
				$duration   = isset( $row['duration'] ) ? (float) $row['duration'] : 0.0;
				$title      = isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '';

				if ( $start_time <= 0 || $duration <= 0 ) {
					continue;
				}

				$clean_rows[] = array(
					'start_time' => $start_time,
					'duration'   => $duration,
					'title'      => $title,
				);
			}
		}

		update_post_meta( $post_id, '_' . ltrim( (string) $key, '_' ), $clean_rows );
	}

	/**
	 * Persist selected user IDs.
	 */
	private function saveUsersMeta( $post_id, $key, $values ) {
		$user_ids = array();
		if ( is_array( $values ) ) {
			foreach ( $values as $value ) {
				if ( is_array( $value ) ) {
					$value = $value['id'] ?? ( $value['value'] ?? '' );
				}

				if ( is_numeric( $value ) ) {
					$user_id = (int) $value;
					$user    = get_userdata( $user_id );
					if ( $user ) {
						$user_ids[] = $user_id;
					}
				}
			}
		}

		$user_ids = array_values( array_unique( array_filter( $user_ids ) ) );
		update_post_meta( $post_id, '_' . ltrim( (string) $key, '_' ), $user_ids );
	}

	/**
	 * Default selected member when creating a new episode.
	 *
	 * @return array<int,int>
	 */
	private function getDefaultMembersValue() {
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			return array();
		}

		$current_user = get_userdata( $current_user_id );
		if ( ! $current_user ) {
			return array();
		}

		$allowed_roles = array( 'administrator', 'author', 'editor' );
		if ( empty( array_intersect( $allowed_roles, (array) $current_user->roles ) ) ) {
			return array();
		}

		return array( (int) $current_user_id );
	}

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

					$timeout = (int) apply_filters( BaseConstant::PREFIX . '_podcast_episode_audio_meta_download_timeout', 300, $audio_url, $post_id );
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
		$value = self::getStoredPostMetaValue( $post_id, $key, '' );
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
					return (string) self::getEpisodeValue( (int) $post['id'], 'audio_file', '' );
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
					return (int) self::getEpisodeValue( (int) $post['id'], 'duration', 0 );
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
					return (string) self::getEpisodeValue( (int) $post['id'], 'episode_transcript', '' );
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
		return self::getStoredPostMetaValue( $post_id, $key, $default );
	}

	/**
	 * Read one current-format Episode Details meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key Meta key without leading underscore.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	private static function getStoredPostMetaValue( $post_id, $key, $default = null ) {
		$value = get_post_meta( (int) $post_id, '_' . ltrim( (string) $key, '_' ), true );

		return $value !== '' ? $value : $default;
	}

	/**
	 * Read one current-format Episode Details array meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key Meta key without leading underscore.
	 * @param array  $default Default value.
	 * @return array
	 */
	private static function getStoredPostMetaArray( $post_id, $key, array $default = array() ) {
		$value = get_post_meta( (int) $post_id, '_' . ltrim( (string) $key, '_' ), true );

		return is_array( $value ) ? $value : $default;
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
