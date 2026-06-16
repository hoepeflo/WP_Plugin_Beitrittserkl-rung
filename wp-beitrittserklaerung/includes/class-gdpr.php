<?php
/**
 * DSGVO-Funktionen: Aufbewahrung, Hinweise.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Datenschutz- und Aufbewahrungslogik.
 */
class BSE_GDPR {

	/**
	 * Cron-Hook-Name.
	 */
	public const CLEANUP_HOOK = 'bse_cleanup_old_submissions';

	/**
	 * Registriert Cron und Handler.
	 */
	public static function init(): void {
		add_action( self::CLEANUP_HOOK, array( self::class, 'run_cleanup' ) );
	}

	/**
	 * Plant täglichen Cleanup-Job.
	 */
	public static function schedule_cleanup(): void {
		if ( ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::CLEANUP_HOOK );
		}
	}

	/**
	 * Löscht abgelaufene Einreichungen.
	 */
	public static function run_cleanup(): void {
		BSE_Submission_Repository::delete_older_than_months( BSE_RETENTION_MONTHS );
	}

	/**
	 * Hinweistext für Backend (Aufbewahrung).
	 */
	public static function retention_notice(): string {
		return sprintf(
			/* translators: %d: number of months */
			__( 'Personenbezogene Daten aus Beitrittserklärungen werden aus Datenschutzgründen automatisch nach %d Monaten gelöscht. Die IP-Adresse wird zu Nachweiszwecken bis zur Löschung gespeichert.', 'beitrittserklaerung' ),
			BSE_RETENTION_MONTHS
		);
	}
}

BSE_GDPR::init();
