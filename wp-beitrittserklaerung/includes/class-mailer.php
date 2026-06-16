<?php
/**
 * E-Mail-Versand via wp_mail (kompatibel mit WP Mail SMTP).
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Versendet PDF-Anhänge an Verein und Antragsteller.
 */
class BSE_Mailer {

	/**
	 * Sendet E-Mails an Verein und Antragsteller.
	 *
	 * @param array<string, mixed> $data      Formulardaten.
	 * @param string               $pdf_path  Pfad zur PDF-Datei.
	 * @return string Status: sent, partial, failed.
	 */
	public static function send( array $data, string $pdf_path ): string {
		$settings = BSE_Settings::get();
		$sent     = 0;
		$attempts = 0;

		$admin_email = sanitize_email( (string) ( $settings['recipient_email'] ?? '' ) );
		$user_email  = sanitize_email( (string) ( $data['email'] ?? '' ) );

		if ( $admin_email ) {
			++$attempts;
			$subject = BSE_Settings::replace_placeholders( (string) $settings['mail_subject_admin'], $data, $settings );
			$body    = nl2br( esc_html( BSE_Settings::replace_placeholders( (string) $settings['mail_body_admin'], $data, $settings ) ) );

			if ( self::send_single( $admin_email, $subject, $body, $pdf_path ) ) {
				++$sent;
			}
		}

		if ( $user_email ) {
			++$attempts;
			$subject = BSE_Settings::replace_placeholders( (string) $settings['mail_subject_user'], $data, $settings );
			$body    = nl2br( esc_html( BSE_Settings::replace_placeholders( (string) $settings['mail_body_user'], $data, $settings ) ) );

			if ( self::send_single( $user_email, $subject, $body, $pdf_path ) ) {
				++$sent;
			}
		}

		if ( 0 === $attempts ) {
			return 'failed';
		}

		if ( $sent === $attempts ) {
			return 'sent';
		}

		if ( $sent > 0 ) {
			return 'partial';
		}

		return 'failed';
	}

	/**
	 * Sendet eine einzelne E-Mail mit PDF-Anhang.
	 */
	private static function send_single( string $to, string $subject, string $body, string $pdf_path ): bool {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$attachments = array();

		if ( $pdf_path && file_exists( $pdf_path ) ) {
			$attachments[] = $pdf_path;
		}

		return (bool) wp_mail( $to, $subject, $body, $headers, $attachments );
	}
}
