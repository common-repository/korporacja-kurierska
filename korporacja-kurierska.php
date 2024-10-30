<?php
/*
	Plugin Name: Korporacja Kurierska - Metoda wysyłki
	Plugin URI: https://wordpress.org/plugins/korporacja-kurierska/
	Description: Wtyczka Korporacji Kurierskiej dla wysyłki zamówień w WooCommerce.
	Version: 2.1.2
	Author: Korporacja Kurierska
	Author URI: https://www.korporacjakurierska.pl/
	Text Domain: korporacja-kurierska
	Domain Path: /languages/
	Tested up to: 4.8

	Copyright 2017 Polska Korporacja Wydawców i Dystrybutorów Dudkiewicz i S-ka Spółka Jawna.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . __( 'Wtyczka Korporacji Kurierskiej wymaga PHP w wersji 5.6 lub wyższej do poprawnego działania. Zaktualizuj PHP.', 'korporacja-kurierska' ) . "</p></div>';" ) );

	return;
} else {
	if ( ! function_exists( '__is_plugin_active' ) ) {
		function __is_plugin_active( $plugin_file ) {

			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}

			return in_array( $plugin_file, $active_plugins ) || array_key_exists( $plugin_file, $active_plugins );
		}
	}

	if ( __is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		foreach ( glob( plugin_dir_path( __FILE__ ) . "classes/*.php" ) as $filename ) {
			require_once $filename;
		}

		class Plugin extends Base_Plugin {
			const VALID_TIME_PACZKOMATY = 86400; //60 * 60 * 24;
			const VALID_TIME_PACZKA_W_RUCHU = 86400; //60 * 60 * 24;
			const VALID_TIME_UPS_ACCESS_POINT = 86400; //60 * 60 * 24;

			const SYSTEM_NAME = 'woocommerce';
			const WC_ORDER_COLUMN_NAME = 'kk-column';
			const WC_ORDER_COLUMN_POSITION = 7;

			public function __construct() {
				parent::__construct();
				add_action( Bridge::PLUGINS_LOADED, [ $this, 'init_korporacja_kurierska' ], 1000 );
			}

			/**
			 * Plugin hooks
			 */
			public function init_korporacja_kurierska() {
				new Setting();
				new Courier();
				new Package_Template();
				new Meta_Box();
				new Ajax();

				add_filter( Bridge::ADMIN_MENU, [ $this, 'add_menu_pages' ], 50 );

				add_action( Bridge::WC_UPDATE_OPTIONS_SHIPPING . self::ID, [ $this, 'process_admin_options' ] );
				add_filter( Bridge::WC_SHIPPING_METHODS, [ $this, 'add_shipping_methods' ] );

				add_filter( Bridge::WC_CHECKOUT_FIELDS, [ $this, 'change_checkout_field_description' ] );
				add_action( Bridge::WC_CHECKOUT_PROCESS, [ $this, 'checkout_process' ] );

				add_action( Bridge::WC_REVIEW_ORDER_AFTER_SHIPPING, [ $this, 'review_order_after_shipping' ] );
				add_action( Bridge::WC_AFTER_SHIPPING_RATE, [ $this, 'shipping_rate_description' ], 10, 2 );

				if ( ! get_option( Plugin::ID . '_setting_column_disabled' ) ) {
					add_action( Bridge::WC_ORDER_COLUMNS, [ $this, 'add_column_to_wc_order' ], 11 );
					add_action( Bridge::WC_ORDER_COLUMN_CONTENT, [ $this, 'manage_wc_order_column' ], 10, 2 );
				}
			}

			/**
			 * Add additional fields base on courier option
			 */
			public function review_order_after_shipping() {
				$methods = WC()->session->get( 'chosen_shipping_methods' );
				$method  = reset( $methods );
				$method  = explode( ':', $method );
				if ( $method[0] == Shipping_Method::ID ) {
					$instance = new Shipping_Method( $method[1] );
					switch ( $instance->get_option( 'courier' ) ) {
						case API::COURIER_UPS_ACCESS_POINT:
							$this->ups_access_point_checkout_review_order();
							break;

						case API::COURIER_PACZKA_W_RUCHU:
							$this->paczka_w_ruchu_checkout_review_order();
							break;

						case API::COURIER_PACZKOMATY:
							$this->paczkomaty_checkout_review_order();
							break;

						default:

							break;
					}
				}
			}

			/**
			 * Review paczkomaty machine list
			 */
			public function paczkomaty_checkout_review_order() {
				echo self::load_template( 'review-order-row', 'front', [
					'name'    => __( 'Wybierz Paczkomat', 'korporacja-kurierska' ),
					'content' => woocommerce_form_field( Shipping_Method::META_FIELD_MACHINE, [
							'type'        => 'select',
							'options'     => array_merge( [ '' => __( 'Wybierz', 'korporacja-kurierska' ) ], Courier::get_paczkomaty() ),
							'input_class' => [ 'add-select2' ],
							'return'      => true
						] ) . self::load_template( 'paczkomaty', 'front', [] )
				] );
			}

			/**
			 * Review paczka w ruchu machine list
			 */
			public function paczka_w_ruchu_checkout_review_order() {
				echo self::load_template( 'review-order-row', 'front', [
					'name'    => __( 'Wybierz Punkt', 'korporacja-kurierska' ),
					'content' => woocommerce_form_field( Shipping_Method::META_FIELD_MACHINE, [
							'type'        => 'select',
							'options'     => array_merge( [ '' => __( 'Wybierz', 'korporacja-kurierska' ) ], Courier::get_paczka_w_ruchu() ),
							'input_class' => [ 'add-select2' ],
							'return'      => true
						] ) . self::load_template( 'paczka-w-ruchu', 'front', [] )
				] );
			}

			/**
			 * Review ups access point machine list
			 */
			public function ups_access_point_checkout_review_order() {
				$options = Courier::get_ups_access_point( $_POST['s_city'], $_POST['s_country'] );
				if ( ! empty( $options ) ) {
					$content = woocommerce_form_field( Shipping_Method::META_FIELD_MACHINE, [
						'type'        => 'select',
						'options'     => array_merge( [ '' => __( 'Wybierz', 'korporacja-kurierska' ) ], $options ),
						'input_class' => [ 'add-select2' ],
						'return'      => true
					] );
				} else {
					$content = __( 'Dla podanych danych nie można znaleźć punktu odbioru.', 'korporacja-kurierska' );
				}

				$content .= '<a href="https://www.ups.com/dropoff?loc=pl_PL" target="_blank">' . __( 'Znajdź punkt odbioru', 'korporacja-kurierska' ) . '</a>';

				echo self::load_template( 'review-order-row', 'front', [
					'name'    => __( 'Wybierz Punkt', 'korporacja-kurierska' ),
					'content' => $content
				] );
			}

			/**
			 * Add Korporacja Kurierska to Admin menu
			 */
			public function add_menu_pages() {
				add_menu_page( 'Korporacja Kurierska', 'Korporacja Kurierska', 'manage_options', self::ID, null, 'dashicons-admin-site', 59 );
			}

			/**
			 * Add label to address second line
			 *
			 * @param array $fields
			 *
			 * @return array
			 */
			public function change_checkout_field_description( $fields = [] ) {
				$fields['billing']['billing_address_2']['label'] = $fields['shipping']['shipping_address_2']['label'] = __( 'Numer / lokal', 'korporacja-kurierska' );

				return $fields;
			}

			/**
			 * Additional validation based on courier on checkout page
			 */
			public function checkout_process() {
				if ( ! empty( $_POST[ Shipping_Method::META_FIELD_COURIER ] ) ) {
					switch ( absint( $_POST[ Shipping_Method::META_FIELD_COURIER ] ) ) {
						case API::COURIER_PACZKOMATY:
						case API::COURIER_PACZKA_W_RUCHU:
						case API::COURIER_UPS_ACCESS_POINT:
							if ( empty( $_POST[ Shipping_Method::META_FIELD_MACHINE ] ) ) {
								wc_add_notice( __( 'Brak wybranego punktu odbioru.', 'korporacja-kurierska' ), 'error' );
							}
							break;

						default:

							break;
					}
				}
			}

			/**
			 * Prepare data to send to Korporacja Kurierska
			 *
			 * @param \WC_Order $order
			 * @param array $packages
			 * @param array $data
			 *
			 * @return array
			 */
			public static function prepare_order_data( \WC_Order $order, $packages = [], $data = [] ) {
				$address = explode( '/', $order->get_shipping_address_2() );
				$args    = [
					API::FIELD_COURIER_ID            => $data['courier_id'],
					API::FIELD_SENDER_NAME           => get_option( self::ID . '_profile_sender_first_name' ),
					API::FIELD_SENDER_LAST_NAME      => get_option( self::ID . '_profile_sender_last_name' ),
					API::FIELD_SENDER_COMPANY        => get_option( self::ID . '_profile_sender_company' ),
					API::FIELD_SENDER_STREET         => get_option( self::ID . '_profile_sender_street' ),
					API::FIELD_SENDER_HOUSE_NUMBER   => get_option( self::ID . '_profile_sender_house_number' ),
					API::FIELD_SENDER_FLAT_NUMBER    => get_option( self::ID . '_profile_sender_flat_number' ),
					API::FIELD_SENDER_POST_CODE      => get_option( self::ID . '_profile_sender_post_code' ),
					API::FIELD_SENDER_CITY           => get_option( self::ID . '_profile_sender_city' ),
					API::FIELD_SENDER_COUNTRY        => 'PL',
					API::FIELD_SENDER_PHONE          => get_option( self::ID . '_profile_sender_phone' ),
					API::FIELD_RECEIVER_NAME         => $order->get_shipping_first_name(),
					API::FIELD_RECEIVER_LAST_NAME    => $order->get_shipping_last_name(),
					API::FIELD_RECEIVER_COMPANY      => $order->get_shipping_company(),
					API::FIELD_RECEIVER_STREET       => $order->get_shipping_address_1(),
					API::FIELD_RECEIVER_HOUSE_NUMBER => trim( $address[0] ),
					API::FIELD_RECEIVER_FLAT_NUMBER  => trim( @$address[1] ),
					API::FIELD_RECEIVER_POST_CODE    => $order->get_shipping_postcode(),
					API::FIELD_RECEIVER_CITY         => $order->get_shipping_city(),
					API::FIELD_RECEIVER_COUNTRY      => $order->get_shipping_country(),
					API::FIELD_RECEIVER_PHONE        => $order->get_billing_phone(),
					API::FIELD_RECEIVER_EMAIL        => $order->get_billing_email(),
					API::FIELD_PACKAGE_TYPE          => sanitize_text_field( $_POST[ API::FIELD_PACKAGE_TYPE ] ),
					API::FIELD_PACKAGES              => $packages,
					API::FIELD_PICKUP_DATE           => $data[ API::FIELD_PICKUP_DATE ],
					API::FIELD_COMMENTS              => $data[ API::FIELD_COMMENTS ],
					API::FIELD_CONTENT               => $data[ API::FIELD_CONTENT ],
					API::FIELD_PAYMENT_TYPE          => get_option( self::ID . '_setting_payment_type' ),
					API::FIELD_COD_BANK_ACCOUNT      => get_option( self::ID . '_setting_cod_bank' ),
					API::FIELD_SYSTEM_NAME           => self::SYSTEM_NAME
				];

				$args = self::add_value_data( $args, $data, [
					API::FIELD_SENDER_MACHINE_NAME,
					API::FIELD_RECEIVER_MACHINE_NAME,
					API::FIELD_COD_BANK_ACCOUNT,
					API::FIELD_PURPOSE,
					API::FIELD_SMS_SENDING_NOTIFICATION,
					API::FIELD_PURPOSE,
					API::FIELD_INFO_SERVICE_EMAIL,
					API::FIELD_CONFIRMATION_EMAIL,
					API::FIELD_CONFIRMATION_SMS,
					API::FIELD_SENDING_NOTIFICATION_EMAIL,
					API::FIELD_SENDING_NOTIFICATION_SMS,
					API::FIELD_DELIVERY_NOTIFICATION_SMS,
					API::FIELD_CONFIRMATION_SMS,
					API::FIELD_NOTIFICATION_EMAIL,
					API::FIELD_DELIVERY_CONFIRMATION,
					API::FIELD_NO_COURIER_ORDER,
					API::FIELD_DELIVERY_SATURDAY,
					API::FIELD_PRIVATE_RECEIVER,
					API::FIELD_ROD
				] );

				if ( ! empty( $data['pickupHours'] ) ) {
					$hours                               = explode( '-', $data['pickupHours'] );
					$args[ API::FIELD_PICKUP_TIME_FROM ] = $hours[0];
					$args[ API::FIELD_PICKUP_TIME_TO ]   = $hours[1];
				}

				if ( ! empty( $data[ API::FIELD_COD_AMOUNT ] ) ) {
					$args[ API::FIELD_COD ]        = 1;
					$args[ API::FIELD_COD_AMOUNT ] = $data[ API::FIELD_COD_AMOUNT ];
					$args[ API::FIELD_COD_TYPE ]   = $data[ API::FIELD_COD_TYPE ];
				}

				if ( ! empty( $data[ API::FIELD_DECLARED_VALUE ] ) ) {
					$args[ API::FIELD_INSURANCE ]      = 1;
					$args[ API::FIELD_DECLARED_VALUE ] = $data[ API::FIELD_DECLARED_VALUE ];
				}

				return $args;
			}

			/**
			 * Add values based on fields data
			 *
			 * @param array $args
			 * @param array $data
			 * @param array $fields
			 *
			 * @return array
			 */
			private static function add_value_data( $args = [], $data = [], $fields = [] ) {
				foreach ( $fields as $field ) {
					if ( ! empty( $data[ $field ] ) ) {
						$args[ $field ] = $data[ $field ];
					}
				}

				return $args;
			}

			/**
			 * Get token
			 *
			 * @return string
			 */
			public static function get_token() {
				$token_time = get_option( self::ID . '_valid_token' );
				$token      = get_option( self::ID . '_api_token' );

				if ( time() < $token_time && ! empty( $token ) ) {
					return get_option( self::ID . '_api_token' );
				} else {
					return self::get_api_token();
				}
			}

			/**
			 * @return string|bool
			 */
			public static function get_api_token() {
				$api = self::get_api();
				try {
					$response = $api->login( get_option( self::ID . '_api_email' ), md5( get_option( self::ID . '_api_password' ) ) );

					update_option( self::ID . '_api_token', $response[ API::FIELD_SESSION ] );
					update_option( self::ID . '_valid_token', time() + $api::API_VALID_TOKEN );

					return $response[ API::FIELD_SESSION ];
				} catch ( \Exception $e ) {
					return false;
				}
			}

			/**
			 * Add shipping methods to checkout
			 *
			 * @param array $methods
			 *
			 * @return array
			 */
			public function add_shipping_methods( $methods = [] ) {
				$methods[ Shipping_Method::ID ] = new Shipping_Method();

				return $methods;
			}

			/**
			 * Add column to WC Order
			 *
			 * @param array $columns
			 *
			 * @return array
			 */
			public function add_column_to_wc_order( $columns = [] ) {
				return array_slice( $columns, 0, self::WC_ORDER_COLUMN_POSITION, true ) +
				       array( self::WC_ORDER_COLUMN_NAME => __( 'KK', 'korporacja-kurierska' ) ) +
				       array_slice( $columns, self::WC_ORDER_COLUMN_POSITION, count( $columns ) - self::WC_ORDER_COLUMN_POSITION, true );
			}

			/**
			 * Add content to korporacja's WC Order column
			 *
			 * @param $column
			 * @param $post_id
			 */
			public function manage_wc_order_column( $column, $post_id ) {
				switch ( $column ) {
					case self::WC_ORDER_COLUMN_NAME :
						echo self::load_template( 'wc-order-kk-column', 'admin/columns', [
							'availability' => self::check_kk_for_order( $post_id ),
							'status'       => get_post_meta( $post_id, '_' . Plugin::ID . '_order', true )
						] );

						break;
				}
			}

			/**
			 * Checking if order is good for Korporacja Kurierska integration
			 *
			 * @param $post_id
			 *
			 * @return bool
			 */
			public static function check_kk_for_order( $post_id ) {
				$override = get_post_meta( $post_id, '_' . Plugin::ID . '_enable', true );
				if ( $override === "" ) {
					return ! empty( get_post_meta( $post_id, '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_COURIER, true ) );
				} else {
					return ! empty( $override );
				}
			}

			/**
			 * Method fired when plugin is activated.
			 *
			 * @return string
			 */
			public static function activation() {
				$version = get_option( self::ID . '_version' );
				if ( version_compare( $version, '2.0.0', '<' ) ) {
					set_transient( self::ID . '_transient', __( 'Korporacja Kurierska - Integracja została aktywowana. Jeśli wcześniej były zdefiniowane metody wysyłki przez integracje to prosimy o ich zaktualizowanie.', 'korporacja-kurierska' ) );
				}
				update_option( self::ID . '_version', self::VERSION );

				return self::VERSION;
			}

			/**
			 * Add description to shipping method
			 *
			 * @param $method
			 * @param $index
			 */
			public function shipping_rate_description( $method, $index ) {
				if ( $method->method_id == Shipping_Method::ID ) {
					$data        = explode( ':', $method->id );
					$description = get_option( 'woocommerce_' . $data[0] . '_' . $data[1] . '_settings' );

					if ( ! empty( $description['description'] ) ) {
						echo Plugin::load_template( 'rate-description', 'front', [
							'description' => $description['description']
						] );
					}
				}
			}
		}

		new Plugin();

		register_activation_hook( __FILE__, [ '\Korporacja\Plugin', 'activation' ] );
	}
}
