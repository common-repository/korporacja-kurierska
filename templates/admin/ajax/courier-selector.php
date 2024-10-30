<?php if ( ! empty( $couriers ) && ! empty( $content ) ): ?>
    <h3><?= __( 'Szczegóły nadania', 'korporacja-kurierska' ); ?></h3>
	<?php if ( $package_type == 'koperta' ): ?>
        <p class="form-row">
            <strong>Pamiętaj, że maksymalny wymiar dla koperty to: 35.00cm x 25.00cm x 5.00cm.</strong>
        </p>
	<?php endif; ?>
    <p class="form-row">
        <label for="korporacja-courier-option"><?= __( 'Kurier', 'korporacja-kurierska' ); ?></label>
        <select name="courier_id" id="korporacja-courier-option" class="korporacja-courier-option">
            <option><?= __( 'Wybierz', 'korporacja-kurierska' ); ?></option>
			<?php foreach ( $couriers as $courier ): ?>
				<?php if ( ! empty( $courier['available'] ) ): ?>
                    <option value="<?= esc_html( $courier['id'] ); ?>" <?php if ( isset( $selected_courier ) && $selected_courier == $courier['id'] ): ?> selected="selected" <?php endif; ?>>
						<?= esc_html( $courier['name'] ); ?> - <?= $courier['grossPriceTotal']; ?>PLN Brutto
                    </option>
				<?php else: ?>
                    <option disabled="disabled">
						<?= esc_html( $courier['name'] ); ?>
                        <?php if ( ! empty( $courier['message'] ) ): ?>
                            (<?= esc_html( $courier['message'] ) ?>)
                        <?php endif; ?>
                    </option>
				<?php endif; ?>
			<?php endforeach; ?>
        </select>
    </p>
	<?php woocommerce_form_field( \Korporacja\API::FIELD_CONTENT, [
		'label'     => __( 'Zawartość przesyłki', 'korporacja-kurierska' ),
		'type'      => 'textarea',
		'maxlength' => 100
	], $content ); ?>
	<?php woocommerce_form_field( \Korporacja\API::FIELD_COMMENTS, [
		'label'     => __( 'Dodatkowe uwagi i komentarze', 'korporacja-kurierska' ),
		'type'      => 'textarea',
		'maxlength' => 150
	] ); ?>
    <div class="options"></div>
    <p class="form-row">
        <input type="submit" value="<?= __( 'Sprawdź dane przed nadaniem', 'korporacja-kurierska' ); ?>"
               class="button button-primary button-check-data"/>
    </p>
    <p class="form-row">
        <input type="submit" value="<?= __( 'Anuluj', 'korporacja-kurierska' ); ?>" class="button button-cancel"/>
    </p>
<?php endif; ?>
