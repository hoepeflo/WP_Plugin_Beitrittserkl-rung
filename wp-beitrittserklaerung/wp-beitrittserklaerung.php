<?php
/**
 * Plugin Name:       Beitrittserklärung
 * Plugin URI:        https://github.com/example/wp-beitrittserklaerung
 * Description:       DSGVO-konformes Formular für Vereins-Beitrittserklärungen mit PDF-Versand und Backend-Archiv.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Verein
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       beitrittserklaerung
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BSE_VERSION', '1.0.0' );
define( 'BSE_PLUGIN_FILE', __FILE__ );
define( 'BSE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BSE_URL', plugin_dir_url( __FILE__ ) );
define( 'BSE_TEXTDOMAIN', 'beitrittserklaerung' );
define( 'BSE_RETENTION_MONTHS', 12 );

require_once BSE_PATH . 'includes/class-activator.php';
require_once BSE_PATH . 'includes/class-deactivator.php';
require_once BSE_PATH . 'includes/class-field-definitions.php';
require_once BSE_PATH . 'includes/class-settings.php';
require_once BSE_PATH . 'includes/class-submission-repository.php';
require_once BSE_PATH . 'includes/class-form-validator.php';
require_once BSE_PATH . 'includes/class-pdf-generator.php';
require_once BSE_PATH . 'includes/class-mailer.php';
require_once BSE_PATH . 'includes/class-form-renderer.php';
require_once BSE_PATH . 'includes/class-submission-handler.php';
require_once BSE_PATH . 'includes/class-gdpr.php';
require_once BSE_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'BSE_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'BSE_Deactivator', 'deactivate' ) );

BSE_Plugin::instance();
