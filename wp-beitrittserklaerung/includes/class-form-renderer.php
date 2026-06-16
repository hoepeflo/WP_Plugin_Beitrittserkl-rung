<?php
/**
 * Frontend-Formular-Rendering.
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rendert das Beitrittserklärungs-Formular.
 */
class BSE_Form_Renderer {

	/**
	 * Shortcode-Callback.
	 *
	 * @param array<string, string> $atts Attribute.
	 */
	public static function render_shortcode( array $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'class' => '',
			),
			$atts,
			'beitrittserklaerung'
		);

		return self::render( array( 'className' => $atts['class'] ) );
	}

	/**
	 * Rendert das Formular.
	 *
	 * @param array<string, mixed> $attributes Block-/Modul-Attribute.
	 */
	public static function render( array $attributes = array() ): string {
		$settings     = BSE_Settings::get();
		$clusters     = BSE_Field_Definitions::get_visible_fields_by_cluster();
		$form_id      = 'bse-form-' . wp_unique_id();
		$class_name   = 'bse-form';
		$extra_class  = isset( $attributes['className'] ) ? sanitize_html_class( $attributes['className'] ) : '';

		if ( $extra_class ) {
			$class_name .= ' ' . $extra_class;
		}

		ob_start();
		?>
		<div class="bse-form-wrapper" id="<?php echo esc_attr( $form_id ); ?>-wrapper">
			<form
				class="<?php echo esc_attr( $class_name ); ?>"
				id="<?php echo esc_attr( $form_id ); ?>"
				method="post"
				novalidate
				data-bse-form="1"
			>
				<?php wp_nonce_field( 'bse_submit_form', 'bse_nonce' ); ?>
				<input type="hidden" name="action" value="bse_submit_form" />
				<input type="text" name="bse_hp_field" value="" class="bse-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />

				<div class="bse-form-messages" role="alert" aria-live="polite" hidden></div>

				<?php foreach ( $clusters as $cluster_key => $cluster ) : ?>
					<?php if ( empty( $cluster['fields'] ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>

					<fieldset class="bse-cluster bse-cluster--<?php echo esc_attr( $cluster_key ); ?>">
						<legend class="bse-cluster__legend"><?php echo esc_html( $cluster['label'] ); ?></legend>

						<?php foreach ( $cluster['fields'] as $field_key => $field ) : ?>
							<?php self::render_field( $field_key, $field, $settings ); ?>
						<?php endforeach; ?>
					</fieldset>
				<?php endforeach; ?>

				<?php if ( ! empty( $settings['turnstile_site_key'] ) ) : ?>
					<div class="bse-field bse-field--turnstile">
						<div
							class="cf-turnstile"
							data-sitekey="<?php echo esc_attr( $settings['turnstile_site_key'] ); ?>"
							data-theme="auto"
						></div>
					</div>
				<?php endif; ?>

				<p class="bse-form-actions">
					<button type="submit" class="bse-submit button">
						<?php esc_html_e( 'Beitrittserklärung absenden', 'beitrittserklaerung' ); ?>
					</button>
				</p>
			</form>

			<div class="bse-thank-you" hidden>
				<p><?php echo esc_html( $settings['thank_you_message'] ); ?></p>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Rendert ein einzelnes Feld.
	 *
	 * @param string               $key      Feldschlüssel.
	 * @param array<string, mixed> $field    Feldkonfiguration.
	 * @param array<string, mixed> $settings Plugin-Einstellungen.
	 */
	private static function render_field( string $key, array $field, array $settings ): void {
		$type     = $field['type'] ?? 'text';
		$label    = $field['label'] ?? $key;
		$required = ! empty( $field['required'] );
		$field_id = 'bse-field-' . $key;

		?>
		<div class="bse-field bse-field--<?php echo esc_attr( $key ); ?> bse-field--<?php echo esc_attr( $type ); ?>">
			<?php if ( 'sepa_checkbox' === $type ) : ?>
				<?php
				$mandate_text = BSE_Settings::replace_placeholders(
					(string) ( $settings['sepa_mandate_text'] ?? BSE_Settings::default_sepa_mandate_text() ),
					array(),
					$settings
				);
				?>
				<label for="<?php echo esc_attr( $field_id ); ?>" class="bse-checkbox-label">
					<input
						type="checkbox"
						name="<?php echo esc_attr( $key ); ?>"
						id="<?php echo esc_attr( $field_id ); ?>"
						value="1"
						<?php echo $required ? 'required' : ''; ?>
					/>
					<span class="bse-checkbox-text"><?php echo esc_html( $mandate_text ); ?></span>
				</label>
			<?php elseif ( 'privacy_checkbox' === $type ) : ?>
				<?php
				$privacy_url  = BSE_Settings::get_privacy_page_url();
				$consent_text = (string) ( $settings['consent_text'] ?? '' );
				?>
				<label for="<?php echo esc_attr( $field_id ); ?>" class="bse-checkbox-label">
					<input
						type="checkbox"
						name="<?php echo esc_attr( $key ); ?>"
						id="<?php echo esc_attr( $field_id ); ?>"
						value="1"
						<?php echo $required ? 'required' : ''; ?>
					/>
					<span class="bse-checkbox-text">
						<?php echo esc_html( $consent_text ); ?>
						<?php if ( $privacy_url ) : ?>
							<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Datenschutzerklärung', 'beitrittserklaerung' ); ?>
							</a>
						<?php endif; ?>
					</span>
				</label>
			<?php else : ?>
				<label for="<?php echo esc_attr( $field_id ); ?>">
					<?php echo esc_html( $label ); ?>
					<?php if ( $required ) : ?>
						<span class="bse-required" aria-hidden="true">*</span>
					<?php endif; ?>
				</label>

				<?php if ( 'select' === $type ) : ?>
					<select
						name="<?php echo esc_attr( $key ); ?>"
						id="<?php echo esc_attr( $field_id ); ?>"
						<?php echo $required ? 'required' : ''; ?>
					>
						<option value=""><?php esc_html_e( 'Bitte wählen', 'beitrittserklaerung' ); ?></option>
						<?php foreach ( (array) ( $field['options'] ?? array() ) as $option_value => $option_label ) : ?>
							<option value="<?php echo esc_attr( $option_value ); ?>">
								<?php echo esc_html( $option_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input
						type="<?php echo esc_attr( self::input_type( $type ) ); ?>"
						name="<?php echo esc_attr( $key ); ?>"
						id="<?php echo esc_attr( $field_id ); ?>"
						value="<?php echo esc_attr( (string) ( $field['default'] ?? '' ) ); ?>"
						<?php echo $required ? 'required' : ''; ?>
						<?php echo 'date' === $type ? 'max="9999-12-31"' : ''; ?>
					/>
				<?php endif; ?>
			<?php endif; ?>

			<span class="bse-field-error" data-field="<?php echo esc_attr( $key ); ?>" hidden></span>
		</div>
		<?php
	}

	/**
	 * Mappt Feldtypen auf HTML-Input-Typen.
	 */
	private static function input_type( string $type ): string {
		$map = array(
			'email' => 'email',
			'tel'   => 'tel',
			'date'  => 'date',
		);

		return $map[ $type ] ?? 'text';
	}
}
