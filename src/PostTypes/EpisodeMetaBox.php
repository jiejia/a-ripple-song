<?php

namespace ARippleSong\PostTypes;

use ARippleSong\Core\LegacyMeta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Native episode meta box implementation.
 */
class EpisodeMetaBox {

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
		<div id="<?php echo esc_attr( self::META_BOX_ID ); ?>" class="ars-admin-form" data-ars-admin-form="episode">
			<div class="ars-admin-section">
				<h2><?php echo esc_html__( 'Media', 'a-ripple-song' ); ?></h2>
				<?php $this->renderMediaField( 'audio_file', __( 'Audio File', 'a-ripple-song' ), $values['audio_file'], __( 'Required. Upload an audio file or enter audio file URL (https).', 'a-ripple-song' ), 'audio' ); ?>
				<?php $this->renderReadonlyField( 'duration', __( 'Duration (seconds)', 'a-ripple-song' ), $values['duration'], __( 'Auto detected from "Audio File" on save.', 'a-ripple-song' ) ); ?>
				<?php $this->renderReadonlyField( 'audio_length', __( 'Audio Length (bytes)', 'a-ripple-song' ), $values['audio_length'], __( 'Auto detected from "Audio File" on save.', 'a-ripple-song' ) ); ?>
				<?php $this->renderReadonlyField( 'audio_mime', __( 'Audio MIME Type', 'a-ripple-song' ), $values['audio_mime'], __( 'Auto detected from "Audio File" on save.', 'a-ripple-song' ) ); ?>
			</div>

			<div class="ars-admin-section">
				<h2><?php echo esc_html__( 'Episode Details', 'a-ripple-song' ); ?></h2>
				<?php $this->renderSelectField( 'episode_explicit', __( 'Explicit', 'a-ripple-song' ), $values['episode_explicit'], array( 'clean' => __( 'clean', 'a-ripple-song' ), 'explicit' => __( 'explicit', 'a-ripple-song' ) ), __( 'Required. clean / explicit.', 'a-ripple-song' ) ); ?>
				<?php $this->renderSelectField( 'episode_type', __( 'Episode Type', 'a-ripple-song' ), $values['episode_type'], array( 'full' => __( 'full', 'a-ripple-song' ), 'trailer' => __( 'trailer', 'a-ripple-song' ), 'bonus' => __( 'bonus', 'a-ripple-song' ) ), __( 'Required. full / trailer / bonus.', 'a-ripple-song' ) ); ?>
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

		$input = isset( $_POST['ars_episode_details'] ) && is_array( $_POST['ars_episode_details'] ) ? wp_unslash( $_POST['ars_episode_details'] ) : array();
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
			'audio_file'           => (string) LegacyMeta::getPostMetaValue( $post_id, 'audio_file', '' ),
			'duration'             => (int) LegacyMeta::getPostMetaValue( $post_id, 'duration', 0 ),
			'audio_length'         => (int) LegacyMeta::getPostMetaValue( $post_id, 'audio_length', 0 ),
			'audio_mime'           => (string) LegacyMeta::getPostMetaValue( $post_id, 'audio_mime', 'audio/mpeg' ),
			'episode_explicit'     => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_explicit', 'clean' ),
			'episode_type'         => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_type', 'full' ),
			'episode_number'       => (int) LegacyMeta::getPostMetaValue( $post_id, 'episode_number', 0 ),
			'season_number'        => (int) LegacyMeta::getPostMetaValue( $post_id, 'season_number', 0 ),
			'episode_author'       => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_author', '' ),
			'episode_image'        => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_image', '' ),
			'episode_transcript'    => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_transcript', '' ),
			'itunes_title'         => (string) LegacyMeta::getPostMetaValue( $post_id, 'itunes_title', '' ),
			'episode_chapters'     => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_chapters', '' ),
			'episode_chapters_type' => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_chapters_type', 'application/json+chapters' ),
			'episode_soundbites'   => LegacyMeta::getPostMetaArray( $post_id, 'episode_soundbites', array() ),
			'episode_subtitle'     => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_subtitle', '' ),
			'episode_summary'      => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_summary', '' ),
			'episode_guid'         => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_guid', '' ),
			'episode_block'        => (string) LegacyMeta::getPostMetaValue( $post_id, 'episode_block', 'no' ),
			'members'              => LegacyMeta::getPostMetaArray( $post_id, 'members', $default_members ),
			'guests'               => LegacyMeta::getPostMetaArray( $post_id, 'guests', array() ),
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
		$this->saveUsersMeta( $post_id, 'members', $input['members'] ?? array(), array( 'administrator', 'author', 'editor' ) );
		$this->saveUsersMeta( $post_id, 'guests', $input['guests'] ?? array(), array( 'administrator', 'author', 'editor', 'contributor', 'subscriber' ) );
	}

	/**
	 * Render a text field row.
	 */
	private function renderTextField( $key, $label, $value, $help = '' ) {
		$this->renderFieldStart( $label, $help );
		?>
		<input type="text" class="regular-text" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" />
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
	private function renderTextareaField( $key, $label, $value, $help = '' ) {
		$this->renderFieldStart( $label, $help );
		?>
		<textarea class="large-text" rows="4" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]"><?php echo esc_textarea( (string) $value ); ?></textarea>
		<?php
		$this->renderFieldEnd();
	}

	/**
	 * Render a select field row.
	 */
	private function renderSelectField( $key, $label, $value, $options, $help = '' ) {
		$this->renderFieldStart( $label, $help );
		?>
		<select name="ars_episode_details[<?php echo esc_attr( $key ); ?>]">
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
	private function renderMediaField( $key, $label, $value, $help = '', $mode = 'transcript' ) {
		$this->renderFieldStart( $label, $help );
		?>
		<div class="ars-media-field">
			<input type="url" class="regular-text" name="ars_episode_details[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $value ); ?>" placeholder="https://" data-ars-media-uploader="<?php echo esc_attr( $mode ); ?>" />
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
		<select multiple="multiple" size="6" name="ars_episode_details[<?php echo esc_attr( $key ); ?>][]">
			<?php foreach ( $users as $user ) : ?>
				<?php $label_text = $user->display_name ? $user->display_name : $user->user_login; ?>
				<option value="<?php echo esc_attr( (string) $user->ID ); ?>" <?php selected( in_array( (int) $user->ID, $selected_ids, true ) ); ?>><?php echo esc_html( $label_text ); ?></option>
			<?php endforeach; ?>
		</select>
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
	private function renderFieldStart( $label, $help = '' ) {
		?>
		<div class="ars-admin-field">
			<label class="ars-admin-field__label"><?php echo esc_html( (string) $label ); ?></label>
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
	private function saveUsersMeta( $post_id, $key, $values, $allowed_roles ) {
		$user_ids = array();
		if ( is_array( $values ) ) {
			foreach ( $values as $value ) {
				if ( is_array( $value ) ) {
					$value = $value['id'] ?? ( $value['value'] ?? '' );
				}

				if ( is_numeric( $value ) ) {
					$user_id = (int) $value;
					$user    = get_userdata( $user_id );
					if ( $user && array_intersect( $allowed_roles, (array) $user->roles ) ) {
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
}
