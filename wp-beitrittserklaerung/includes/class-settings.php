<?php
/**
 * Plugin-Einstellungen.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verwaltet Plugin-Optionen.
 */
class BSE_Settings {

	public const OPTION_KEY = 'bse_settings';

	/**
	 * Standard-SEPA-Mandatstext.
	 */
	public static function default_sepa_mandate_text(): string {
		return __(
			'Ich ermächtige hiermit den Verein {verein}, die von mir zu entrichtenden Zahlungen bei Fälligkeit von meinem Konto mittels Lastschrift einzuziehen. Ich weise mein Kreditinstitut an, die von Verein {verein} auf mein Konto gezogenen Lastschriften einzulösen. Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrags verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.',
			'beitrittserklaerung'
		);
	}

	/**
	 * Setzt Standardwerte bei Erstinstallation.
	 */
	public static function set_defaults(): void {
		if ( false !== get_option( self::OPTION_KEY, false ) ) {
			return;
		}

		update_option( self::OPTION_KEY, self::get_defaults() );
	}

	/**
	 * Standardkonfiguration.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults(): array {
		$fields = array();
		foreach ( BSE_Field_Definitions::get_fields() as $key => $field ) {
			$fields[ $key ] = array(
				'visible'  => $field['visible'],
				'required' => $field['required'],
				'label'    => $field['label'],
				'order'    => $field['order'],
			);
		}

		return array(
			'recipient_email'      => get_option( 'admin_email' ),
			'verein_name'          => get_bloginfo( 'name' ),
			'verein_address'       => '',
			'pdf_logo_id'          => 0,
			'privacy_page_id'      => 0,
			'consent_text'         => __(
				'Ich willige ein, dass meine Angaben zur Bearbeitung meines Mitgliedschaftsantrags elektronisch erhoben und verarbeitet werden. Die Daten werden ausschließlich für diesen Zweck verwendet und nach Ablauf der gesetzlichen Aufbewahrungsfrist gelöscht. Weitere Informationen finden Sie in unserer Datenschutzerklärung.',
				'beitrittserklaerung'
			),
			'sepa_mandate_text'    => self::default_sepa_mandate_text(),
			'mail_subject_admin'   => __( 'Neue Beitrittserklärung: {vorname} {name}', 'beitrittserklaerung' ),
			'mail_subject_user'    => __( 'Ihre Beitrittserklärung – Kopie', 'beitrittserklaerung' ),
			'mail_body_admin'      => __( "Es liegt eine neue Beitrittserklärung vor.\n\nName: {vorname} {name}\nE-Mail: {email}\nEintrittsdatum: {eintrittsdatum}", 'beitrittserklaerung' ),
			'mail_body_user'       => __( "Vielen Dank für Ihre Beitrittserklärung.\n\nAnbei erhalten Sie eine Kopie Ihrer Angaben als PDF.\n\nMit freundlichen Grüßen\n{verein}", 'beitrittserklaerung' ),
			'thank_you_message'    => __( 'Vielen Dank! Ihre Beitrittserklärung wurde erfolgreich übermittelt. Sie erhalten in Kürze eine Bestätigung per E-Mail.', 'beitrittserklaerung' ),
			'turnstile_site_key'   => '',
			'turnstile_secret_key' => '',
			'rate_limit_per_hour'  => 3,
			'fields'               => $fields,
		);
	}

	/**
	 * Liest Einstellungen mit Fallback auf Defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return array_replace_recursive( self::get_defaults(), $stored );
	}

	/**
	 * Speichert Einstellungen.
	 *
	 * @param array<string, mixed> $settings Neue Werte.
	 */
	public static function update( array $settings ): void {
		$current = self::get();
		$merged  = array_replace_recursive( $current, $settings );
		update_option( self::OPTION_KEY, $merged );
	}

	/**
	 * Ersetzt Platzhalter in Texten.
	 *
	 * @param string               $text     Vorlage.
	 * @param array<string, mixed> $data     Formulardaten.
	 * @param array<string, mixed> $settings Einstellungen.
	 */
	public static function replace_placeholders( string $text, array $data, ?array $settings = null ): string {
		$settings = $settings ?? self::get();

		$replacements = array(
			'{verein}'         => (string) ( $settings['verein_name'] ?? '' ),
			'{vorname}'        => (string) ( $data['vorname'] ?? '' ),
			'{name}'           => (string) ( $data['name'] ?? '' ),
			'{email}'          => (string) ( $data['email'] ?? '' ),
			'{eintrittsdatum}' => (string) ( $data['eintrittsdatum'] ?? '' ),
		);

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $text );
	}

	/**
	 * URL der Datenschutzseite.
	 */
	public static function get_privacy_page_url(): string {
		$settings = self::get();
		$page_id  = (int) ( $settings['privacy_page_id'] ?? 0 );

		if ( $page_id > 0 ) {
			$url = get_permalink( $page_id );
			if ( $url ) {
				return $url;
			}
		}

		return '';
	}
}
