<?php if ( ! empty( $value ) ): ?>
	<?php
	$option_value      = (array) WC_Admin_Settings::get_option( $value['id'], $value['default'] );
	$custom_attributes = array();

	if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
		foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
	}

	$field_description = WC_Admin_Settings::get_field_description( $value );
	extract( $field_description );
	?>
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="<?= esc_attr( $value['id'] ); ?>"><?= esc_html( $value['title'] ); ?></label>
			<?= ! empty( $tooltip_html ) ? $tooltip_html : ''; ?>
        </th>
        <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
            <fieldset>
				<?= ! empty( $description ) ? $description : ''; ?>
                <ul>
					<?php foreach ( $value['options'] as $key => $val ) : ?>
                        <li>
                            <label>
                                <input name="<?= esc_attr( $value['id'] ); ?>[]" value="<?= $key; ?>" type="checkbox"
                                       style="<?php echo esc_attr( $value['css'] ); ?>"
                                       class="<?= esc_attr( $value['class'] ); ?>"
									<?= implode( ' ', $custom_attributes ); ?>
									<?php if ( in_array( $key, $option_value ) ): ?> checked="checked" <?php endif; ?>
                                />
								<?= $val ?>
                            </label>
                        </li>
					<?php endforeach; ?>
                </ul>
            </fieldset>
        </td>
    </tr>
<?php endif; ?>
