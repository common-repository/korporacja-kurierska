<?php if ( ! empty( $courier ) ): ?>
    <p>
		<?= __( 'Wybrany kurier przez użytkownika:', 'korporacja-kurierska' ); ?>
        <strong><?= \Korporacja\API::COURIERS[ $courier ]; ?></strong>
    </p>
<?php endif; ?>

<?php $select_data = \Korporacja\Package_Template::get_select_data(); ?>
<?php if ( ! empty( $select_data ) ): ?>
	<?php woocommerce_form_field( 'kk-package-template', [
		'type'    => 'select',
		'options' => $select_data,
		'label'   => __( 'Możesz skorzystać ze zdefiniowanego szablonu. Pamiętaj zapisać dane paczki poniżej.', 'korporacja-kurierska' )
	] ) ?>
<?php endif; ?>

<?= \Korporacja\Plugin::load_template( 'table', 'fields', [
	'name'      => \Korporacja\Plugin::ID . '_packages',
	'values'    => ! empty( $packages_value ) ? $packages_value : null,
	'disabled'  => true,
	'structure' => [
		'weight'          => [
			'name'  => __( 'Waga (kg)', 'korporacja-kurierska' ),
			'class' => 'required',
			'tip'   => __( 'Liczba większa od 0', 'korporacja-kurierska' )
		],
		'length'          => [
			'name'  => __( 'Długość (cm)', 'korporacja-kurierska' ),
			'class' => 'required number',
			'tip'   => __( 'Liczba całkowita większa od 0', 'korporacja-kurierska' )
		],
		'width'           => [
			'name'  => __( 'Szerokość (cm)', 'korporacja-kurierska' ),
			'class' => 'required number',
			'tip'   => __( 'Liczba całkowita większa od 0', 'korporacja-kurierska' )
		],
		'height'          => [
			'name'  => __( 'Wysokość (cm)', 'korporacja-kurierska' ),
			'class' => 'required number',
			'tip'   => __( 'Liczba całkowita większa od 0', 'korporacja-kurierska' )
		],
		'amount'          => [
			'name'  => __( 'Ilość', 'korporacja-kurierska' ),
			'class' => 'required number',
			'tip'   => __( 'Liczba całkowita większa od 0', 'korporacja-kurierska' )
		],
		'unsortableShape' => [
			'name'    => __( 'Rodzaj opakowania', 'korporacja-kurierska' ),
			'class'   => 'required number',
			'tip'     => __( 'standardowy – opakowanie kartonowe o kształcie prostopadłościanu; niestandardowy - kształt owalny, nieregularne kształty, wystające elementy' ),
			'type'    => 'select',
			'options' => [
				0 => __( 'standardowy', 'korporacja-kurierska' ),
				1 => __( 'niestandardowy', 'korporacja-kurierska' )
			]
		]
	],
	'plugin'    => new \Korporacja\Shipping_Method()
] ); ?>
<p>
    <input type="submit" value="<?= __( 'Zapisz dane', 'korporacja-kurierska' ); ?>" class="button button-primary"/>
</p>
