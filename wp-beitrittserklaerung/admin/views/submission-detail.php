<?php
/**
 * Admin: Einreichungsdetail (nur Lesen).
 *
 * @var array<string, mixed> $submission
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data         = $submission['form_data'] ?? array();
$fields       = BSE_Field_Definitions::get_merged_fields();
$clusters     = BSE_Field_Definitions::get_clusters();
$list_url     = admin_url( 'admin.php?page=bse-submissions' );
$status_labels = array(
	'sent'    => __( 'Gesendet', 'beitrittserklaerung' ),
	'partial' => __( 'Teilweise gesendet', 'beitrittserklaerung' ),
	'failed'  => __( 'Fehlgeschlagen', 'beitrittserklaerung' ),
	'pending' => __( 'Ausstehend', 'beitrittserklaerung' ),
);
?>
<div class="wrap">
	<h1>
		<?php
		printf(
			/* translators: %d: submission ID */
			esc_html__( 'Einreichung #%d', 'beitrittserklaerung' ),
			(int) $submission['id']
		);
		?>
	</h1>

	<p><a href="<?php echo esc_url( $list_url ); ?>">&larr; <?php esc_html_e( 'Zurück zur Übersicht', 'beitrittserklaerung' ); ?></a></p>

	<h2><?php esc_html_e( 'Metadaten', 'beitrittserklaerung' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php esc_html_e( 'Eingegangen am', 'beitrittserklaerung' ); ?></th>
			<td><?php echo esc_html( mysql2date( 'd.m.Y H:i', $submission['created_at'] ) ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'IP-Adresse', 'beitrittserklaerung' ); ?></th>
			<td><?php echo esc_html( (string) ( $submission['ip_address'] ?? '' ) ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'User-Agent', 'beitrittserklaerung' ); ?></th>
			<td><code><?php echo esc_html( (string) ( $submission['user_agent'] ?? '' ) ); ?></code></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'E-Mail-Status', 'beitrittserklaerung' ); ?></th>
			<td><?php echo esc_html( $status_labels[ $submission['mail_status'] ?? 'pending' ] ?? '' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Datenschutzseite (ID)', 'beitrittserklaerung' ); ?></th>
			<td><?php echo esc_html( (string) (int) ( $submission['privacy_page_id'] ?? 0 ) ); ?></td>
		</tr>
	</table>

	<?php foreach ( $clusters as $cluster_key => $cluster ) : ?>
		<?php
		$cluster_fields = array_filter(
			$fields,
			static function ( $field ) use ( $cluster_key ) {
				return ( $field['cluster'] ?? '' ) === $cluster_key && ! empty( $field['visible'] );
			}
		);
		if ( empty( $cluster_fields ) ) {
			continue;
		}
		?>
		<h2><?php echo esc_html( $cluster['label'] ); ?></h2>
		<table class="widefat striped">
			<tbody>
				<?php foreach ( $cluster_fields as $field_key => $field ) : ?>
					<?php
					$value = $data[ $field_key ] ?? '';
					if ( 'geschlecht' === $field_key ) {
						$value = BSE_Field_Definitions::get_geschlecht_label( (string) $value );
					} elseif ( in_array( $field['type'] ?? '', array( 'sepa_checkbox', 'privacy_checkbox' ), true ) ) {
						$value = '1' === (string) $value ? __( 'Ja', 'beitrittserklaerung' ) : __( 'Nein', 'beitrittserklaerung' );
					}
					?>
					<tr>
						<th style="width:30%"><?php echo esc_html( (string) $field['label'] ); ?></th>
						<td><?php echo esc_html( (string) $value ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
</div>
