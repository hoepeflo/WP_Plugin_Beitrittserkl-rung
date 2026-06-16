<?php
/**
 * Öffentliche Hooks und Assets.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend-Integration.
 */
class BSE_Public {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Lädt Skripte nur wenn Formular auf der Seite ist.
	 */
	public function enqueue_assets(): void {
		if ( ! $this->page_has_form() ) {
			return;
		}

		$settings = BSE_Settings::get();

		wp_enqueue_style(
			'bse-form',
			BSE_URL . 'public/css/form.css',
			array(),
			BSE_VERSION
		);

		wp_enqueue_script(
			'bse-form',
			BSE_URL . 'public/js/form.js',
			array(),
			BSE_VERSION,
			true
		);

		wp_localize_script(
			'bse-form',
			'bseForm',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'submitError' => __( 'Beim Senden ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.', 'beitrittserklaerung' ),
				),
			)
		);

		if ( ! empty( $settings['turnstile_site_key'] ) ) {
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				array(),
				null,
				true
			);
		}
	}

	/**
	 * Prüft ob aktuelle Seite das Formular enthält.
	 */
	private function page_has_form(): bool {
		if ( is_singular() ) {
			$post = get_post();
			if ( $post && (
				has_shortcode( $post->post_content, 'beitrittserklaerung' )
				|| has_block( 'beitrittserklaerung/form', $post )
				|| false !== strpos( $post->post_content, 'bse_beitrittserklaerung' )
			) ) {
				return true;
			}
		}

		return (bool) apply_filters( 'bse_enqueue_form_assets', false );
	}
}
