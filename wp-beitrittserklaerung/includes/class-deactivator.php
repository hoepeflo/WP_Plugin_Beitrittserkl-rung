<?php
/**
 * Plugin-Deaktivierung.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deaktivierungs-Hooks.
 */
class BSE_Deactivator {

	/**
	 * Entfernt geplante Cron-Jobs.
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'bse_cleanup_old_submissions' );
	}
}
