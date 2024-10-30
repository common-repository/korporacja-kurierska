<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Courier {

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init() {
		$this->check_default_values();
	}

	/**
	 * Filter couriers by Korporacja Kurierska Settings
	 *
	 * @param array $couriers
	 *
	 * @return array
	 */
	public static function filter_couriers( $couriers = [] ) {
		$available = self::get_available_couriers();
		foreach ( $couriers as $key => $courier ) {
			if ( ! key_exists( $courier['id'], $available ) ) {
				unset( $couriers[ $key ] );
			}
		}

		return $couriers;
	}

	/**
	 * Check default values
	 */
	private function check_default_values() {
		$this->get_paczkomaty();
		$this->get_paczka_w_ruchu();
	}

	/**
	 * Return Paczkomaty points list
	 *
	 * @return bool|array
	 */
	public static function get_paczkomaty() {
		if ( get_option( Plugin::ID . "_valid_paczkomaty" ) < time() ) {
			try {
				$response = Plugin::get_api()->inpostMachines( Plugin::get_token() );
				$list     = self::prepare_paczkomaty_list( $response['machines']['machine'] );
				update_option( Plugin::ID . '_paczkomaty', $list );
				update_option( Plugin::ID . "_valid_paczkomaty", time() + Plugin::VALID_TIME_PACZKOMATY );

				return $list;
			} catch ( \Exception $e ) {
				/*echo Plugin::load_template( 'silence-error', 'admin', [
					'message' => 'Paczkomaty: ' . $e->getMessage()
				] );*/
				return false;
			}
		} else {
			return get_option( Plugin::ID . '_paczkomaty' );
		}
	}

	/**
	 * Reformat Paczkomaty data to simple structure
	 *
	 * @param array $points
	 *
	 * @return array
	 */
	private static function prepare_paczkomaty_list( $points = [] ) {
		$list = [];
		foreach ( $points as $point ) {
			$list[ $point['name'] ] = $point['description'];
		}

		return $list;
	}

	/**
	 * Return Paczka w Ruchu points list
	 *
	 * @return bool|array
	 */
	public static function get_paczka_w_ruchu() {
		if ( get_option( Plugin::ID . "_valid_paczka_w_ruchu" ) < time() ) {
			try {
				$response = Plugin::get_api()->pwrPoints( Plugin::get_token() );
				$list     = self::prepare_paczka_w_ruchu_list( $response['points']['point'] );
				update_option( Plugin::ID . '_paczka_w_ruchu', $list );
				update_option( Plugin::ID . "_valid_paczka_w_ruchu", time() + Plugin::VALID_TIME_PACZKA_W_RUCHU );

				return $list;
			} catch ( \Exception $e ) {
				/*echo $this->load_template( 'silence-error', 'admin', [
					'message' => 'Paczka w ruchu: ' . $e->getMessage()
				] );*/

				return false;
			}
		} else {
			return get_option( Plugin::ID . '_paczka_w_ruchu' );
		}
	}

	/**
	 * Reformat Paczka w Ruchu data to simple structure
	 *
	 * @param array $points
	 *
	 * @return array
	 */
	private static function prepare_paczka_w_ruchu_list( $points = [] ) {
		$list = [];
		foreach ( $points as $point ) {
			$list[ $point['destinationCode'] ] = $point['description'];
		}

		return $list;
	}

	/**
	 * Return UPS Access Point list base on city
	 *
	 * @param string $city
	 * @param string $country
	 *
	 * @return bool|array
	 */
	public static function get_ups_access_point( $city, $country = 'PL' ) {
		try {
			$response = Plugin::get_api()->upsAccessPoints( Plugin::get_token(), $city, $country );

			return self::prepare_ups_access_point_list( $response['points']['point'] );
		} catch ( \Exception $e ) {
			return [ '' => $e->getMessage() ];
		}
	}

	/**
	 * Reformat UPS Access Point data to simple structure
	 *
	 * @param array $points
	 *
	 * @return array
	 */
	private static function prepare_ups_access_point_list( $points = [] ) {
		$list = [];
		foreach ( $points as $point ) {
			$list[ $point['id'] ] = '[' . $point['id'] . '] ' . $point['name'] . ' - ' . $point['address'];
		}

		return $list;
	}

	/**
	 * Get available couriers base or plugin settings
	 *
	 * @return array
	 */
	public static function get_available_couriers() {
		$couriers = get_option( Plugin::ID . '_couriers' );
		$all      = API::COURIERS;

		foreach ( $all as $key => $values ) {
			if ( ! in_array( $key, (array) $couriers ) ) {
				unset( $all[ $key ] );
			}
		}

		return $all;
	}

	/**
	 * Courier fields and options
	 * @return array
	 */
	public static function courier_fields() {
		return [
			API::FIELD_NO_COURIER_ORDER           => [
				'label' => __( 'Nie zamawiaj kuriera dla tej przesyłki', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_PICKUP_DATE                => [
				'label'             => __( 'Data nadania', 'korporacja-kurierska' ),
				'type'              => 'text',
				'placeholder'       => 'yyyy-mm-dd',
				'input_class'       => [ 'datepicker-future', 'korporacja-pickup-date' ],
				'custom_attributes' => [ 'readonly' => 'readonly' ],
				'value'             => date( 'Y-m-d', strtotime( '+1 day', time() ) )
			],
			API::FIELD_COD_AMOUNT                 => [
				'label'       => __( 'Wartość pobrania', 'korporacja-kurierska' ),
				'type'        => 'text',
				'placeholder' => __( 'Brak', 'korporacja-kurierska' ),
				'description' => '<a href="#" class="button-copy-order-value">' . __( 'wstaw wartość zamówienia', 'korporacja-kurierska' ) . '</a>'
			],
			API::FIELD_COD_TYPE                   => [
				'label' => __( 'Rodzaj pobrania', 'korporacja-kurierska' ),
				'type'  => 'select'
			],
			API::FIELD_DECLARED_VALUE             => [
				'label'       => __( 'Wartość ubezpieczenia', 'korporacja-kurierska' ),
				'type'        => 'text',
				'placeholder' => __( 'Brak', 'korporacja-kurierska' ),
				'description' => '<a href="#" class="button-copy-order-value">' . __( 'wstaw wartość zamówienia', 'korporacja-kurierska' ) . '</a>'
			],
			API::FIELD_SMS_SENDING_NOTIFICATION   => [
				'label' => __( 'Powiadomienie SMS o nadaniu przesyłki', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_PURPOSE                    => [
				'label'   => __( 'Przeznaczenie przesyłki', 'korporacja-kurierska' ),
				'type'    => 'select',
				'options' => [
					'GIFT'              => __( 'Prezent', 'korporacja-kurierska' ),
					'NOT_SOLD'          => __( 'Rzeczy do użytku własnego', 'korporacja-kurierska' ),
					'PERSONAL_EFFECTS'  => __( 'Przedmioty osobiste', 'korporacja-kurierska' ),
					'REPAIR_AND_RETURN' => __( 'Naprawa i zwrot', 'korporacja-kurierska' ),
					'SAMPLE'            => __( 'Próbka', 'korporacja-kurierska' ),
					'SOLD'              => __( 'Firmowy', 'korporacja-kurierska' ),
				]
			],
			API::FIELD_INFO_SERVICE_SMS           => [
				'label' => __( 'Serwis SMS', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_INFO_SERVICE_EMAIL         => [
				'label' => __( 'Serwis E-mail', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_SENDING_NOTIFICATION_EMAIL => [
				'label' => __( 'Awizacja odbioru e-mail', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_SENDING_NOTIFICATION_SMS   => [
				'label' => __( 'Awizacja odbioru SMS', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_DELIVERY_NOTIFICATION_SMS  => [
				'label' => __( 'Awizacja dostawy SMS', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_CONFIRMATION_SMS           => [
				'label' => __( 'Potwierdzenie dostarczenia SMS', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_NOTIFICATION_EMAIL         => [
				'label' => __( 'Powiadomienie e-mail', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_DELIVERY_CONFIRMATION      => [
				'label' => __( 'Potwierdzenie doręczenia', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_SENDER_MACHINE_NAME        => [
				'label'       => __( 'Punkt nadania', 'korporacja-kurierska' ),
				'type'        => 'select',
				'input_class' => [ 'add-select2' ],
			],
			API::FIELD_RECEIVER_MACHINE_NAME      => [
				'label'       => __( 'Punkt odbioru', 'korporacja-kurierska' ),
				'type'        => 'select',
				'input_class' => [ 'add-select2' ],
			],
			API::FIELD_ROD                        => [
				'label' => __( 'Zwrot dokumentów', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_PRIVATE_RECEIVER           => [
				'label' => __( 'Doręczenie pod adres prywatny', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
			API::FIELD_DELIVERY_SATURDAY          => [
				'label' => __( 'Dostawa w sobotę', 'korporacja-kurierska' ),
				'type'  => 'checkbox'
			],
		];
	}

	/**
	 * Return html for courier fields
	 *
	 * @param array $fields
	 * @param array $data
	 *
	 * @return string
	 */
	public static function get_courier_fields( $fields = [], $data = [] ) {
		$all = self::courier_fields();
		ob_start();
		foreach ( $fields as $field ) {
			if ( ! empty( $all[ $field ] ) ) {
				$options = $all[ $field ];
				if ( ! empty( $data[ $field ] ) ) {
					$options = array_merge( $options, $data[ $field ] );
				}
				woocommerce_form_field( $field, $options, ! empty( $options['value'] ) ? $options['value'] : false );
			}
		}

		return ob_get_clean();
	}

	/**
	 * Get couriers arrive hours
	 *
	 * @param \WC_Order $order
	 * @param array $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function get_hours( \WC_Order $order, $data = [] ) {
		$packages = get_post_meta( $order->get_id(), '_' . Plugin::ID . '_packages', true );
		switch ( intval( $data['courier_id'] ) ) {
			case API::COURIER_UPS_STANDARD:
				$response = Plugin::get_api()->upsHours( Plugin::get_token(), [
					API::FIELD_DATE             => $data[ API::FIELD_PICKUP_DATE ],
					API::FIELD_SENDER_COUNTRY   => 'PL',
					API::FIELD_SENDER_POST_CODE => get_option( Plugin::ID . '_profile_sender_post_code' ),
					API::FIELD_WEIGHT           => self::calculate_package_weight( $packages ),
					API::FIELD_PACKAGES_NUMBER  => count( $packages ),
					API::FIELD_SERVICE_TYPE     => 'UPS Standard'
				] );

				break;

			case API::COURIER_UPS_EXPRESS:
				$response = Plugin::get_api()->upsHours( Plugin::get_token(), [
					API::FIELD_DATE             => $data[ API::FIELD_PICKUP_DATE ],
					API::FIELD_SENDER_COUNTRY   => 'PL',
					API::FIELD_SENDER_POST_CODE => get_option( Plugin::ID . '_profile_sender_post_code' ),
					API::FIELD_WEIGHT           => self::calculate_package_weight( $packages ),
					API::FIELD_PACKAGES_NUMBER  => count( $packages ),
					API::FIELD_SERVICE_TYPE     => 'UPS Express Saver'
				] );

				break;

			case API::COURIER_DHL:
				$response = Plugin::get_api()->dhlHours( Plugin::get_token(), [
					API::FIELD_DATE      => $data[ API::FIELD_PICKUP_DATE ],
					API::FIELD_TYPE      => self::get_dhl_package_type( self::calculate_package_weight( $packages ), $_POST[ API::FIELD_PACKAGE_TYPE ] ),
					API::FIELD_POST_CODE => get_option( Plugin::ID . '_profile_sender_post_code' )
				] );

				break;

			case API::COURIER_FEDEX_LOT:
				$response = Plugin::get_api()->fedexIntHours( Plugin::get_token(), [
					API::FIELD_DATE             => $data[ API::FIELD_PICKUP_DATE ],
					API::FIELD_SENDER_COUNTRY   => 'PL',
					API::FIELD_SENDER_POST_CODE => get_option( Plugin::ID . '_profile_sender_post_code' )
				] );

				break;

			default:

				break;
		}

		if ( ! empty( $response['timeSlots']['timeSlot'] ) ) {
			$response['html'] = woocommerce_form_field( 'pickupHours', [
				'label'   => __( 'Godzina podjazdu kuriera', 'korporacja-kurierska' ),
				'type'    => 'select',
				'options' => self::prepare_time_slots( $response['timeSlots']['timeSlot'] ),
				'return'  => true
			] );
		}

		return $response;
	}

	/**
	 * Set DHL package type by package type and weight
	 *
	 * @param $weight
	 * @param $packageType
	 *
	 * @return string
	 */
	public static function get_dhl_package_type( $weight, $packageType ) {
		switch ( $packageType ) {
			case 'paleta':
				return 'dr';
			default:
				return $weight <= 31.5 ? 'ex' : 'dr';
		}
	}

	/**
	 * Calculate package weight
	 *
	 * @param array $packages
	 *
	 * @return int
	 */
	public static function calculate_package_weight( $packages = [] ) {
		$weight = 0;
		if ( ! empty( $packages ) ) {
			foreach ( $packages as $package ) {
				if ( ! empty( $package[ API::FIELD_WEIGHT ] ) ) {
					$weight += $package[ API::FIELD_WEIGHT ];
				}
			}
		}

		return $weight;
	}

	/**
	 * Prepare time slots from couriers hours
	 *
	 * @param array $time_slots
	 *
	 * @return array
	 */
	public static function prepare_time_slots( $time_slots = [] ) {
		$options = [];

		if ( ! empty( $time_slots['timeFrom'] ) && ! empty( $time_slots['timeTo'] ) ) {
			$value             = $time_slots['timeFrom'] . '-' . $time_slots['timeTo'];
			$options[ $value ] = $value;

			return $options;
		}

		if ( ! empty( $time_slots ) ) {
			foreach ( $time_slots as $slot ) {
				$value             = $slot['timeFrom'] . '-' . $slot['timeTo'];
				$options[ $value ] = $value;
			}
		}

		return $options;
	}
}
