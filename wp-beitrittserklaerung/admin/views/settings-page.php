<?php
/**
 * Admin: Einstellungen.
 *
 * @var array<string, mixed> $settings
 * @var array<string, array<string, mixed>> $fields
 * @var array<string, array{label: string, order: int}> $clusters
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

settings_errors( 'bse_settings' );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Beitrittserklärung – Einstellungen', 'beitrittserklaerung' ); ?></h1>

	<div class="notice notice-info">
		<p><?php echo esc_html( BSE_GDPR::retention_notice() ); ?></p>
	</div>

	<form method="post" action="">
		<?php wp_nonce_field( 'bse_save_settings', 'bse_settings_nonce' ); ?>

		<h2 class="title"><?php esc_html_e( 'Allgemein', 'beitrittserklaerung' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="recipient_email"><?php esc_html_e( 'Empfänger-E-Mail (Verein)', 'beitrittserklaerung' ); ?></label></th>
				<td><input type="email" class="regular-text" id="recipient_email" name="recipient_email" value="<?php echo esc_attr( (string) $settings['recipient_email'] ); ?>" required /></td>
			</tr>
			<tr>
				<th scope="row"><label for="verein_name"><?php esc_html_e( 'Vereinsname', 'beitrittserklaerung' ); ?></label></th>
				<td><input type="text" class="regular-text" id="verein_name" name="verein_name" value="<?php echo esc_attr( (string) $settings['verein_name'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="verein_address"><?php esc_html_e( 'Vereinsadresse (PDF-Fußzeile)', 'beitrittserklaerung' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="verein_address" name="verein_address"><?php echo esc_textarea( (string) $settings['verein_address'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'PDF-Logo', 'beitrittserklaerung' ); ?></th>
				<td>
					<input type="hidden" id="pdf_logo_id" name="pdf_logo_id" value="<?php echo esc_attr( (string) (int) $settings['pdf_logo_id'] ); ?>" />
					<button type="button" class="button" id="bse-select-logo"><?php esc_html_e( 'Logo auswählen', 'beitrittserklaerung' ); ?></button>
					<button type="button" class="button" id="bse-remove-logo"><?php esc_html_e( 'Entfernen', 'beitrittserklaerung' ); ?></button>
					<div id="bse-logo-preview" style="margin-top:8px;">
						<?php
						$logo_id = (int) $settings['pdf_logo_id'];
						if ( $logo_id ) {
							echo wp_get_attachment_image( $logo_id, 'medium' );
						}
						?>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="thank_you_message"><?php esc_html_e( 'Danke-Meldung', 'beitrittserklaerung' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="thank_you_message" name="thank_you_message"><?php echo esc_textarea( (string) $settings['thank_you_message'] ); ?></textarea></td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Datenschutz (Pflicht)', 'beitrittserklaerung' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="privacy_page_id"><?php esc_html_e( 'Seite mit Datenschutzerklärung', 'beitrittserklaerung' ); ?></label></th>
				<td>
					<?php
					wp_dropdown_pages(
						array(
							'name'              => 'privacy_page_id',
							'id'                => 'privacy_page_id',
							'selected'          => (int) $settings['privacy_page_id'],
							'show_option_none'  => __( '— Bitte wählen —', 'beitrittserklaerung' ),
							'option_none_value' => '0',
						)
					);
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="consent_text"><?php esc_html_e( 'Einwilligungstext', 'beitrittserklaerung' ); ?></label></th>
				<td><textarea class="large-text" rows="4" id="consent_text" name="consent_text" required><?php echo esc_textarea( (string) $settings['consent_text'] ); ?></textarea></td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'SEPA-Mandat', 'beitrittserklaerung' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sepa_mandate_text"><?php esc_html_e( 'Mandatstext', 'beitrittserklaerung' ); ?></label></th>
				<td>
					<textarea class="large-text" rows="5" id="sepa_mandate_text" name="sepa_mandate_text"><?php echo esc_textarea( (string) $settings['sepa_mandate_text'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Platzhalter: {verein}', 'beitrittserklaerung' ); ?></p>
				</td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'E-Mail', 'beitrittserklaerung' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Der Versand erfolgt über wp_mail() und ist kompatibel mit WP Mail SMTP.', 'beitrittserklaerung' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="mail_subject_admin"><?php esc_html_e( 'Betreff (Verein)', 'beitrittserklaerung' ); ?></label></th>
				<td><input type="text" class="large-text" id="mail_subject_admin" name="mail_subject_admin" value="<?php echo esc_attr( (string) $settings['mail_subject_admin'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="mail_body_admin"><?php esc_html_e( 'Nachricht (Verein)', 'beitrittserklaerung' ); ?></label></th>
				<td><textarea class="large-text" rows="4" id="mail_body_admin" name="mail_body_admin"><?php echo esc_textarea( (string) $settings['mail_body_admin'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="mail_subject_user"><?php esc_html_e( 'Betreff (Antragsteller)', 'beitrittserklaerung' ); ?></label></th>
				<td><input type="text" class="large-text" id="mail_subject_user" name="mail_subject_user" value="<?php echo esc_attr( (string) $settings['mail_subject_user'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="mail_body_user"><?php esc_html_e( 'Nachricht (Antragsteller)', 'beitrittserklaerung' ); ?></label></th>
				<td><textarea class="large-text" rows="4" id="mail_body_user" name="mail_body_user"><?php echo esc_textarea( (string) $settings['mail_body_user'] ); ?></textarea></td>
			</tr>
		</table>
		<p class="description"><?php esc_html_e( 'Platzhalter: {verein}, {vorname}, {name}, {email}, {eintrittsdatum}', 'beitrittserklaerung' ); ?></p>

		<h2 class="title"><?php esc_html_e( 'Captcha (Cloudflare Turnstile)', 'beitrittserklaerung' ); ?></h2>
		<div class="notice notice-info inline">
			<p><?php esc_html_e( 'Cloudflare Turnstile ist kostenfrei, werbefrei, datenschutzfreundlich und belastet die Seite nur minimal. Schlüssel erhalten Sie unter dash.cloudflare.com → Turnstile.', 'beitrittserklaerung' ); ?></p>
		</div>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="turnstile_site_key"><?php esc_html_e( 'Site Key', 'beitrittserklaerung' ); ?></label></th>
				<td><input type="text" class="regular-text" id="turnstile_site_key" name="turnstile_site_key" value="<?php echo esc_attr( (string) $settings['turnstile_site_key'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="turnstile_secret_key"><?php esc_html_e( 'Secret Key', 'beitrittserklaerung' ); ?></label></th>
				<td><input type="text" class="regular-text" id="turnstile_secret_key" name="turnstile_secret_key" value="<?php echo esc_attr( (string) $settings['turnstile_secret_key'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="rate_limit_per_hour"><?php esc_html_e( 'Max. Absendungen pro Stunde (pro IP)', 'beitrittserklaerung' ); ?></label></th>
				<td><input type="number" min="1" id="rate_limit_per_hour" name="rate_limit_per_hour" value="<?php echo esc_attr( (string) (int) $settings['rate_limit_per_hour'] ); ?>" /></td>
			</tr>
		</table>

		<h2 class="title"><?php esc_html_e( 'Formularfelder', 'beitrittserklaerung' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Alle Felder sind standardmäßig optional. Aktivieren Sie „Pflicht“ nur für die Felder, die zwingend ausgefüllt werden müssen. Ausgeblendete Felder können nicht Pflichtfeld sein.', 'beitrittserklaerung' ); ?>
		</p>

		<?php foreach ( $clusters as $cluster_key => $cluster ) : ?>
			<h3><?php echo esc_html( $cluster['label'] ); ?></h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Feld', 'beitrittserklaerung' ); ?></th>
						<th><?php esc_html_e( 'Label', 'beitrittserklaerung' ); ?></th>
						<th><?php esc_html_e( 'Sichtbar', 'beitrittserklaerung' ); ?></th>
						<th><?php esc_html_e( 'Pflicht', 'beitrittserklaerung' ); ?></th>
						<th><?php esc_html_e( 'Reihenfolge', 'beitrittserklaerung' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $fields as $field_key => $field ) : ?>
						<?php if ( ( $field['cluster'] ?? '' ) !== $cluster_key ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<tr>
							<td><code><?php echo esc_html( $field_key ); ?></code></td>
							<td>
								<input
									type="text"
									name="fields[<?php echo esc_attr( $field_key ); ?>][label]"
									value="<?php echo esc_attr( (string) $field['label'] ); ?>"
									class="regular-text"
								/>
							</td>
							<td>
								<input
									type="checkbox"
									class="bse-field-visible"
									name="fields[<?php echo esc_attr( $field_key ); ?>][visible]"
									value="1"
									<?php checked( ! empty( $field['visible'] ) ); ?>
								/>
							</td>
							<td>
								<input
									type="checkbox"
									class="bse-field-required"
									name="fields[<?php echo esc_attr( $field_key ); ?>][required]"
									value="1"
									<?php checked( ! empty( $field['required'] ) ); ?>
									<?php disabled( empty( $field['visible'] ) ); ?>
								/>
							</td>
							<td>
								<input
									type="number"
									name="fields[<?php echo esc_attr( $field_key ); ?>][order]"
									value="<?php echo esc_attr( (string) (int) ( $field['order'] ?? 0 ) ); ?>"
									min="0"
									style="width:70px"
								/>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endforeach; ?>

		<?php submit_button( __( 'Einstellungen speichern', 'beitrittserklaerung' ) ); ?>
	</form>
</div>
