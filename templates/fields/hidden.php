<?php if ( ! empty( $name ) && ! empty( $value ) ): ?>
    <input type="hidden" name="<?= esc_html( $name ); ?>" value="<?= esc_html( $value ); ?>"/>
<?php endif; ?>
