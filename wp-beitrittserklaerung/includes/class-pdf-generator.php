<?php
/**
 * PDF-Generierung für Beitrittserklärungen (TCPDF).
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Erstellt PDF-Dokumente aus Formulardaten.
 */
class BSE_PDF_Generator {

	/**
	 * Erzeugt eine PDF-Datei und gibt den temporären Pfad zurück.
	 *
	 * @param array<string, mixed> $data Formulardaten.
	 * @return string|WP_Error Dateipfad oder Fehler.
	 */
	public static function generate( array $data ) {
		$tcpdf_path = BSE_PATH . 'lib/tcpdf/tcpdf.php';
		if ( ! file_exists( $tcpdf_path ) ) {
			return new WP_Error(
				'bse_pdf_missing',
				__( 'PDF-Bibliothek nicht verfügbar.', 'beitrittserklaerung' )
			);
		}

		require_once $tcpdf_path;

		$settings = BSE_Settings::get();
		$html     = self::render_html( $data, $settings );

		try {
			$pdf = new TCPDF( 'P', 'mm', 'A4', true, 'UTF-8', false );
			$pdf->SetCreator( 'Beitrittserklaerung Plugin' );
			$pdf->SetAuthor( (string) ( $settings['verein_name'] ?? '' ) );
			$pdf->SetTitle( __( 'Beitrittserklärung', 'beitrittserklaerung' ) );
			$pdf->setPrintHeader( false );
			$pdf->setPrintFooter( false );
			$pdf->SetMargins( 15, 15, 15 );
			$pdf->SetAutoPageBreak( true, 15 );
			$pdf->AddPage();
			$pdf->writeHTML( $html, true, false, true, false, '' );

			$upload_dir = wp_upload_dir();
			$filename   = self::build_filename( $data );
			$filepath   = trailingslashit( $upload_dir['basedir'] ) . 'bse-temp/' . $filename;

			wp_mkdir_p( dirname( $filepath ) );
			$pdf->Output( $filepath, 'F' );

			return $filepath;
		} catch ( Exception $e ) {
			return new WP_Error( 'bse_pdf_error', $e->getMessage() );
		}
	}

	/**
	 * Baut HTML für TCPDF.
	 *
	 * @param array<string, mixed> $data     Formulardaten.
	 * @param array<string, mixed> $settings Einstellungen.
	 */
	private static function render_html( array $data, array $settings ): string {
		$fields   = BSE_Field_Definitions::get_merged_fields();
		$clusters = BSE_Field_Definitions::get_clusters();

		$grouped = array();
		foreach ( $fields as $key => $field ) {
			if ( empty( $field['visible'] ) ) {
				continue;
			}
			$cluster = $field['cluster'] ?? 'other';
			if ( ! isset( $grouped[ $cluster ] ) ) {
				$grouped[ $cluster ] = array();
			}
			$grouped[ $cluster ][ $key ] = $field;
		}

		$logo_html = '';
		$logo_id   = (int) ( $settings['pdf_logo_id'] ?? 0 );
		if ( $logo_id > 0 ) {
			$logo_path = get_attached_file( $logo_id );
			if ( $logo_path && file_exists( $logo_path ) ) {
				$logo_html = '<div style="text-align:center;margin-bottom:16px;"><img src="' . esc_attr( $logo_path ) . '" style="max-height:70px;" /></div>';
			}
		}

		ob_start();
		include BSE_PATH . 'templates/pdf-beitrittserklaerung.php';
		return (string) ob_get_clean();
	}

	/**
	 * Formatiert Feldwert für PDF-Ausgabe.
	 *
	 * @param string               $key   Feldschlüssel.
	 * @param array<string, mixed> $field Feldkonfiguration.
	 * @param array<string, mixed> $data  Formulardaten.
	 */
	public static function format_value( string $key, array $field, array $data ): string {
		$value = $data[ $key ] ?? '';

		if ( 'geschlecht' === $key ) {
			return BSE_Field_Definitions::get_geschlecht_label( (string) $value );
		}

		if ( in_array( $field['type'] ?? '', array( 'sepa_checkbox', 'privacy_checkbox' ), true ) ) {
			return '1' === (string) $value
				? __( 'Ja, zugestimmt', 'beitrittserklaerung' )
				: __( 'Nein', 'beitrittserklaerung' );
		}

		return (string) $value;
	}

	/**
	 * Dateiname für PDF-Anhang.
	 *
	 * @param array<string, mixed> $data Formulardaten.
	 */
	private static function build_filename( array $data ): string {
		$vorname = sanitize_file_name( (string) ( $data['vorname'] ?? 'antrag' ) );
		$name    = sanitize_file_name( (string) ( $data['name'] ?? '' ) );
		$date    = gmdate( 'Y-m-d' );

		return "beitrittserklaerung-{$vorname}-{$name}-{$date}.pdf";
	}

	/**
	 * Löscht temporäre PDF-Datei.
	 */
	public static function cleanup( string $filepath ): void {
		if ( $filepath && file_exists( $filepath ) ) {
			wp_delete_file( $filepath );
		}
	}
}
