<?php
/**
 * Felddefinitionen und Cluster.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Statische Feld- und Cluster-Definitionen.
 */
class BSE_Field_Definitions {

	/**
	 * Gibt alle Cluster mit Metadaten zurück.
	 *
	 * @return array<string, array{label: string, order: int}>
	 */
	public static function get_clusters(): array {
		return array(
			'personal'   => array(
				'label' => __( 'Persönliche Daten', 'beitrittserklaerung' ),
				'order' => 10,
			),
			'address'    => array(
				'label' => __( 'Anschrift', 'beitrittserklaerung' ),
				'order' => 20,
			),
			'contact'    => array(
				'label' => __( 'Kontakt', 'beitrittserklaerung' ),
				'order' => 30,
			),
			'membership' => array(
				'label' => __( 'Mitgliedschaft', 'beitrittserklaerung' ),
				'order' => 40,
			),
			'sepa'       => array(
				'label' => __( 'SEPA-Lastschrift', 'beitrittserklaerung' ),
				'order' => 50,
			),
			'privacy'    => array(
				'label' => __( 'Datenschutz', 'beitrittserklaerung' ),
				'order' => 60,
			),
		);
	}

	/**
	 * Gibt alle Formularfelder zurück.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_fields(): array {
		return array(
			'titel'              => array(
				'cluster'  => 'personal',
				'label'    => __( 'Titel', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 10,
			),
			'vorname'            => array(
				'cluster'  => 'personal',
				'label'    => __( 'Vorname', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 20,
			),
			'name'               => array(
				'cluster'  => 'personal',
				'label'    => __( 'Name', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 30,
			),
			'geburtsdatum'       => array(
				'cluster'  => 'personal',
				'label'    => __( 'Geburtsdatum', 'beitrittserklaerung' ),
				'type'     => 'date',
				'visible'  => true,
				'required' => false,
				'order'    => 40,
			),
			'geburtsort'         => array(
				'cluster'  => 'personal',
				'label'    => __( 'Geburtsort', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 50,
			),
			'geschlecht'         => array(
				'cluster'  => 'personal',
				'label'    => __( 'Geschlecht', 'beitrittserklaerung' ),
				'type'     => 'select',
				'options'  => array(
					'maennlich'     => __( 'Männlich', 'beitrittserklaerung' ),
					'weiblich'      => __( 'Weiblich', 'beitrittserklaerung' ),
					'divers'        => __( 'Divers', 'beitrittserklaerung' ),
					'keine_angabe'  => __( 'Keine Angabe', 'beitrittserklaerung' ),
				),
				'visible'  => true,
				'required' => false,
				'order'    => 60,
			),
			'strasse'            => array(
				'cluster'  => 'address',
				'label'    => __( 'Straße und Hausnummer', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 10,
			),
			'plz'                => array(
				'cluster'  => 'address',
				'label'    => __( 'PLZ', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 20,
			),
			'ort'                => array(
				'cluster'  => 'address',
				'label'    => __( 'Ort', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 30,
			),
			'land'               => array(
				'cluster'  => 'address',
				'label'    => __( 'Land', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'default'  => __( 'Deutschland', 'beitrittserklaerung' ),
				'order'    => 40,
			),
			'email'              => array(
				'cluster'  => 'contact',
				'label'    => __( 'E-Mail-Adresse', 'beitrittserklaerung' ),
				'type'     => 'email',
				'visible'  => true,
				'required' => false,
				'order'    => 10,
			),
			'mobiltelefon'       => array(
				'cluster'  => 'contact',
				'label'    => __( 'Mobiltelefon', 'beitrittserklaerung' ),
				'type'     => 'tel',
				'visible'  => true,
				'required' => false,
				'order'    => 20,
			),
			'festnetztelefon'    => array(
				'cluster'  => 'contact',
				'label'    => __( 'Festnetztelefon', 'beitrittserklaerung' ),
				'type'     => 'tel',
				'visible'  => true,
				'required' => false,
				'order'    => 30,
			),
			'eintrittsdatum'     => array(
				'cluster'  => 'membership',
				'label'    => __( 'Gewünschtes Eintrittsdatum', 'beitrittserklaerung' ),
				'type'     => 'date',
				'visible'  => true,
				'required' => false,
				'order'    => 10,
			),
			'kontoinhaber'       => array(
				'cluster'  => 'sepa',
				'label'    => __( 'Kontoinhaber', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 10,
			),
			'iban'               => array(
				'cluster'  => 'sepa',
				'label'    => __( 'IBAN', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 20,
			),
			'bic'                => array(
				'cluster'  => 'sepa',
				'label'    => __( 'BIC (nur bei ausländischen Konten)', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 30,
			),
			'institut'           => array(
				'cluster'  => 'sepa',
				'label'    => __( 'Kreditinstitut', 'beitrittserklaerung' ),
				'type'     => 'text',
				'visible'  => true,
				'required' => false,
				'order'    => 40,
			),
			'sepa_mandat'        => array(
				'cluster'  => 'sepa',
				'label'    => __( 'SEPA-Lastschriftmandat', 'beitrittserklaerung' ),
				'type'     => 'sepa_checkbox',
				'visible'  => true,
				'required' => true,
				'order'    => 50,
			),
			'datenschutz'        => array(
				'cluster'  => 'privacy',
				'label'    => __( 'Datenschutzeinwilligung', 'beitrittserklaerung' ),
				'type'     => 'privacy_checkbox',
				'visible'  => true,
				'required' => true,
				'order'    => 10,
			),
		);
	}

	/**
	 * Liefert zusammengeführte Feldkonfiguration aus Defaults und gespeicherten Einstellungen.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_merged_fields(): array {
		$defaults = self::get_fields();
		$settings = BSE_Settings::get();
		$stored   = $settings['fields'] ?? array();

		foreach ( $defaults as $key => $field ) {
			if ( ! isset( $stored[ $key ] ) || ! is_array( $stored[ $key ] ) ) {
				continue;
			}

			$field['visible']  = ! empty( $stored[ $key ]['visible'] );
			$field['required'] = ! empty( $stored[ $key ]['required'] );
			$field['label']    = (string) ( $stored[ $key ]['label'] ?? $field['label'] );
			$field['order']    = (int) ( $stored[ $key ]['order'] ?? $field['order'] );

			if ( ! $field['visible'] ) {
				$field['required'] = false;
			}

			$defaults[ $key ] = $field;
		}

		return $defaults;
	}

	/**
	 * Nur sichtbare Felder, nach Cluster gruppiert.
	 *
	 * @return array<string, array<string, array<string, mixed>>>
	 */
	public static function get_visible_fields_by_cluster(): array {
		$fields   = self::get_merged_fields();
		$clusters = self::get_clusters();
		$grouped  = array();

		foreach ( $clusters as $cluster_key => $cluster ) {
			$grouped[ $cluster_key ] = array(
				'label'  => $cluster['label'],
				'order'  => $cluster['order'],
				'fields' => array(),
			);
		}

		foreach ( $fields as $key => $field ) {
			if ( empty( $field['visible'] ) ) {
				continue;
			}

			$cluster_key = $field['cluster'];
			if ( ! isset( $grouped[ $cluster_key ] ) ) {
				continue;
			}

			$field['key'] = $key;
			$grouped[ $cluster_key ]['fields'][ $key ] = $field;
		}

		foreach ( $grouped as $cluster_key => $cluster ) {
			uasort(
				$grouped[ $cluster_key ]['fields'],
				static function ( $a, $b ) {
					return ( $a['order'] ?? 0 ) <=> ( $b['order'] ?? 0 );
				}
			);
		}

		uasort(
			$grouped,
			static function ( $a, $b ) {
				return ( $a['order'] ?? 0 ) <=> ( $b['order'] ?? 0 );
			}
		);

		return $grouped;
	}

	/**
	 * Label für Geschlecht-Option.
	 *
	 * @param string $value Optionsschlüssel.
	 */
	public static function get_geschlecht_label( string $value ): string {
		$field   = self::get_fields()['geschlecht'];
		$options = $field['options'] ?? array();

		return $options[ $value ] ?? $value;
	}
}
