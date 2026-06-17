<?php
/**
 * DIVI 5 Modul für Beitrittserklärung.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DIVI-Builder-Modul.
 */
class BSE_Divi_Module extends ET_Builder_Module {

	/**
	 * Modul-Slug.
	 *
	 * @var string
	 */
	public $slug = 'bse_beitrittserklaerung';

	/**
	 * Partial VB support – SSR via AJAX für komplexes Formular.
	 *
	 * @var string
	 */
	public $vb_support = 'partial';

	/**
	 * Modul initialisieren.
	 */
	public function init(): void {
		$this->name             = esc_html__( 'Beitrittserklärung', 'beitrittserklaerung' );
		$this->plural           = esc_html__( 'Beitrittserklärungen', 'beitrittserklaerung' );
		$this->icon_path        = '';
		$this->main_css_element = '%%order_class%%';

		$this->settings_modal_toggles = array(
			'general' => array(
				'toggles' => array(
					'main_content' => esc_html__( 'Inhalt', 'beitrittserklaerung' ),
				),
			),
		);
	}

	/**
	 * Modulfelder im Builder.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_fields(): array {
		return array(
			'css_class' => array(
				'label'           => esc_html__( 'Zusätzliche CSS-Klasse', 'beitrittserklaerung' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'toggle_slug'     => 'main_content',
			),
		);
	}

	/**
	 * Frontend- und SSR-Ausgabe.
	 *
	 * @param array<string, mixed> $attrs Attribute.
	 * @param string|null          $content Inhalt.
	 * @param string|null          $render_slug Slug.
	 */
	public function render( $attrs, $content = null, $render_slug = null ): string {
		$css_class = isset( $attrs['css_class'] ) ? sanitize_html_class( $attrs['css_class'] ) : '';

		add_filter( 'bse_enqueue_form_assets', '__return_true' );

		$output = BSE_Form_Renderer::render(
			array(
				'className' => $css_class,
			)
		);

		remove_filter( 'bse_enqueue_form_assets', '__return_true' );

		return $output;
	}

	/**
	 * Registriert das Modul beim Builder.
	 */
	public static function register(): void {
		if ( ! class_exists( 'ET_Builder_Module' ) ) {
			return;
		}

		new self();
	}
}
