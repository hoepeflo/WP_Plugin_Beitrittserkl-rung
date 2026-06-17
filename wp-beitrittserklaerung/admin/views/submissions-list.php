<?php
/**
 * Admin: Einreichungsliste.
 *
 * @var array<int, array<string, mixed>> $items
 * @var int $total
 * @var int $page
 * @var int $pages
 *
 * @package Beitrittserklaerung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_labels = array(
	'sent'    => __( 'Gesendet', 'beitrittserklaerung' ),
	'partial' => __( 'Teilweise gesendet', 'beitrittserklaerung' ),
	'failed'  => __( 'Fehlgeschlagen', 'beitrittserklaerung' ),
	'pending' => __( 'Ausstehend', 'beitrittserklaerung' ),
);
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Beitrittserklärungen', 'beitrittserklaerung' ); ?></h1>

	<div class="notice notice-info">
		<p><?php echo esc_html( BSE_GDPR::retention_notice() ); ?></p>
	</div>

	<p><?php esc_html_e( 'Alle Einreichungen sind schreibgeschützt (nur Lesen).', 'beitrittserklaerung' ); ?></p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'beitrittserklaerung' ); ?></th>
				<th><?php esc_html_e( 'Datum', 'beitrittserklaerung' ); ?></th>
				<th><?php esc_html_e( 'Name', 'beitrittserklaerung' ); ?></th>
				<th><?php esc_html_e( 'E-Mail', 'beitrittserklaerung' ); ?></th>
				<th><?php esc_html_e( 'Eintrittsdatum', 'beitrittserklaerung' ); ?></th>
				<th><?php esc_html_e( 'E-Mail-Status', 'beitrittserklaerung' ); ?></th>
				<th><?php esc_html_e( 'IP-Adresse', 'beitrittserklaerung' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $items ) ) : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'Noch keine Einreichungen vorhanden.', 'beitrittserklaerung' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $items as $item ) : ?>
					<?php
					$data   = $item['form_data'] ?? array();
					$status = $item['mail_status'] ?? 'pending';
					$url    = admin_url( 'admin.php?page=bse-submissions&submission=' . (int) $item['id'] );
					?>
					<tr>
						<td><a href="<?php echo esc_url( $url ); ?>">#<?php echo esc_html( (string) $item['id'] ); ?></a></td>
						<td><?php echo esc_html( mysql2date( 'd.m.Y H:i', $item['created_at'] ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( $url ); ?>">
								<?php echo esc_html( trim( ( $data['vorname'] ?? '' ) . ' ' . ( $data['name'] ?? '' ) ) ); ?>
							</a>
						</td>
						<td><?php echo esc_html( (string) ( $data['email'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $data['eintrittsdatum'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( $status_labels[ $status ] ?? $status ); ?></td>
						<td><?php echo esc_html( (string) ( $item['ip_address'] ?? '' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $pages > 1 ) : ?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total'     => $pages,
							'current'   => $page,
						)
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
