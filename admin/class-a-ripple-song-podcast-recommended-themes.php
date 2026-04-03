<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recommended themes admin page for the shared A Ripple Song settings menu.
 *
 * @package    A_Ripple_Song_Podcast
 * @subpackage A_Ripple_Song_Podcast/admin
 */
class A_Ripple_Song_Podcast_Recommended_Themes {

	/**
	 * Parent settings page slug for the shared menu group.
	 */
	private const PARENT_SETTINGS_PAGE_FILE = 'ars_settings.php';

	/**
	 * Admin page slug for the recommended themes screen.
	 */
	private const PAGE_SLUG = 'ars_recommended_themes';

	/**
	 * Admin-post action for theme activation.
	 */
	private const ACTIVATE_ACTION = 'ars_activate_recommended_theme';

	/**
	 * Admin-post action for theme installation.
	 */
	private const INSTALL_ACTION = 'ars_install_recommended_theme';

	/**
	 * Query arg used to transport admin notices.
	 */
	private const NOTICE_QUERY_ARG = 'ars_theme_notice';

	/**
	 * Query arg used to transport notice types.
	 */
	private const NOTICE_TYPE_QUERY_ARG = 'ars_theme_notice_type';

	/**
	 * Register the ARS Themes submenu page.
	 *
	 * @return void
	 */
	public function register_page() {
		add_submenu_page(
			self::PARENT_SETTINGS_PAGE_FILE,
			__( 'ARS Themes', 'a-ripple-song-podcast' ),
			__( 'ARS Themes', 'a-ripple-song-podcast' ),
			'switch_themes',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the ARS Themes admin screen.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage themes on this site.', 'a-ripple-song-podcast' ) );
		}

		/** @var array<int, array<string, mixed>> $themes Recommended themes enriched with status data. */
		$themes = $this->get_recommended_themes();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'ARS Themes', 'a-ripple-song-podcast' ); ?></h1>
			<p><?php echo esc_html__( 'Themes recommended for the A Ripple Song Podcast plugin.', 'a-ripple-song-podcast' ); ?></p>
			<style>
				.ars-themes-table th,
				.ars-themes-table td {
					vertical-align: middle;
				}

				.ars-themes-table {
					table-layout: fixed;
					width: 100%;
				}

				.ars-themes-table .ars-theme-action {
					margin: 0;
				}

				.ars-themes-table .ars-theme-action .description {
					margin: 0;
				}

				.ars-themes-table code {
					background: #eef2ff;
					border-radius: 4px;
					color: #3730a3;
					display: inline-block;
					padding: 2px 6px;
				}

				.ars-themes-table code,
				.ars-themes-table td {
					overflow-wrap: anywhere;
					word-break: break-word;
				}
			</style>
			<table class="widefat striped ars-themes-table">
				<colgroup>
					<col style="width: 13%;">
					<col style="width: 16%;">
					<col style="width: 37%;">
					<col style="width: 10%;">
					<col style="width: 10%;">
					<col style="width: 14%;">
				</colgroup>
				<thead>
					<tr>
						<th scope="col"><?php echo esc_html__( 'Name', 'a-ripple-song-podcast' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Slug', 'a-ripple-song-podcast' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Description', 'a-ripple-song-podcast' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'ARS Official', 'a-ripple-song-podcast' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Status', 'a-ripple-song-podcast' ); ?></th>
						<th scope="col"><?php echo esc_html__( 'Option', 'a-ripple-song-podcast' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $themes as $theme ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $theme['name'] ); ?></td>
							<td><code><?php echo esc_html( (string) $theme['slug'] ); ?></code></td>
							<td class="ars-theme-description"><?php echo wp_kses( (string) $theme['description'], array( 'code' => array() ) ); ?></td>
							<td><?php echo esc_html( (string) $theme['official_label'] ); ?></td>
							<td><?php echo esc_html( (string) $theme['status_label'] ); ?></td>
							<td>
								<?php if ( 'inactive' === (string) $theme['status'] ) : ?>
									<div class="ars-theme-action">
										<a class="button button-primary" href="<?php echo esc_url( $this->get_activate_url( (string) $theme['slug'] ) ); ?>">
											<?php echo esc_html__( 'Activate', 'a-ripple-song-podcast' ); ?>
										</a>
									</div>
								<?php elseif ( 'missing' === (string) $theme['status'] && (bool) $theme['can_install'] ) : ?>
									<div class="ars-theme-action">
										<a class="button button-primary" href="<?php echo esc_url( $this->get_install_url( (string) $theme['slug'] ) ); ?>">
											<?php echo esc_html__( 'Install', 'a-ripple-song-podcast' ); ?>
										</a>
									</div>
								<?php elseif ( 'missing' === (string) $theme['status'] ) : ?>
									<div class="ars-theme-action">
										<p class="description">
											<?php echo esc_html__( 'This theme will be installable after it is published on WordPress.org.', 'a-ripple-song-podcast' ); ?>
										</p>
									</div>
								<?php else : ?>
									<span aria-hidden="true">-</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Handle the activation action for a recommended theme.
	 *
	 * @return void
	 */
	public function handle_activate_action() {
		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_die( esc_html__( 'You are not allowed to switch themes on this site.', 'a-ripple-song-podcast' ) );
		}

		check_admin_referer( self::ACTIVATE_ACTION );

		/** @var string $theme_slug Requested theme slug from the admin action. */
		$theme_slug = isset( $_GET['theme'] ) ? sanitize_key( wp_unslash( (string) $_GET['theme'] ) ) : '';

		if ( ! $this->is_recommended_theme( $theme_slug ) ) {
			$this->redirect_with_notice( 'recommended-theme-not-found', 'error' );
		}

		/** @var \WP_Theme $theme Installed theme instance resolved by slug. */
		$theme = wp_get_theme( $theme_slug );

		if ( ! $theme->exists() ) {
			$this->redirect_with_notice( 'theme-missing', 'error' );
		}

		switch_theme( $theme->get_stylesheet() );

		if ( get_stylesheet() !== $theme->get_stylesheet() ) {
			$this->redirect_with_notice( 'theme-activation-failed', 'error' );
		}

		$this->redirect_with_notice( 'theme-activated', 'success' );
	}

	/**
	 * Handle the installation action for a recommended theme.
	 *
	 * @return void
	 */
	public function handle_install_action() {
		if ( ! current_user_can( 'install_themes' ) ) {
			wp_die( esc_html__( 'You are not allowed to install themes on this site.', 'a-ripple-song-podcast' ) );
		}

		check_admin_referer( self::INSTALL_ACTION );

		/** @var string $theme_slug Requested theme slug from the admin action. */
		$theme_slug = isset( $_GET['theme'] ) ? sanitize_key( wp_unslash( (string) $_GET['theme'] ) ) : '';

		if ( ! $this->is_recommended_theme( $theme_slug ) ) {
			$this->redirect_with_notice( 'recommended-theme-not-found', 'error' );
		}

		$this->load_theme_installer_functions();

		/** @var object|\WP_Error $theme_information Remote theme information from WordPress.org. */
		$theme_information = themes_api(
			'theme_information',
			array(
				'slug'   => $theme_slug,
				'fields' => array(
					'sections'     => false,
					'tested'       => false,
					'requires'     => false,
					'rating'       => false,
					'downloaded'   => false,
					'downloadlink' => true,
					'last_updated' => false,
					'homepage'     => false,
					'tags'         => false,
					'num_ratings'  => false,
				),
			)
		);

		if ( is_wp_error( $theme_information ) || empty( $theme_information->download_link ) ) {
			$this->redirect_with_notice( 'theme-install-unavailable', 'error' );
		}

		/** @var \Automatic_Upgrader_Skin $skin Quiet upgrader skin for the admin-post workflow. */
		$skin = new Automatic_Upgrader_Skin();

		/** @var \Theme_Upgrader $upgrader Official WordPress theme upgrader instance. */
		$upgrader = new Theme_Upgrader( $skin );

		/** @var bool|\WP_Error $install_result Installation result from the upgrader. */
		$install_result = $upgrader->install( $theme_information->download_link );

		if ( is_wp_error( $install_result ) || true !== $install_result ) {
			$this->redirect_with_notice( 'theme-install-failed', 'error' );
		}

		$this->redirect_with_notice( 'theme-installed', 'success' );
	}

	/**
	 * Render an admin notice after redirecting back to the custom page.
	 *
	 * @return void
	 */
	public function render_admin_notice() {
		if ( ! $this->is_recommended_themes_page() ) {
			return;
		}

		/** @var string $notice_key Notice message key provided by the redirect. */
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice state from our own redirect query args.
		$notice_key = isset( $_GET[ self::NOTICE_QUERY_ARG ] ) ? sanitize_key( wp_unslash( (string) $_GET[ self::NOTICE_QUERY_ARG ] ) ) : '';

		/** @var string $notice_type Notice type provided by the redirect. */
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice state from our own redirect query args.
		$notice_type = isset( $_GET[ self::NOTICE_TYPE_QUERY_ARG ] ) ? sanitize_key( wp_unslash( (string) $_GET[ self::NOTICE_TYPE_QUERY_ARG ] ) ) : 'success';

		if ( '' === $notice_key ) {
			return;
		}

		/** @var array<string, string> $notice_messages Message map for supported notice keys. */
		$notice_messages = $this->get_notice_messages();

		if ( ! isset( $notice_messages[ $notice_key ] ) ) {
			return;
		}

		/** @var string $notice_class Final WordPress admin notice class. */
		$notice_class = 'error' === $notice_type ? 'notice notice-error' : 'notice notice-success';
		?>
		<div class="<?php echo esc_attr( $notice_class ); ?> is-dismissible">
			<p><?php echo esc_html( $notice_messages[ $notice_key ] ); ?></p>
		</div>
		<?php
	}

	/**
	 * Return the recommended themes with derived status metadata.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_recommended_themes() {
		/** @var array<int, array<string, mixed>> $recommended_themes Recommended themes enriched for rendering. */
		$recommended_themes = array();

		foreach ( $this->get_recommended_theme_definitions() as $theme_definition ) {
			/** @var string $theme_slug Recommended theme slug. */
			$theme_slug = (string) $theme_definition['slug'];

			/** @var \WP_Theme $theme Installed theme instance resolved by slug. */
			$theme = wp_get_theme( $theme_slug );

			/** @var string $theme_status Derived theme installation status. */
			$theme_status = 'missing';

			if ( $theme->exists() ) {
				$theme_status = get_stylesheet() === $theme->get_stylesheet() ? 'active' : 'inactive';
			}

			$recommended_themes[] = array(
				'slug'         => $theme_slug,
				'name'         => $theme->exists() ? $theme->get( 'Name' ) : (string) $theme_definition['name'],
				'description'  => $this->format_description( (string) $theme_definition['description'] ),
				'official_label' => $this->get_official_label( (bool) ( $theme_definition['is_official'] ?? false ) ),
				'status'       => $theme_status,
				'status_label' => $this->get_status_label( $theme_status ),
				'can_install'  => 'missing' === $theme_status ? $this->can_install_from_wordpress_org( $theme_slug ) : false,
			);
		}

		return $recommended_themes;
	}

	/**
	 * Return the hard-coded recommended theme definitions.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function get_recommended_theme_definitions() {
		return array(
			array(
				'slug'        => 'a-ripple-song',
				'name'        => 'A Ripple Song',
				'description' => __( 'A theme that provides a complete podcast presentation and playback interface for the A Ripple Song Podcast plugin.', 'a-ripple-song-podcast' ),
				'is_official' => true,
			),
		);
	}

	/**
	 * Return the official label for a recommended theme row.
	 *
	 * @param bool $is_official Whether the item is an official ARS package.
	 * @return string
	 */
	private function get_official_label( $is_official ) {
		return $is_official ? __( 'Yes', 'a-ripple-song-podcast' ) : __( 'No', 'a-ripple-song-podcast' );
	}

	/**
	 * Return the status label for a recommended theme row.
	 *
	 * @param string $status Internal status key.
	 * @return string
	 */
	private function get_status_label( $status ) {
		if ( 'active' === $status ) {
			return __( 'Active', 'a-ripple-song-podcast' );
		}

		if ( 'inactive' === $status ) {
			return __( 'Installed but Inactive', 'a-ripple-song-podcast' );
		}

		return __( 'Not Installed', 'a-ripple-song-podcast' );
	}

	/**
	 * Format the description markup for the recommended theme row.
	 *
	 * @param string $description Raw theme description text.
	 * @return string
	 */
	private function format_description( $description ) {
		/** @var string $plugin_name Plugin name that should be highlighted inside the description text. */
		$plugin_name = 'A Ripple Song Podcast';

		return str_replace( $plugin_name, '<code>' . esc_html( $plugin_name ) . '</code>', esc_html( $description ) );
	}

	/**
	 * Return whether a missing theme can be installed from WordPress.org.
	 *
	 * @param string $theme_slug Theme slug.
	 * @return bool
	 */
	private function can_install_from_wordpress_org( $theme_slug ) {
		$this->load_theme_installer_functions();

		/** @var object|\WP_Error $theme_information Remote theme information lookup result. */
		$theme_information = themes_api(
			'theme_information',
			array(
				'slug'   => $theme_slug,
				'fields' => array(
					'sections'     => false,
					'tested'       => false,
					'requires'     => false,
					'rating'       => false,
					'downloaded'   => false,
					'downloadlink' => true,
					'last_updated' => false,
					'homepage'     => false,
					'tags'         => false,
					'num_ratings'  => false,
				),
			)
		);

		return ! is_wp_error( $theme_information ) && ! empty( $theme_information->download_link );
	}

	/**
	 * Return whether the slug belongs to the recommended theme list.
	 *
	 * @param string $theme_slug Theme slug.
	 * @return bool
	 */
	private function is_recommended_theme( $theme_slug ) {
		/** @var array<int, array<string, string>> $theme_definitions Supported recommended theme definitions. */
		$theme_definitions = $this->get_recommended_theme_definitions();

		foreach ( $theme_definitions as $theme_definition ) {
			if ( (string) $theme_definition['slug'] === $theme_slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return the activate action URL for a theme row.
	 *
	 * @param string $theme_slug Theme slug.
	 * @return string
	 */
	private function get_activate_url( $theme_slug ) {
		/** @var string $activate_url Signed admin-post URL for theme activation. */
		$activate_url = add_query_arg(
			array(
				'action' => self::ACTIVATE_ACTION,
				'theme'  => $theme_slug,
			),
			admin_url( 'admin-post.php' )
		);

		return wp_nonce_url( $activate_url, self::ACTIVATE_ACTION );
	}

	/**
	 * Return the install action URL for a theme row.
	 *
	 * @param string $theme_slug Theme slug.
	 * @return string
	 */
	private function get_install_url( $theme_slug ) {
		/** @var string $install_url Signed admin-post URL for theme installation. */
		$install_url = add_query_arg(
			array(
				'action' => self::INSTALL_ACTION,
				'theme'  => $theme_slug,
			),
			admin_url( 'admin-post.php' )
		);

		return wp_nonce_url( $install_url, self::INSTALL_ACTION );
	}

	/**
	 * Redirect back to the recommended themes screen with a notice payload.
	 *
	 * @param string $notice_key Notice message key.
	 * @param string $notice_type Notice type.
	 * @return void
	 */
	private function redirect_with_notice( $notice_key, $notice_type ) {
		/** @var string $redirect_url Redirect destination for the recommended themes page. */
		$redirect_url = add_query_arg(
			array(
				'page'                         => self::PAGE_SLUG,
				self::NOTICE_QUERY_ARG      => $notice_key,
				self::NOTICE_TYPE_QUERY_ARG => $notice_type,
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Return the supported admin notice messages.
	 *
	 * @return array<string, string>
	 */
	private function get_notice_messages() {
		return array(
			'recommended-theme-not-found' => __( 'The requested recommended theme could not be found.', 'a-ripple-song-podcast' ),
			'theme-missing'               => __( 'The selected theme is not installed on this site.', 'a-ripple-song-podcast' ),
			'theme-activation-failed'     => __( 'The theme could not be activated.', 'a-ripple-song-podcast' ),
			'theme-activated'             => __( 'The theme was activated successfully.', 'a-ripple-song-podcast' ),
			'theme-install-unavailable'   => __( 'The theme is not yet available for installation from WordPress.org.', 'a-ripple-song-podcast' ),
			'theme-install-failed'        => __( 'The theme could not be installed.', 'a-ripple-song-podcast' ),
			'theme-installed'             => __( 'The theme was installed successfully. You can activate it now.', 'a-ripple-song-podcast' ),
		);
	}

	/**
	 * Return whether the current request targets the recommended themes page.
	 *
	 * @return bool
	 */
	private function is_recommended_themes_page() {
		if ( ! is_admin() ) {
			return false;
		}

		/** @var string $page Current admin page slug. */
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing check.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';

		return self::PAGE_SLUG === $page;
	}

	/**
	 * Load the core WordPress theme installer functions when needed.
	 *
	 * @return void
	 */
	private function load_theme_installer_functions() {
		if ( ! function_exists( 'themes_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
		}

		if ( ! class_exists( 'Theme_Upgrader' ) || ! class_exists( 'Automatic_Upgrader_Skin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
		}
	}
}
