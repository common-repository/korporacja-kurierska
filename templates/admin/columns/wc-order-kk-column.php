<?php if ( ! empty( $availability ) ) : ?>
	<?php if ( empty( $status ) ) : ?>
        <div class="kk-icon await tips"
             data-tip="<?= __( 'Zamówienie oczekuje na nadanie.', 'korporacjak-kurierska' ); ?>"></div>
	<?php else: ?>
        <div class="kk-icon send tips"
             data-tip="<?= __( 'Zdefiniowano przesyłkę.', 'korporacjak-kurierska' ); ?>"></div>
	<?php endif; ?>
<?php else: ?>
    <div class="kk-icon none tips"
         data-tip="<?= __( 'Klient wybrał inną metodę wysyłki niż tą zdefiniowną przez Korporację Kurierską.', 'korporacjak-kurierska' ); ?>"></div>
<?php endif; ?>
