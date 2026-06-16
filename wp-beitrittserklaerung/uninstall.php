<?php
/**
 * Deinstallation des Plugins.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$table = $wpdb->prefix . 'bse_submissions';
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

delete_option( 'bse_settings' );
delete_option( 'bse_db_version' );

wp_clear_scheduled_hook( 'bse_cleanup_old_submissions' );
