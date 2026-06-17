<?php
/**
 * Datenbankzugriff für Einreichungen (nur Lesen/Schreiben beim Submit).
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repository für Formulareinreichungen.
 */
class BSE_Submission_Repository {

	public const DB_VERSION = '1.0.0';

	/**
	 * Tabellenname mit Präfix.
	 */
	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'bse_submissions';
	}

	/**
	 * Erstellt die Datenbanktabelle.
	 */
	public static function create_table(): void {
		global $wpdb;

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL,
			ip_address varchar(45) NOT NULL DEFAULT '',
			user_agent text NULL,
			form_data longtext NOT NULL,
			mail_status varchar(20) NOT NULL DEFAULT 'pending',
			consent_text_hash varchar(64) NOT NULL DEFAULT '',
			privacy_page_id bigint(20) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'bse_db_version', self::DB_VERSION );
	}

	/**
	 * Speichert eine neue Einreichung.
	 *
	 * @param array<string, mixed> $data Einreichungsdaten.
	 * @return int|false Insert-ID oder false.
	 */
	public static function insert( array $data ) {
		global $wpdb;

		$result = $wpdb->insert(
			self::table_name(),
			array(
				'created_at'        => current_time( 'mysql' ),
				'ip_address'        => $data['ip_address'] ?? '',
				'user_agent'        => $data['user_agent'] ?? '',
				'form_data'         => wp_json_encode( $data['form_data'] ?? array() ),
				'mail_status'       => $data['mail_status'] ?? 'pending',
				'consent_text_hash' => $data['consent_text_hash'] ?? '',
				'privacy_page_id'     => (int) ( $data['privacy_page_id'] ?? 0 ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d' )
		);

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Liest eine Einreichung anhand der ID.
	 *
	 * @param int $id Einreichungs-ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_by_id( int $id ): ?array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table_name() . ' WHERE id = %d',
				$id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		return self::hydrate_row( $row );
	}

	/**
	 * Listet Einreichungen paginiert.
	 *
	 * @param int $page     Seite (1-basiert).
	 * @param int $per_page Einträge pro Seite.
	 * @return array{items: array<int, array<string, mixed>>, total: int}
	 */
	public static function list( int $page = 1, int $per_page = 20 ): array {
		global $wpdb;

		$table  = self::table_name();
		$offset = max( 0, ( $page - 1 ) * $per_page );

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$items = array_map( array( self::class, 'hydrate_row' ), $rows ?: array() );

		return array(
			'items' => $items,
			'total' => $total,
		);
	}

	/**
	 * Löscht Einreichungen älter als die Aufbewahrungsfrist.
	 *
	 * @return int Anzahl gelöschter Datensätze.
	 */
	public static function delete_older_than_months( int $months ): int {
		global $wpdb;

		$threshold = gmdate( 'Y-m-d H:i:s', strtotime( "-{$months} months" ) );

		return (int) $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM ' . self::table_name() . ' WHERE created_at < %s',
				$threshold
			)
		);
	}

	/**
	 * Dekodiert JSON und formatiert Zeile.
	 *
	 * @param array<string, mixed> $row DB-Zeile.
	 * @return array<string, mixed>
	 */
	private static function hydrate_row( array $row ): array {
		$form_data = json_decode( $row['form_data'] ?? '{}', true );
		if ( ! is_array( $form_data ) ) {
			$form_data = array();
		}

		$row['form_data'] = $form_data;

		return $row;
	}
}
