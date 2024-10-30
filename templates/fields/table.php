<?php if ( ! empty( $structure ) && ! empty( $name ) ): ?>
	<?php if ( ! empty( $title ) ): ?>
        <h2>
            <?= esc_html( $title ); ?>
	        <?php if ( ! empty( $description ) ): ?>
                <span class="woocommerce-help-tip" data-tip="<?= esc_html( $description ); ?>"></span>
	        <?php endif; ?>
        </h2>
	<?php endif; ?>
    <table class="korporacja-table wc_input_table widefat <?php if ( ! empty( $disabled ) ): ?> disabled <?php endif; ?>">
        <thead>
        <tr>
			<?php foreach ( $structure as $selector => $item ): ?>
                <th>
					<?= esc_html( $item['name'] ); ?>
					<?php if ( ! empty( $item['tip'] ) && ! empty( $plugin ) && $plugin instanceof \WC_Settings_API ): ?>
						<?= $plugin->get_tooltip_html( [ 'desc_tip' => esc_html( $item['tip'] ) ] ); ?>
					<?php endif; ?>
                </th>
			<?php endforeach; ?>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th colspan="<?= count( $structure ); ?>">
                <a href="#" class="button insert"><?= __( 'Dodaj wiersz', 'korporacja-kurierska' ); ?></a>
                <a href="#" class="button remove"><?= __( 'Usuń zaznaczone', 'korporacja-kurierska' ); ?></a>
            </th>
        </tr>
        </tfoot>
        <tbody data-structure="<?= htmlspecialchars( json_encode( $structure ), ENT_QUOTES, 'UTF-8' ); ?>"
               data-name="<?= $name; ?>">
		<?php if ( ! empty( $values ) ): ?>
			<?php foreach ( $values as $key => $value ): ?>
                <tr>
					<?php foreach ( $structure as $selector => $item ): ?>
                        <td>
							<?php if ( @$item['type'] == 'select' ): ?>
                                <select name="<?= $name; ?>[<?= $key; ?>][<?= $selector; ?>]"
                                        class="<?= @$item['class']; ?>">
									<?php if ( ! empty( $item['options'] ) ): ?>
										<?php foreach ( $item['options'] as $var => $option ): ?>
                                            <option value="<?= $var; ?>" <?php if ( isset( $value[ $selector ] ) && $var == $value[ $selector ] ): ?> selected="selected" <?php endif; ?>><?= $option; ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
                                </select>
							<?php else: ?>
                                <input type="text" value="<?= @$value[ $selector ]; ?>" placeholder="*"
                                       name="<?= $name; ?>[<?= $key; ?>][<?= $selector; ?>]"
                                       class="<?= @$item['class']; ?>">
							<?php endif; ?>
                        </td>
					<?php endforeach; ?>
                </tr>
			<?php endforeach; ?>
		<?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>
