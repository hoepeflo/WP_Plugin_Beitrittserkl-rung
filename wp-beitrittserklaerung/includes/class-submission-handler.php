<?php
/**
 * Verarbeitung von Formular-Einreichungen.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX- und Submit-Handler.
 */
class BSE_Submission_Handler {

	/**
	 * Registriert AJAX-Hooks.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_bse_submit_form', array( self::class, 'handle' ) );
		add_action( 'wp_ajax_nopriv_bse_submit_form', array( self::class, 'handle' ) );
	}

	/**
	 * Verarbeitet Formular-Absendung.
	 */
	public static function handle(): void {
		if ( ! isset( $_POST['bse_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bse_nonce'] ) ), 'bse_submit_form' ) ) {
			self::json_error( __( 'Sicherheitsprüfung fehlgeschlagen. Bitte laden Sie die Seite neu.', 'beitrittserklaerung' ) );
		}

		if ( ! empty( $_POST['bse_hp_field'] ) ) {
			self::json_error( __( 'Übermittlung abgelehnt.', 'beitrittserklaerung' ) );
		}

		if ( BSE_Form_Validator::is_rate_limited() ) {
			self::json_error( __( 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.', 'beitrittserklaerung' ) );
		}

		$turnstile_token = isset( $_POST['cf-turnstile-response'] )
			? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) )
			: '';

		if ( ! BSE_Form_Validator::validate_turnstile( $turnstile_token ) ) {
			self::json_error( __( 'Captcha-Prüfung fehlgeschlagen. Bitte versuchen Sie es erneut.', 'beitrittserklaerung' ) );
		}

		$input      = wp_unslash( $_POST );
		$validation = BSE_Form_Validator::validate( is_array( $input ) ? $input : array() );

		if ( ! $validation['valid'] ) {
			wp_send_json_error(
				array(
					'message' => __( 'Bitte korrigieren Sie die markierten Felder.', 'beitrittserklaerung' ),
					'errors'  => $validation['errors'],
				)
			);
		}

		$data     = $validation['data'];
		$settings = BSE_Settings::get();

		$pdf_path = BSE_PDF_Generator::generate( $data );
		if ( is_wp_error( $pdf_path ) ) {
			self::json_error( $pdf_path->get_error_message() );
		}

		$mail_status = BSE_Mailer::send( $data, $pdf_path );

		$submission_id = BSE_Submission_Repository::insert(
			array(
				'ip_address'        => BSE_Form_Validator::get_client_ip(),
				'user_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] )
					? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
					: '',
				'form_data'         => $data,
				'mail_status'       => $mail_status,
				'consent_text_hash' => hash( 'sha256', (string) ( $settings['consent_text'] ?? '' ) ),
				'privacy_page_id'   => (int) ( $settings['privacy_page_id'] ?? 0 ),
			)
		);

		BSE_PDF_Generator::cleanup( $pdf_path );
		BSE_Form_Validator::increment_rate_limit();

		if ( false === $submission_id ) {
			self::json_error( __( 'Die Einreichung konnte nicht gespeichert werden.', 'beitrittserklaerung' ) );
		}

		wp_send_json_success(
			array(
				'message' => $settings['thank_you_message'],
			)
		);
	}

	/**
	 * Sendet JSON-Fehlerantwort.
	 */
	private static function json_error( string $message ): void {
		wp_send_json_error( array( 'message' => $message ) );
	}
}

BSE_Submission_Handler::register();
