<?php if ( ! empty( $order_id ) && ! empty( $order_args ) && ! empty( $form ) && ! empty( $order ) && $order instanceof \WC_Order ): ?>
    <p>
		<?= __( 'Kurier:', 'korporacja-kurierska' ); ?><br/>
        <strong><?= \Korporacja\API::COURIERS[ $order_args['courierId'] ]; ?></strong>
    </p>
    <p>
		<?= __( 'Numer zamówienia w systemie:', 'korporacja-kurierska' ); ?><br/>
        <strong><?= $order_id; ?></strong>
    </p>
    <div class="korporacja-action-container" data-order-id="<?= $order->get_id(); ?>">
		<?= $form; ?>
        <div class="options"></div>
        <input type="hidden" name="courier_id" value="<?= $order_args['courierId']; ?>" />
        <input type="hidden" name="courierId" value="<?= $order_args['courierId']; ?>" />
        <input type="hidden" name="ordersIds" value="<?= $order_id; ?>" />
        <input type="submit" name="button-call-courier" class="button button-primary button-call-courier"
               value="<?= __( 'Zamów kuriera', 'korporacja-kurierska' ); ?>" />
    </div>
<?php endif; ?>
