<?php if ( ! empty( $status ) ) : ?>
    <input type="hidden" name="<?= \Korporacja\Plugin::ID; ?>_enable" value="0"/>
    <input type="submit" name="<?= \Korporacja\Plugin::ID; ?>_enable_submit" class="button"
           value="<?= __( 'Wyłącz integrację dla tego zamówienia', 'korporacja-kurierska' ); ?>"/>
<?php else: ?>
    <input type="hidden" name="<?= \Korporacja\Plugin::ID; ?>_enable" value="1"/>
    <input type="submit" name="<?= \Korporacja\Plugin::ID; ?>_enable_submit" class="button button-primary"
           value="<?= __( 'Włącz integrację dla tego zamówienia', 'korporacja-kurierska' ); ?>"/>
<?php endif; ?>
