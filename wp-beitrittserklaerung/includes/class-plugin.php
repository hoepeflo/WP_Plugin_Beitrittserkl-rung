<?php
/**
 * Zentrale Plugin-Initialisierung.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once BSE_PATH . 'admin/class-admin.php';
require_once BSE_PATH . 'public/class-public.php';

/**
 * Haupt-Plugin-Klasse (Singleton).
 */
class BSE_Plugin {

	/**
	 * Instanz.
	 *
	 * @var BSE_Plugin|null
	 */
	private static ?BSE_Plugin $instance = null;

	/**
	 * Gibt die Singleton-Instanz zurück.
	 */
	public static function instance(): BSE_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Konstruktor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'init' ) );

		if ( is_admin() ) {
			new BSE_Admin();
		}

		new BSE_Public();
	}

	/**
	 * Lädt Übersetzungen.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			BSE_TEXTDOMAIN,
			false,
			dirname( plugin_basename( BSE_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Registriert Blöcke und Shortcode.
	 */
	public function init(): void {
		add_shortcode( 'beitrittserklaerung', array( 'BSE_Form_Renderer', 'render_shortcode' ) );

		if ( function_exists( 'register_block_type' ) ) {
			register_block_type( BSE_PATH . 'blocks/gutenberg' );
		}

		if ( $this->is_divi5_available() ) {
			require_once BSE_PATH . 'blocks/divi5/class-divi-module.php';
			add_action( 'et_builder_ready', array( 'BSE_Divi_Module', 'register' ) );
		}
	}

	/**
	 * Prüft, ob Divi 5 oder neuer verfügbar ist.
	 */
	private function is_divi5_available(): bool {
		if ( ! class_exists( 'ET_Builder_Module' ) ) {
			return false;
		}

		if ( defined( 'ET_BUILDER_VERSION' ) ) {
			return version_compare( ET_BUILDER_VERSION, '5.0', '>=' );
		}

		$theme = wp_get_theme();
		if ( 'Divi' === $theme->get( 'Name' ) || 'Divi' === $theme->get_template() ) {
			$version = $theme->get( 'Version' );
			return $version && version_compare( $version, '5.0', '>=' );
		}

		return false;
	}
}
