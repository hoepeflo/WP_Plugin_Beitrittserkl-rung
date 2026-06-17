<?php
/**
 * WordPress-Administration.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin-Menü und Einstellungsseiten.
 */
class BSE_Admin {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_settings_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Zeigt Konfigurationshinweise im Backend.
	 */
	public function admin_notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( $screen->id, 'bse-' ) ) {
			return;
		}

		$settings = BSE_Settings::get();
		$notices  = array();

		if ( empty( $settings['turnstile_site_key'] ) || empty( $settings['turnstile_secret_key'] ) ) {
			$notices[] = __( 'Bitte hinterlegen Sie Cloudflare-Turnstile-Schlüssel, damit das Captcha aktiv ist.', 'beitrittserklaerung' );
		}

		if ( empty( $settings['privacy_page_id'] ) ) {
			$notices[] = __( 'Bitte wählen Sie eine Datenschutzseite aus (Pflicht für DSGVO-Konformität).', 'beitrittserklaerung' );
		}

		foreach ( $notices as $notice ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html( $notice )
			);
		}
	}

	/**
	 * Registriert Admin-Menüeinträge.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Beitrittserklärung', 'beitrittserklaerung' ),
			__( 'Beitrittserklärung', 'beitrittserklaerung' ),
			'manage_options',
			'bse-submissions',
			array( $this, 'render_submissions_page' ),
			'dashicons-id-alt',
			58
		);

		add_submenu_page(
			'bse-submissions',
			__( 'Einreichungen', 'beitrittserklaerung' ),
			__( 'Einreichungen', 'beitrittserklaerung' ),
			'manage_options',
			'bse-submissions',
			array( $this, 'render_submissions_page' )
		);

		add_submenu_page(
			'bse-submissions',
			__( 'Einstellungen', 'beitrittserklaerung' ),
			__( 'Einstellungen', 'beitrittserklaerung' ),
			'manage_options',
			'bse-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Lädt Medien-Uploader auf Einstellungsseite.
	 *
	 * @param string $hook Aktuelle Admin-Seite.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'bse-submissions_page_bse-settings' !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'bse-admin-settings',
			BSE_URL . 'admin/js/settings.js',
			array( 'jquery' ),
			BSE_VERSION,
			true
		);
	}

	/**
	 * Speichert Einstellungen.
	 */
	public function handle_settings_save(): void {
		if ( ! isset( $_POST['bse_settings_nonce'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bse_settings_nonce'] ) ), 'bse_save_settings' ) ) {
			return;
		}

		$settings = BSE_Settings::get();

		$settings['recipient_email']      = sanitize_email( wp_unslash( $_POST['recipient_email'] ?? '' ) );
		$settings['verein_name']          = sanitize_text_field( wp_unslash( $_POST['verein_name'] ?? '' ) );
		$settings['verein_address']         = sanitize_textarea_field( wp_unslash( $_POST['verein_address'] ?? '' ) );
		$settings['pdf_logo_id']            = absint( $_POST['pdf_logo_id'] ?? 0 );
		$settings['privacy_page_id']        = absint( $_POST['privacy_page_id'] ?? 0 );
		$settings['consent_text']           = sanitize_textarea_field( wp_unslash( $_POST['consent_text'] ?? '' ) );
		$settings['sepa_mandate_text']      = sanitize_textarea_field( wp_unslash( $_POST['sepa_mandate_text'] ?? '' ) );
		$settings['mail_subject_admin']     = sanitize_text_field( wp_unslash( $_POST['mail_subject_admin'] ?? '' ) );
		$settings['mail_subject_user']      = sanitize_text_field( wp_unslash( $_POST['mail_subject_user'] ?? '' ) );
		$settings['mail_body_admin']        = sanitize_textarea_field( wp_unslash( $_POST['mail_body_admin'] ?? '' ) );
		$settings['mail_body_user']         = sanitize_textarea_field( wp_unslash( $_POST['mail_body_user'] ?? '' ) );
		$settings['thank_you_message']      = sanitize_textarea_field( wp_unslash( $_POST['thank_you_message'] ?? '' ) );
		$settings['turnstile_site_key']     = sanitize_text_field( wp_unslash( $_POST['turnstile_site_key'] ?? '' ) );
		$settings['turnstile_secret_key']   = sanitize_text_field( wp_unslash( $_POST['turnstile_secret_key'] ?? '' ) );
		$settings['rate_limit_per_hour']    = max( 1, absint( $_POST['rate_limit_per_hour'] ?? 3 ) );

		$field_defs = BSE_Field_Definitions::get_fields();
		$fields     = array();

		foreach ( $field_defs as $key => $default ) {
			$visible  = isset( $_POST['fields'][ $key ]['visible'] );
			$required = $visible && isset( $_POST['fields'][ $key ]['required'] );

			$fields[ $key ] = array(
				'visible'  => $visible,
				'required' => $required,
				'label'    => sanitize_text_field( wp_unslash( $_POST['fields'][ $key ]['label'] ?? $default['label'] ) ),
				'order'    => absint( $_POST['fields'][ $key ]['order'] ?? $default['order'] ),
			);
		}

		$settings['fields'] = $fields;

		BSE_Settings::update( $settings );

		add_settings_error( 'bse_settings', 'bse_saved', __( 'Einstellungen gespeichert.', 'beitrittserklaerung' ), 'success' );
	}

	/**
	 * Einreichungsliste oder Detailansicht.
	 */
	public function render_submissions_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$submission_id = isset( $_GET['submission'] ) ? absint( $_GET['submission'] ) : 0;

		if ( $submission_id > 0 ) {
			$submission = BSE_Submission_Repository::get_by_id( $submission_id );
			if ( ! $submission ) {
				wp_die( esc_html__( 'Einreichung nicht gefunden.', 'beitrittserklaerung' ) );
			}
			include BSE_PATH . 'admin/views/submission-detail.php';
			return;
		}

		$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$result   = BSE_Submission_Repository::list( $page, 20 );
		$items    = $result['items'];
		$total    = $result['total'];
		$per_page = 20;
		$pages    = (int) ceil( $total / $per_page );

		include BSE_PATH . 'admin/views/submissions-list.php';
	}

	/**
	 * Einstellungsseite.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = BSE_Settings::get();
		$fields     = BSE_Field_Definitions::get_merged_fields();
		$clusters   = BSE_Field_Definitions::get_clusters();

		include BSE_PATH . 'admin/views/settings-page.php';
	}
}
