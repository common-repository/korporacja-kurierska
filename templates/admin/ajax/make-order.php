<?php if ( ! empty( $price ) && ! empty( $packageType ) ): ?>
    <h3>
		<?= __( 'Koszt zamówienia:', 'korporacja-kurierska' ); ?> <?= esc_html($price); ?>PLN
    </h3>
    <p class="form-row">
        <input type="submit" value="<?= __( 'Nadaj paczkę', 'korporacja-kurierska' ); ?>"
               class="button button-primary button-make-order"/>
    </p>
    <p class="form-row">
        <input type="submit" value="<?= __( 'Wybierz innego przewoźnika', 'korporacja-kurierka' ); ?>"
               class="button button-check-price"/>
    </p>
    <p class="form-row">
        <input type="submit" value="<?= __( 'Anuluj', 'korporacja-kurierska' ); ?>" class="button button-cancel"/>
    </p>
    <input name="<?= \Korporacja\API::FIELD_PACKAGE_TYPE; ?>" type="hidden" value="<?= esc_html($packageType); ?>"
           id="<?= \Korporacja\API::FIELD_PACKAGE_TYPE; ?>"/>
<?php endif; ?>
