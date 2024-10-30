<h3><?= __( 'Zamówienie przyjęte', 'korporacja-kurierska' ); ?></h3>
<?php if ( ! empty( $response['message'] ) ): ?>
    <p><?= esc_html( $response['message'] ); ?></p>
<?php endif; ?>
<?php if ( ! empty( $response['orderId'] ) ): ?>
    <p><?= __( 'Numer utworzonego zamówienia', 'korporacja-kurierska' ); ?> <?= esc_html( $response['orderId'] ); ?></p>
<?php endif; ?>
