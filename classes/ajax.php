<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	public function __construct() {
		add_action( Bridge::WP_AJAX . 'korporacja_check_price', [ $this, 'check_price' ] );
		add_action( Bridge::WP_AJAX . 'korporacja_check_data', [ $this, 'check_data' ] );
		add_action( Bridge::WP_AJAX . 'korporacja_get_courier', [ $this, 'get_courier_template' ] );
		add_action( Bridge::WP_AJAX . 'korporacja_get_label', [ $this, 'get_label' ] );
		add_action( Bridge::WP_AJAX . 'korporacja_make_order', [ $this, 'make_order' ] );
		add_action( Bridge::WP_AJAX . 'korporacja_call_courier', [ $this, 'call_courier' ] );
		add_action( Bridge::WP_AJAX . 'korporacja_check_hours', [ $this, 'check_hours' ] );
	}

	/**
	 * Check price in Korporacja Kurierska
	 */
	public function check_price() {
		$order = wc_get_order( absint( $_POST['order_id'] ) );
		if ( $order instanceof \WC_Order ) {
			$packages = get_post_meta( $order->get_id(), '_' . Plugin::ID . '_packages', true );
			try {
				$response = Plugin::get_api()->checkPrices( Plugin::get_token(), [
					API::FIELD_PACKAGE_TYPE       => esc_html( $_POST[ API::FIELD_PACKAGE_TYPE ] ),
					API::FIELD_SENDER_COUNTRY     => 'PL',
					API::FIELD_SENDER_POST_CODE   => get_option( Plugin::ID . '_profile_sender_post_code' ),
					API::FIELD_RECEIVER_COUNTRY   => $order->get_shipping_country(),
					API::FIELD_RECEIVER_POST_CODE => $order->get_shipping_postcode(),
					API::FIELD_PACKAGES           => $packages
				] );

				$response['html'] = Plugin::load_template( 'courier-selector', 'admin/ajax', [
					'couriers'         => Courier::filter_couriers( $response['couriers']['courier'] ),
					'content'          => __( 'Zamówienie: #', 'korporacja-kurierska' ) . $order->get_order_number(),
					'selected_courier' => esc_html( get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_COURIER, true ) ),
					'selected_machine' => esc_html( get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_MACHINE, true ) ),
					'package_type'     => esc_html( $_POST[ API::FIELD_PACKAGE_TYPE ] )
				] );
			} catch ( \Exception $e ) {
				$response = [
					'status'  => 'ERROR',
					'message' => $e->getMessage()
				];
			}
		} else {
			$response = [
				'status'  => 'ERROR',
				'message' => __( 'Nie znaleziono zamówienia', 'korporacja-kurierska' )
			];
		}
		echo json_encode( $response );
		wp_die();
	}

	/**
	 *
	 */
	public function call_courier() {
		$order = wc_get_order( absint( $_POST['order_id'] ) );
		if ( $order instanceof \WC_Order ) {
			try {
				parse_str( $_POST['data'], $data );
				$data = Plugin::sanitize_value($data);
				if ( ! empty( $data['pickupHours'] ) ) {
					$hours                               = explode( '-', $data['pickupHours'] );
					$data[ API::FIELD_PICKUP_TIME_FROM ] = $hours[0];
					$data[ API::FIELD_PICKUP_TIME_TO ]   = $hours[1];
				}
				$response = Plugin::get_api()->bookCourier( Plugin::get_token(), $data );
				update_post_meta( $order->get_id(), '_' . Plugin::ID . '_order_courier', $data );
				$response['html'] = Plugin::load_template( 'order-courier-detail', 'admin/meta-box', [
					'data' => $data
				] );
			} catch ( \Exception $e ) {
				$response = [
					'status'  => 'ERROR',
					'message' => $e->getMessage()
				];
			}
		} else {
			$response = [
				'status'  => 'ERROR',
				'message' => __( 'Nie znaleziono zamówienia', 'korporacja-kurierska' )
			];
		}
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Load form for specific courier
	 */
	public function get_courier_template() {
		$order    = wc_get_order( absint( $_POST['order_id'] ) );
		$response = [
			'html' => ''
		];
		if ( $order instanceof \WC_Order ) {
			switch ( absint( $_POST['courier_id'] ) ) {
				case API::COURIER_DELTA_CITY:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_NO_COURIER_ORDER,
						API::FIELD_PICKUP_DATE,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_ROD,
						API::FIELD_DELIVERY_SATURDAY
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_DHL:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_NO_COURIER_ORDER,
						API::FIELD_PICKUP_DATE,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_DELIVERY_SATURDAY,
						API::FIELD_ROD
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_FEDEX:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_NO_COURIER_ORDER,
						API::FIELD_PICKUP_DATE,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_SMS_SENDING_NOTIFICATION,
						API::FIELD_ROD,
						API::FIELD_DELIVERY_SATURDAY
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_FEDEX_LOT:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_PICKUP_DATE,
						API::FIELD_DECLARED_VALUE,
						( $order->get_shipping_country() == 'PL' ) ? API::FIELD_PURPOSE : null,
						API::FIELD_SMS_SENDING_NOTIFICATION
					], [
						API::FIELD_DECLARED_VALUE => [
							'value' => $order->get_total()
						]
					] );
					break;

				case API::COURIER_GLS:
					$response['html'] = Courier::get_courier_fields( [ API::FIELD_PICKUP_DATE ] );
					break;

				case API::COURIER_INPOST_KURIER:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_NO_COURIER_ORDER,
						API::FIELD_PICKUP_DATE,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						( $order->get_shipping_country() != 'PL' ) ? API::FIELD_PURPOSE : null,
						API::FIELD_INFO_SERVICE_EMAIL,
						API::FIELD_INFO_SERVICE_SMS,
						API::FIELD_DELIVERY_SATURDAY,
						API::FIELD_ROD
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_KEX:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_PICKUP_DATE,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_SENDING_NOTIFICATION_EMAIL,
						API::FIELD_CONFIRMATION_SMS
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [ API::STD10, API::STD21 ] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_PACZKA_W_RUCHU:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_SENDER_MACHINE_NAME,
						API::FIELD_RECEIVER_MACHINE_NAME,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE
					], [
						API::FIELD_COD_TYPE              => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						],
						API::FIELD_SENDER_MACHINE_NAME   => [
							'options' => Courier::get_paczka_w_ruchu(),
							'value'   => get_option( Plugin::ID . '_setting_paczka_w_ruchu_machine' )
						],
						API::FIELD_RECEIVER_MACHINE_NAME => [
							'options' => Courier::get_paczka_w_ruchu(),
							'value'   => esc_html( get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_MACHINE, true ) )
						]
					] );

					break;

				case API::COURIER_PACZKOMATY:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_SENDER_MACHINE_NAME,
						API::FIELD_RECEIVER_MACHINE_NAME,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE
					], [
						API::FIELD_COD_TYPE              => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						],
						API::FIELD_SENDER_MACHINE_NAME   => [
							'options' => Courier::get_paczkomaty(),
							'value'   => get_option( Plugin::ID . '_setting_paczkomat_machine' )
						],
						API::FIELD_RECEIVER_MACHINE_NAME => [
							'options' => Courier::get_paczkomaty(),
							'value'   => esc_html( get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_MACHINE, true ) )
						]
					] );
					break;

				case API::COURIER_UPS_EXPRESS:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_NO_COURIER_ORDER,
						API::FIELD_PICKUP_DATE,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_NOTIFICATION_EMAIL,
						API::FIELD_DELIVERY_CONFIRMATION,
						API::FIELD_PRIVATE_RECEIVER,
						API::FIELD_ROD
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_UPS_STANDARD:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_NO_COURIER_ORDER,
						API::FIELD_PICKUP_DATE,
						( $order->get_shipping_country() == 'PL' ) ? API::FIELD_COD_AMOUNT : null,
						( $order->get_shipping_country() == 'PL' ) ? API::FIELD_COD_TYPE : null,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_NOTIFICATION_EMAIL,
						API::FIELD_DELIVERY_CONFIRMATION,
						API::FIELD_PRIVATE_RECEIVER,
						API::FIELD_ROD
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_UPS_ACCESS_POINT:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_PICKUP_DATE,
						API::FIELD_SENDER_MACHINE_NAME,
						API::FIELD_RECEIVER_MACHINE_NAME,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_NOTIFICATION_EMAIL,
						API::FIELD_DELIVERY_CONFIRMATION
					], [
						API::FIELD_SENDER_MACHINE_NAME   => [
							'options' => Courier::get_ups_access_point( get_option( Plugin::ID . '_profile_sender_city' ) ),
							'value'   => esc_html( get_option( Plugin::ID . '_setting_ups_access_point_machine' ) )
						],
						API::FIELD_RECEIVER_MACHINE_NAME => [
							'options' => Courier::get_ups_access_point( $order->get_billing_city(), $order->get_shipping_country() ),
							'value'   => esc_html( get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_MACHINE, true ) )
						],
						API::FIELD_COD_TYPE              => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				case API::COURIER_PATRON_SERVICE:
					$response['html'] = Courier::get_courier_fields( [
						API::FIELD_PICKUP_DATE,
						API::FIELD_COD_AMOUNT,
						API::FIELD_COD_TYPE,
						API::FIELD_DECLARED_VALUE,
						API::FIELD_CONFIRMATION_EMAIL,
						API::FIELD_CONFIRMATION_SMS,
						API::FIELD_INFO_SERVICE_EMAIL,
						API::FIELD_INFO_SERVICE_SMS,
						API::FIELD_ROD
					], [
						API::FIELD_COD_TYPE => [
							'options' => array_intersect_key( API::STD, array_flip( [
								API::STD7,
								API::STD10,
								API::STD21
							] ) ),
							'value'   => $this->get_std_default()
						]
					] );
					break;

				default:

					break;
			}
		}
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Check order data in Korporacja Kurierska
	 */
	public function check_data() {
		$order = wc_get_order( absint( $_POST['order_id'] ) );
		if ( $order instanceof \WC_Order ) {
			$packages = get_post_meta( $order->get_id(), '_' . Plugin::ID . '_packages', true );
			try {
				parse_str( $_POST['data'], $data );
				$data = Plugin::sanitize_value($data);
				$args = Plugin::prepare_order_data( $order, $packages, $data );

				$response          = Plugin::get_api()->checkData( Plugin::get_token(), $args );
				$response['price'] = esc_html( $response['grossPriceTotal'] );
			} catch ( \Exception $e ) {
				$response = [
					'status'  => 'ERROR',
					'message' => $e->getMessage()
				];
			}
		} else {
			$response = [
				'status'  => 'ERROR',
				'message' => __( 'Nie znaleziono zamówienia', 'korporacja-kurierska' )
			];
		}
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Create order in Korporacja Kurierska
	 */
	public function make_order() {
		$order = wc_get_order( absint( $_POST['order_id'] ) );
		if ( $order instanceof \WC_Order ) {
			$packages = get_post_meta( $order->get_id(), '_' . Plugin::ID . '_packages', true );
			try {
				parse_str( $_POST['data'], $data );
				$data = Plugin::sanitize_value($data);
				$args = Plugin::prepare_order_data( $order, $packages, $data );

				$response = Plugin::get_api()->makeOrder( Plugin::get_token(), $args );
				update_post_meta( $order->get_id(), '_' . Plugin::ID . '_order', $response['orderId'] );
				update_post_meta( $order->get_id(), '_' . Plugin::ID . '_order_args', $args );

				$status = get_option( Plugin::ID . '_setting_order_status' );
				if ( ! empty( $status ) ) {
					$order->update_status( $status, 'Korporacja Kurierska: ', true );
				}

				$response['html'] = Meta_Box::order_detail_content( $order, false );
			} catch ( \Exception $e ) {
				$response = [
					'status'  => 'ERROR',
					'message' => $e->getMessage()
				];
			}
		} else {
			$response = [
				'status'  => 'ERROR',
				'message' => __( 'Nie znaleziono zamówienia', 'korporacja-kurierska' )
			];
		}
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Render label for Korporacja Kurierska order
	 */
	public function get_label() {
		$order = wc_get_order( absint( $_GET['order_id'] ) );
		if ( $order instanceof \WC_Order ) {
			try {
				$korporacja_order_id = get_post_meta( $order->get_id(), '_' . Plugin::ID . '_order', true );
				switch ( $_GET['type'] ) {
					case 'zebra':
						$response = Plugin::get_api()->labelZebra( Plugin::get_token(), $korporacja_order_id );
						break;

					case 'protocol':
						$response = Plugin::get_api()->protocol( Plugin::get_token(), $korporacja_order_id );
						break;

					case 'document':
						$response = Plugin::get_api()->authorizationDocument( Plugin::get_token(), $korporacja_order_id );
						break;

					case 'proforma':
						$response = Plugin::get_api()->proforma( Plugin::get_token(), $korporacja_order_id );
						break;

					default:
						$response = Plugin::get_api()->label( Plugin::get_token(), $korporacja_order_id );
						break;
				}

				header( 'Content-type: application/pdf' );
				header( 'Content-Disposition: inline; filename="label_' . $korporacja_order_id . '.pdf"' );
				echo base64_decode( ! empty( $response['label'] ) ? $response['label'] : $response['document'] );

			} catch ( \Exception $e ) {
				wc_print_notice( $e->getMessage(), 'error' );
				wp_redirect( wp_get_referer() );
			}
		} else {
			wc_print_notice( __( 'Nie znaleziono zamówienia', 'korporacja-kurierska' ), 'error' );
			wp_redirect( wp_get_referer() );
		}
		wp_die();
	}

	/**
	 * Get hours for courier
	 */
	public function check_hours() {
		$order = wc_get_order( absint( $_POST['order_id'] ) );
		if ( $order instanceof \WC_Order ) {
			try {
				parse_str( $_POST['data'], $data );
				$data = Plugin::sanitize_value($data);
				$response = Courier::get_hours( $order, $data );
			} catch ( \Exception $e ) {
				$response = [
					'status'  => 'ERROR',
					'message' => $e->getMessage()
				];
			}
		} else {
			$response = [
				'status'  => 'ERROR',
				'message' => __( 'Nie znaleziono zamówienia', 'korporacja-kurierska' )
			];
		}
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * @return string
	 */
	private function get_std_default() {
		$cod_type = get_option( Plugin::ID . '_setting_cod_type' );

		return ! ( empty( $cod_type ) ) ? $cod_type : API::STD10;
	}
}
