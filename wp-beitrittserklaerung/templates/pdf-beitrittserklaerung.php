<?php
/**
 * PDF-Template für Beitrittserklärungen.
 *
 * @var array<string, mixed> $settings
 * @var array<string, array<string, mixed>> $grouped
 * @var array<string, mixed> $data
 * @var array<string, array{label: string, order: int}> $clusters
 * @var string $logo_html
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8" />
	<style>
		body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #222; }
		h1 { font-size: 18pt; margin-bottom: 8px; }
		h2 { font-size: 13pt; margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
		table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
		td { padding: 6px 8px; vertical-align: top; border-bottom: 1px solid #eee; }
		td.label { width: 38%; font-weight: bold; }
		.footer { margin-top: 30px; font-size: 9pt; color: #666; }
		.meta { font-size: 9pt; color: #666; margin-bottom: 20px; }
	</style>
</head>
<body>
	<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<h1><?php esc_html_e( 'Beitrittserklärung', 'beitrittserklaerung' ); ?></h1>
	<p class="meta">
		<strong><?php echo esc_html( (string) ( $settings['verein_name'] ?? '' ) ); ?></strong><br />
		<?php esc_html_e( 'Erstellt am:', 'beitrittserklaerung' ); ?>
		<?php echo esc_html( wp_date( 'd.m.Y H:i' ) ); ?>
	</p>

	<?php foreach ( $clusters as $cluster_key => $cluster ) : ?>
		<?php if ( empty( $grouped[ $cluster_key ] ) ) : ?>
			<?php continue; ?>
		<?php endif; ?>

		<h2><?php echo esc_html( $cluster['label'] ); ?></h2>
		<table>
			<?php foreach ( $grouped[ $cluster_key ] as $field_key => $field ) : ?>
				<?php if ( in_array( $field['type'] ?? '', array( 'sepa_checkbox', 'privacy_checkbox' ), true ) ) : ?>
					<tr>
						<td class="label" colspan="2">
							<?php echo esc_html( BSE_PDF_Generator::format_value( $field_key, $field, $data ) ); ?>
						</td>
					</tr>
					<?php if ( 'sepa_checkbox' === ( $field['type'] ?? '' ) ) : ?>
						<tr>
							<td colspan="2" style="font-size: 9pt;">
								<?php
								echo esc_html(
									BSE_Settings::replace_placeholders(
										(string) ( $settings['sepa_mandate_text'] ?? '' ),
										$data,
										$settings
									)
								);
								?>
							</td>
						</tr>
					<?php endif; ?>
				<?php else : ?>
					<tr>
						<td class="label"><?php echo esc_html( (string) ( $field['label'] ?? $field_key ) ); ?></td>
						<td><?php echo esc_html( BSE_PDF_Generator::format_value( $field_key, $field, $data ) ); ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</table>
	<?php endforeach; ?>

	<?php if ( ! empty( $settings['verein_address'] ) ) : ?>
		<div class="footer">
			<?php echo nl2br( esc_html( (string) $settings['verein_address'] ) ); ?>
		</div>
	<?php endif; ?>

	<p class="footer">
		<?php esc_html_e( 'Dieses Dokument wurde elektronisch übermittelt.', 'beitrittserklaerung' ); ?>
	</p>
</body>
</html>
