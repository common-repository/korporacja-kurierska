<?php if ( $order instanceof \WC_Order ): ?>
	<?php if ( ! empty( $courier ) ) : ?>
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
    <div class="korporacja-action-container" data-order-id="<?= $order->get_id(); ?>"
         data-order-value="<?= $order->get_total(); ?>">
		<?php if ( empty( $packages_value ) ): ?>
            <p><?= __( 'Aby nadać przesyłkę należy zdefiniować i zapisać paczki po lewej stronie.', 'korporacja-kurierska' ); ?></p>
		<?php endif; ?>
		<?php woocommerce_form_field( \Korporacja\API::FIELD_PACKAGE_TYPE, [
			'label'   => __( 'Rodzaj przesyłki', 'korporacja-kurierska' ),
			'type'    => 'select',
			'options' => [
				'paczka'  => 'Paczka',
				'paleta'  => 'Paleta',
				'koperta' => 'Koperta'
			]
		] ); ?>
        <input type="submit" name="button-check-price" class="button button-primary button-check-price"
               value="<?= __( 'Sprawdź ceny wysyłki', 'korporacja-kurierska' ); ?>"
			<?php if ( empty( $packages_value ) ): ?> disabled="disabled" <?php endif; ?>
        />
    </div>
<?php endif; ?>
