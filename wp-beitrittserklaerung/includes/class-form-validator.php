<?php
/**
 * Server-seitige Formularvalidierung.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validiert Formulareingaben.
 */
class BSE_Form_Validator {

	/**
	 * Validiert alle sichtbaren Felder.
	 *
	 * @param array<string, mixed> $input Rohe POST-Daten.
	 * @return array{valid: bool, errors: array<string, string>, data: array<string, mixed>}
	 */
	public static function validate( array $input ): array {
		$fields = BSE_Field_Definitions::get_merged_fields();
		$errors = array();
		$data   = array();

		foreach ( $fields as $key => $field ) {
			if ( empty( $field['visible'] ) ) {
				continue;
			}

			$type     = $field['type'] ?? 'text';
			$required = ! empty( $field['required'] );
			$raw      = $input[ $key ] ?? '';

			if ( in_array( $type, array( 'sepa_checkbox', 'privacy_checkbox' ), true ) ) {
				$value = ! empty( $raw ) ? '1' : '';
			} else {
				$value = is_string( $raw ) ? trim( wp_unslash( $raw ) ) : '';
			}

			if ( $required && '' === $value && '0' !== $value ) {
				$errors[ $key ] = sprintf(
					/* translators: %s: field label */
					__( 'Das Feld „%s“ ist erforderlich.', 'beitrittserklaerung' ),
					$field['label']
				);
				continue;
			}

			if ( '' === $value && ! $required ) {
				$data[ $key ] = '';
				continue;
			}

			switch ( $type ) {
				case 'email':
					$sanitized = sanitize_email( $value );
					if ( ! is_email( $sanitized ) ) {
						$errors[ $key ] = __( 'Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'beitrittserklaerung' );
					} else {
						$data[ $key ] = $sanitized;
					}
					break;

				case 'date':
					if ( ! self::is_valid_date( $value ) ) {
						$errors[ $key ] = sprintf(
							/* translators: %s: field label */
							__( '„%s“ muss ein gültiges Datum sein.', 'beitrittserklaerung' ),
							$field['label']
						);
					} else {
						$data[ $key ] = $value;
					}
					break;

				case 'select':
					$options = $field['options'] ?? array();
					if ( ! array_key_exists( $value, $options ) ) {
						$errors[ $key ] = sprintf(
							/* translators: %s: field label */
							__( '„%s“ enthält eine ungültige Auswahl.', 'beitrittserklaerung' ),
							$field['label']
						);
					} else {
						$data[ $key ] = $value;
					}
					break;

				case 'sepa_checkbox':
				case 'privacy_checkbox':
					if ( $required && '1' !== $value ) {
						$errors[ $key ] = sprintf(
							/* translators: %s: field label */
							__( 'Bitte bestätigen Sie: %s', 'beitrittserklaerung' ),
							$field['label']
						);
					} else {
						$data[ $key ] = $value;
					}
					break;

				default:
					$data[ $key ] = sanitize_text_field( $value );
					break;
			}
		}

		if ( isset( $data['iban'] ) && '' !== $data['iban'] && ! self::validate_iban( $data['iban'] ) ) {
			$errors['iban'] = __( 'Bitte geben Sie eine gültige IBAN ein.', 'beitrittserklaerung' );
		}

		if ( isset( $data['bic'] ) && '' !== $data['bic'] && ! self::validate_bic( $data['bic'] ) ) {
			$errors['bic'] = __( 'Bitte geben Sie eine gültige BIC ein.', 'beitrittserklaerung' );
		}

		if ( isset( $data['plz'] ) && '' !== $data['plz'] && ! preg_match( '/^\d{4,10}$/', $data['plz'] ) ) {
			$errors['plz'] = __( 'Bitte geben Sie eine gültige PLZ ein.', 'beitrittserklaerung' );
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
			'data'   => $data,
		);
	}

	/**
	 * Prüft Datumsformat YYYY-MM-DD.
	 */
	private static function is_valid_date( string $value ): bool {
		$date = DateTime::createFromFormat( 'Y-m-d', $value );
		return $date && $date->format( 'Y-m-d' ) === $value;
	}

	/**
	 * IBAN-Prüfung (MOD-97).
	 */
	public static function validate_iban( string $iban ): bool {
		$iban = strtoupper( preg_replace( '/\s+/', '', $iban ) );

		if ( strlen( $iban ) < 15 || strlen( $iban ) > 34 ) {
			return false;
		}

		if ( ! preg_match( '/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban ) ) {
			return false;
		}

		$rearranged = substr( $iban, 4 ) . substr( $iban, 0, 4 );
		$numeric    = '';

		for ( $i = 0, $len = strlen( $rearranged ); $i < $len; $i++ ) {
			$char = $rearranged[ $i ];
			if ( ctype_alpha( $char ) ) {
				$numeric .= (string) ( ord( $char ) - 55 );
			} else {
				$numeric .= $char;
			}
		}

		$checksum = 0;
		for ( $i = 0, $len = strlen( $numeric ); $i < $len; $i++ ) {
			$checksum = ( $checksum * 10 + (int) $numeric[ $i ] ) % 97;
		}

		return 1 === $checksum;
	}

	/**
	 * BIC-Prüfung (SWIFT).
	 */
	public static function validate_bic( string $bic ): bool {
		$bic = strtoupper( preg_replace( '/\s+/', '', $bic ) );
		return (bool) preg_match( '/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $bic );
	}

	/**
	 * Validiert Cloudflare Turnstile-Token.
	 *
	 * @param string $token Turnstile-Antwort.
	 */
	public static function validate_turnstile( string $token ): bool {
		$settings = BSE_Settings::get();
		$secret   = trim( (string) ( $settings['turnstile_secret_key'] ?? '' ) );

		if ( '' === $secret ) {
			return true;
		}

		if ( '' === $token ) {
			return false;
		}

		$response = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'timeout' => 15,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => self::get_client_ip(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return ! empty( $body['success'] );
	}

	/**
	 * Ermittelt Client-IP.
	 */
	public static function get_client_ip(): string {
		$headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			$ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );
			$ip      = trim( $ip_list[0] );

			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		return '';
	}

	/**
	 * Rate-Limiting pro IP.
	 */
	public static function is_rate_limited(): bool {
		$settings = BSE_Settings::get();
		$limit    = max( 1, (int) ( $settings['rate_limit_per_hour'] ?? 3 ) );
		$ip       = self::get_client_ip();

		if ( '' === $ip ) {
			return false;
		}

		$key   = 'bse_rate_' . md5( $ip );
		$count = (int) get_transient( $key );

		return $count >= $limit;
	}

	/**
	 * Erhöht Rate-Limit-Zähler.
	 */
	public static function increment_rate_limit(): void {
		$ip = self::get_client_ip();
		if ( '' === $ip ) {
			return;
		}

		$key   = 'bse_rate_' . md5( $ip );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	}
}
