<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Korporacja\Shipping_Method' ) ) {
	class Shipping_Method extends \WC_Shipping_Method {
		const ID = 'korporacja_kurierska_shipping';

		const META_FIELD_COURIER = 'korporacja_courier_id';
		const META_FIELD_MACHINE = 'korporacja_machine_id';

		const TYPE_WEIGHT = 1;
		const TYPE_COUNT = 2;

		const CALCULATOR_TYPE_CLASS = 1;
		const CALCULATOR_TYPE_ORDER = 2;

		public function __construct( $instance_id = 0 ) {
			parent::__construct();
			$this->instance_id        = absint( $instance_id );
			$this->id                 = self::ID;
			$this->method_title       = __( 'Korporacja Kurierska', 'korporacja-kurierska' );
			$this->method_description = __( 'Metoda wysyłki integrująca wielu kurierów.', 'korporacja-kurierska' );

			$this->supports = [
				'shipping-zones',
				'instance-settings'
			];

			$this->init();

			add_action( Bridge::WC_UPDATE_OPTIONS_SHIPPING . $this->id, [
				$this,
				'process_admin_options'
			] );
			add_action( Bridge::WC_CHECKOUT_UPDATE_ORDER_META, [ $this, 'checkout_update_order_meta' ] );
			add_action( Bridge::WC_ORDER_DETAILS_AFTER_ORDER_TABLE, [ $this, 'receive_review_order' ] );
			add_action( Bridge::WC_EMAIL_ORDER_META, [ $this, 'receive_review_order' ], 100 );
			add_action( Bridge::WC_CART_TOTALS_ORDER_TOTAL_HTML, [ $this, 'add_courier' ] );
		}

		/**
		 * Add courier id to post meta
		 *
		 * @param $value
		 *
		 * @return string
		 */
		public function add_courier( $value ) {
			$shipping_method = (array) @$_POST['shipping_method'];
			if ( reset( $shipping_method ) == $this->id . ':' . $this->instance_id ) {
				echo Plugin::load_template( 'hidden', 'fields', [
					'name'  => self::META_FIELD_COURIER,
					'value' => $this->get_option( 'courier' )
				] );
			}

			return $value;
		}

		/**
		 * Add additional data to order detail
		 *
		 * @param \WC_Order $order
		 */
		public function receive_review_order( \WC_Order $order ) {
			$machine_id = get_post_meta( $order->get_id(), '_' . $this->id . '_' . self::META_FIELD_MACHINE, true );
			if ( ! empty( $machine_id ) ) {
				echo Plugin::load_template( 'review-order-meta', 'front', [
					'name'    => __( 'Wybrany punkt dostawy', 'korporacja-kurierska' ),
					'content' => $machine_id
				] );
			}
		}

		/**
		 * Save order meta on checkout
		 *
		 * @param $order_id
		 */
		public function checkout_update_order_meta( $order_id ) {
			if ( ! empty( $_POST[ self::META_FIELD_MACHINE ] ) ) {
				update_post_meta( $order_id, '_' . $this->id . '_' . self::META_FIELD_MACHINE, Plugin::sanitize_value( $_POST[ self::META_FIELD_MACHINE ] ) );
			}

			if ( ! empty( $_POST[ self::META_FIELD_COURIER ] ) ) {
				update_post_meta( $order_id, '_' . $this->id . '_' . self::META_FIELD_COURIER, Plugin::sanitize_value( $_POST[ self::META_FIELD_COURIER ] ) );
			}
		}

		/**
		 * Init
		 */
		public function init() {
			$this->init_form_fields();
			$this->init_settings();

			$this->enabled    = $this->get_option( 'enabled' );
			$this->title      = $this->get_option( 'title' );
			$this->tax_status = $this->get_option( 'tax_status' );
		}

		/**
		 * Form fields
		 */
		public function init_form_fields() {
			$this->instance_form_fields = [
				'enabled'     => [
					'title'   => __( 'Włączony / Wylączony', 'korporacja-kurierska' ),
					'type'    => 'checkbox',
					'label'   => __( 'Zaznaczone włącza metodę wysyłki', 'korporacja-kurierska' ),
					'default' => 'yes',
				],
				'title'       => [
					'title'       => __( 'Nazwa wysyłki', 'korporacja-kurierska' ),
					'type'        => 'text',
					'description' => __( 'Tą wysyłkę widzą użytkownicy podczas zamówienia.', 'korporacja-kurierska' ),
					'default'     => __( 'Korporacja Kurierska', 'korporacja-kurierska' ),
					'desc_tip'    => true
				],
				'description' => [
					'title'       => __( 'Opis wysyłki', 'korporacja-kurierska' ),
					'type'        => 'text',
					'description' => __( 'Opis wysyłki widoczny pod nazwą wysyłki na stronie zamówienia.', 'korporacja-kurierska' ),
					'desc_tip'    => true
				],
				'courier'     => [
					'title'       => __( 'Kurier', 'korporacja-kurierska' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Od wybrania kuriera zależą dodatkowe integracje.', 'korporacja-kurierska' ),
					'options'     => Courier::get_available_couriers(),
					'desc_tip'    => true
				],
				'calculator'  => [
					'title'       => __( 'Rodzaj kalkulatora dla klasy wysyłkowej', 'korporacja-kurierska' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'default'     => self::CALCULATOR_TYPE_ORDER,
					'description' => __( 'W zależności od wybranej opcji suma zamówienia będzie obliczana w inny sposób.', 'korporacja-kurierska' ),
					'options'     => [
						self::CALCULATOR_TYPE_ORDER => __( 'Na zamówienie: stosuj koszt dla najdroższej klasy wysyłkowej', 'korporacja-kurierska' ),
						self::CALCULATOR_TYPE_CLASS => __( 'Na klasę: stosuj koszt dla każdej klasy wysyłkowej oddzielnie', 'korporacja-kurierska' )
					],
					'desc_tip'    => true
				],
				'free'        => [
					'title'       => __( 'Darmowa wysyłka', 'korporacja-kurierska' ),
					'type'        => 'text',
					'description' => __( 'Podaj kwotę od której ta wysyłka będzie darmowa. Nie podanie kwoty jest równoznaczne z tym, że darmowa wysyłka nie będzie dostępna.', 'korporacja-kurierska' ),
					'desc_tip'    => true
				],
				'tax_status'  => [
					'title'   => __( 'Podatek', 'korporacja-kurierska' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'default' => 'taxable',
					'options' => [
						'taxable' => __( 'Opodatkowany', 'korporacja-kurierska' ),
						'none'    => __( 'Nieopodatkowany', 'korporacja-kurierska' ),
					]
				]
			];
		}

		/**
		 * Save admin options
		 */
		public function process_admin_options() {
			parent::process_admin_options();
			$shipping_classes = WC()->shipping->get_shipping_classes();
			foreach ( $shipping_classes as $shipping_class ) {
				if ( ! isset( $shipping_class->term_id ) ) {
					continue;
				}
				$this->set_price_table( $_POST[ 'price_table_' . $shipping_class->term_id ], $shipping_class->term_id );
			}

			$this->set_price_table( $_POST['price_table'] );
			update_option( $this->get_instance_option_key() . '_type', Plugin::sanitize_value( $_POST[ self::ID . '_type' ] ) );
		}

		/**
		 * Save price table setting
		 *
		 * @param array $price_table
		 * @param int $id
		 */
		public function set_price_table( $price_table, $id = null ) {
			if ( $id !== null ) {
				update_option( $this->get_instance_option_key() . '_price_table_' . $id, Plugin::sanitize_value( $price_table ) );
			} else {
				update_option( $this->get_instance_option_key() . '_price_table', Plugin::sanitize_value( $price_table ) );
			}
		}

		/**
		 * Get price table
		 *
		 * @param null $id
		 *
		 * @return array
		 */
		public function get_price_table( $id = null ) {
			if ( $id !== null ) {
				return get_option( $this->get_instance_option_key() . '_price_table_' . $id );
			} else {
				return get_option( $this->get_instance_option_key() . '_price_table' );
			}
		}

		/**
		 * Render setting form shipping instance
		 */
		public function admin_options() {
			echo Plugin::load_template( 'instance-settings', 'admin', [
				'settings' => $this->generate_settings_html( $this->get_instance_form_fields(), false )
			] );
			$this->calculator_shipping();

			$shipping_classes = WC()->shipping->get_shipping_classes();
			foreach ( $shipping_classes as $shipping_class ) {
				if ( ! isset( $shipping_class->term_id ) ) {
					continue;
				}
				$this->calculator_shipping( $shipping_class->term_id, $shipping_class->name );
			}
		}

		public function calculator_shipping( $id = null, $name = null ) {
			$type = get_option( $this->get_instance_option_key() . '_type' );

			echo Plugin::load_template( 'table', 'fields', [
				'name'        => 'price_table' . ( ! empty( $id ) ? '_' . $id : '' ),
				'values'      => $this->get_price_table( $id ),
				'title'       => ( ! empty( $name ) ? __( 'Ustawienia cenowe dla klasy wysyłki: ', 'korporacja-kurierska' ) . $name : __( 'Ustawienia cenowe bez klasy wysyłki', 'korporacja-kurierska' ) ),
				'description' => __( 'Wartości od / do są uzależnione od typu sposobu obliczenia. Dla typu ilościowego należy podać ilość produktów. Dla typu wagowego należy wpisać wagę w kg. Waga produktów musi być zdefiniowana we właściwościach produktu. Wartość od jest brana włącznie. Np. przy sposobu obliczenia wagowego i zdefiniowanym wpisie od = 1, do 3, cena 10zł a waga produktów w koszyku wynosi 1kg to cena dla użytkownika będzie wynosić 10zł. Jeśli suma wyniesie 3kg to cena dla użytkownika zostanie uwzględniona z następnego wpisu. W przypadku gdy żaden warunek nie zostanie spełniony użytkownik nie zobaczy żadnej opcji wyboru. Warunki sprawdzane są w kolejności w jakiej są w tabeli.', 'korporacja-kurierska' ),
				'structure'   => [
					'from'  => [
						'name'  => __( 'Od', 'korporacja-kurierska' ),
						'class' => 'required number',
						'tip'   => __( 'Liczba całkowita większa od 0', 'korporacja-kurierska' )
					],
					'to'    => [
						'name'  => __( 'Do', 'korporacja-kurierska' ),
						'class' => 'required number',
						'tip'   => __( 'Liczba całkowita większa od 0', 'korporacja-kurierska' )
					],
					'price' => [
						'name'  => __( 'Cena', 'korporacja-kurierska' ),
						'class' => 'required money',
						'tip'   => __( 'Podaj cenę. Np. 10, 9.99, 7.80001 itp. Separatorem dziesiętnym jest kropka (.).', 'korporacja-kurierska' )
					]
				],
				'plugin'      => $this
			] );

			woocommerce_form_field( self::ID . '_type[' . ( ! empty( $id ) ? $id : 0 ) . ']', [
				'label'       => __( 'Sposób oblicznia', 'korporacja-kurierska' ) . Plugin::load_template( 'tip', 'admin', [ 'value' => __( 'Ilościowy będzie liczony od liczby produktów w koszyku. Wagowy będzie obliczał cenę na podstawie wagi produktów w koszyku. Waga produktu musi być podana w kilogramach.', 'korporacja-kurierska' ) ] ),
				'type'        => 'select',
				'input_class' => [ 'wc-enhanced-select' ],
				'options'     => [
					self::TYPE_COUNT  => __( 'Ilościowy', 'korporacja-kurierska' ),
					self::TYPE_WEIGHT => __( 'Wagowy', 'korporacja-kurierska' )
				]
			], @$type[ ! empty( $id ) ? $id : 0 ] );
		}

		/**
		 * Calculate shipping cost
		 *
		 * @param array $package
		 */
		public function calculate_shipping( $package = [] ) {
			$free_shipping = $this->get_option( 'free' );

			if ( ! empty( $free_shipping ) && $package['contents_cost'] >= $free_shipping ) {
				$price = 0;
			} else {
				$price = $this->calculate_shipping_for_class( $package );
			}

			if ( $price !== false ) {
				$rate = [
					'id'      => $this->get_rate_id(),
					'label'   => $this->title . ( ( $price === 0 ) ? ' - ' . __( 'Darmowa dostawa', 'korporacja-kurierska' ) : '' ),
					'cost'    => $price,
					'package' => $package
				];
				$this->add_rate( $rate );
			}
		}

		/**
		 * Calculate shipping cost for shipping class
		 *
		 * @param array $package
		 *
		 * @return bool|float|int
		 */
		public function calculate_shipping_for_class( $package = [] ) {
			$price                  = false;
			$found_shipping_classes = $this->find_shipping_classes( $package );

			if ( ! empty( $found_shipping_classes ) ) {
				$found_shipping_classes = $this->find_shipping_classes( $package );
				$highest_class_cost     = 0;
				$calculator             = $this->get_option( 'calculator' );

				foreach ( $found_shipping_classes as $shipping_class => $products ) {
					$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

					$value = $this->calculate_package_param( $products );
					$cost  = $this->get_price_for_package_value( $value, $this->get_price_table( $shipping_class === '' ? null : $shipping_class_term->term_id ) );

					if ( $cost !== false ) {
						if ( self::CALCULATOR_TYPE_CLASS == $calculator ) {
							$price += (float) $cost;
						} else {
							$highest_class_cost = (float) $cost > $highest_class_cost ? (float) $cost : $highest_class_cost;
						}
					}
				}

				if ( self::CALCULATOR_TYPE_ORDER == $calculator && $highest_class_cost ) {
					$price += $highest_class_cost;
				}
			}

			return $price;
		}

		/**
		 * Finds and returns shipping classes and the products with said class.
		 *
		 * @param mixed $package
		 *
		 * @return array
		 */
		public function find_shipping_classes( $package ) {
			$found_shipping_classes = array();

			foreach ( $package['contents'] as $item_id => $values ) {
				if ( $values['data'] instanceof \WC_Product && $values['data']->needs_shipping() ) {
					$found_class = $values['data']->get_shipping_class();

					if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
						$found_shipping_classes[ $found_class ] = array();
					}

					$found_shipping_classes[ $found_class ][ $item_id ] = $values;
				}
			}

			return $found_shipping_classes;
		}

		/**
		 * Return price from price table
		 *
		 * @param $value
		 * @param $prices
		 *
		 * @return int|float|bool
		 */
		public function get_price_for_package_value( $value, $prices ) {
			if ( ! empty( $prices ) ) {
				foreach ( $prices as $price ) {
					if ( $value >= $price['from'] && $value < $price['to'] ) {
						return $price['price'];
					}
				}
			}

			return false;
		}

		/**
		 * Return value of contents based on cart and type option
		 *
		 * @param $contents
		 *
		 * @return float|int
		 */
		public function calculate_package_param( $contents ) {
			switch ( $this->get_option( 'type' ) ) {
				case self::TYPE_WEIGHT:
					return $this->calculate_package_weight( $contents );

				case self::TYPE_COUNT:
				default:
					return $this->calculate_package_quantity( $contents );
			}
		}

		/**
		 * Return total weight of contents
		 *
		 * @param array $contents
		 *
		 * @return float|int
		 */
		public function calculate_package_weight( $contents = [] ) {
			$weight = 0;
			foreach ( $contents as $content ) {
				if ( $content['data'] instanceof \WC_Product && $content['data']->needs_shipping() ) {
					$weight += (float) $content['data']->get_weight() * $content['quantity'];
				}
			}

			return $weight;
		}

		/**
		 * Return contents quantity
		 *
		 * @param array $contents
		 *
		 * @return int
		 */
		public function calculate_package_quantity( $contents = [] ) {
			$quantity = 0;
			foreach ( $contents as $content ) {
				if ( $content['data'] instanceof \WC_Product && $content['data']->needs_shipping() ) {
					$quantity += $content['quantity'];
				}
			}

			return $quantity;
		}
	}
}
