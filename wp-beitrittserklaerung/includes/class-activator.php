<?php
/**
 * Plugin-Aktivierung.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Aktivierungs-Hooks.
 */
class BSE_Activator {

	/**
	 * Führt Aktivierungsschritte aus.
	 */
	public static function activate(): void {
		BSE_Submission_Repository::create_table();
		BSE_Settings::set_defaults();
		BSE_GDPR::schedule_cleanup();

		flush_rewrite_rules();
	}
}
