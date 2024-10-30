<?php if ( $order instanceof \WC_Order ): ?>
    <?php if ( ! empty( $courier ) ): ?>
        <p>
            <?= __( 'Wybrany kurier przez użytkownika:', 'korporacja-kurierska' ); ?><br/>
            <strong><?= \Korporacja\API::COURIERS[ $courier ]; ?></strong>
        </p>
    <?php endif; ?>
	<?php if ( ! empty( $machine ) ): ?>
        <p>
			<?= __( 'Wybrany punkt dostarczenia:', 'korporacja-kurierska' ); ?><br/>
            <strong><?= $machine; ?></strong>
        </p>
	<?php endif; ?>
    <h3><?= __( 'Zamówienie złożone', 'korporacja-kurierska' ); ?></h3>
    <strong><?= __( 'Numer zamówienia w systemie:', 'korporacja-kurierska' ); ?> <?= esc_html($korporacja_order_id); ?></strong>
    <h3><?= __( 'Szczegóły zamówienia', 'korporacja-kurierska' ); ?></h3>
	<?php if ( ! empty( $korporacja_order_detail ) ): ?>
        <p>
			<?= __( 'Kurier', 'korporacja-kurierska' ); ?><br/>
            <strong><?= esc_html($korporacja_order_detail['courier']); ?></strong>
        </p>
        <p>
			<?= __( 'Kwota', 'korporacja-kurierska' ); ?><br/>
            <strong><?= esc_html($korporacja_order_detail['grossPrice']); ?>PLN
                <small>(brutto)</small>
            </strong>
        </p>
        <p>
			<?= __( 'Status', 'korporacja-kurierska' ); ?><br/>
            <strong><?= esc_html($korporacja_order_detail['orderStatus']); ?></strong>
        </p>
        <p>
			<?= __( 'Data złożenia zamówienia', 'korporacja-kurierska' ); ?><br/>
            <strong><?= esc_html($korporacja_order_detail['date']); ?></strong>
        </p>
        <p>
			<?= __( 'Status przesyłki', 'korporacja-kurierska' ); ?><br/>
            <strong><?= esc_html($korporacja_order_detail['packageStatus']); ?></strong>
        </p>
		<?php if ( ! empty( $korporacja_order_detail['cod'] ) ): ?>
            <p>
				<?= __( 'Kwota pobrania', 'korporacja-kurierska' ); ?><br/>
                <strong><?= esc_html($korporacja_order_detail['codAmount']); ?>PLN</strong>
            </p>
            <p>
				<?= __( 'Konto bankowe na które ma zostać przelana kwota pobrania', 'korporacja-kurierska' ); ?><br/>
                <strong><?= esc_html($korporacja_order_detail['codBankAccount']); ?></strong>
            </p>
            <p>
				<?= __( 'Planowana data zwrotu pobrania', 'korporacja-kurierska' ); ?><br/>
                <strong><?= ! empty( $korporacja_order_detail['codPayOutDate'] ) ? esc_html($korporacja_order_detail['codPayOutDate']) : __( 'Brak danych', 'korporacja-kurierska' ); ?></strong>
            </p>
		<?php endif; ?>
		<?php $list = implode( ', ', (array) $korporacja_order_detail['labelNumber'] ); ?>
		<?php if ( ! empty( $list ) ): ?>
            <p>
				<?= __( 'Numer listu przewozowego', 'korporacja-kurierska' ); ?><br/>
                <strong><?= $list; ?></strong>
            </p>
		<?php endif; ?>
		<?php if ( ! empty( $korporacja_order_detail['labelAvailable'] ) ): ?>
            <p>
                <a href="<?= admin_url( 'admin-ajax.php?action=korporacja_get_label&order_id=' . $order->get_id() ); ?>"
                   class="button button-primary">
					<?= __( 'Pobierz etykietę przewozową', 'korporacja-kurierska' ); ?>
                </a>
            </p>
		<?php endif; ?>
		<?php if ( ! empty( $korporacja_order_detail['labelZebraAvailable'] ) ): ?>
            <p>
                <a href="<?= admin_url( 'admin-ajax.php?action=korporacja_get_label&order_id=' . $order->get_id() . '&type=zebra' ); ?>"
                   class="button button-primary">
					<?= __( 'Pobierz etykietę przewozową Zebra', 'korporacja-kurierska' ); ?>
                </a>
            </p>
		<?php endif; ?>
		<?php if ( ! empty( $korporacja_order_detail['protocolAvailable'] ) ): ?>
            <p>
                <a href="<?= admin_url( 'admin-ajax.php?action=korporacja_get_label&order_id=' . $order->get_id() . '&type=protocol' ); ?>"
                   class="button button-primary">
					<?= __( 'Protokół - Paczka w Ruchu', 'korporacja-kurierska' ); ?>
                </a>
            </p>
		<?php endif; ?>
		<?php if ( ! empty( trim( $korporacja_order_detail['authorizationDocumentAvailable'] ) ) ): ?>
            <p>
                <a href="<?= admin_url( 'admin-ajax.php?action=korporacja_get_label&order_id=' . $order->get_id() . '&type=document' ); ?>"
                   class="button button-primary">
					<?= __( 'Upoważnienie dla FedEx', 'korporacja-kurierska' ); ?>
                </a>
            </p>
		<?php endif; ?>
		<?php if ( ! empty( trim( $korporacja_order_detail['proformaAvailable'] ) ) ): ?>
            <p>
                <a href="<?= admin_url( 'admin-ajax.php?action=korporacja_get_label&order_id=' . $order->get_id() . '&type=proforma' ); ?>"
                   class="button button-primary">
					<?= __( 'Faktura proforma dla FedEx', 'korporacja-kurierska' ); ?>
                </a>
            </p>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
