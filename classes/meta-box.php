<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Meta_Box {

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init() {
		add_action( Bridge::SAVE_POST, [ $this, 'save_packages' ], 10, 1 );
		add_action( Bridge::SAVE_POST, [ $this, 'save_override' ], 10, 1 );

		add_action( Bridge::ADD_META_BOXES, [ $this, 'packages' ], 10, 2 );
		add_action( Bridge::ADD_META_BOXES, [ $this, 'order' ], 10, 2 );
		add_action( Bridge::ADD_META_BOXES, [ $this, 'order_call_courier' ], 10, 2 );
		add_action( Bridge::ADD_META_BOXES, [ $this, 'order_override' ], 10, 2 );
	}

	/**
	 * Add order meta box to WC Order
	 *
	 * @param $post
	 */
	public function order( $post_type, $post ) {
		if ( ! empty( Plugin::check_kk_for_order( $post->ID ) ) ) {
			$korporacja_order = get_post_meta( $post->ID, '_' . Plugin::ID . '_order', true );
			add_meta_box( $this->get_id( __FUNCTION__ ), 'Korporacja Kurierska  <br />' . __( 'Nadawanie przesyłki', 'korporacja-kurierska' ), [
				$this,
				empty( $korporacja_order ) ? 'order_content' : 'order_detail_content'
			], 'shop_order', 'side' );
			add_filter( $this->get_hook( $this->get_id( __FUNCTION__ ) ), [ $this, 'korporacja_postbox' ] );
		}
	}

	/**
	 * Render order meta box in WC Order admin edit page
	 *
	 * @param $post
	 */
	public function order_content( $post ) {
		$order   = wc_get_order( $post );
		$courier = get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_COURIER, true );
		$machine = get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_MACHINE, true );

		echo Plugin::load_template( 'order', 'admin/meta-box', [
			'plugin'         => $this,
			'order'          => $order,
			'courier'        => esc_html( $courier ),
			'machine'        => $this->get_machine_name( $courier, $machine ),
			'packages_value' => get_post_meta( $order->get_id(), '_' . Plugin::ID . '_packages', true )
		] );
	}

	/**
	 * Render order detail meta box in WC Order admin edit page
	 *
	 * @param $post
	 * @param bool $echo
	 *
	 * @return string
	 */
	public static function order_detail_content( $post, $echo = true ) {
		$order               = wc_get_order( $post );
		$korporacja_order_id = esc_html( get_post_meta( $post->ID, '_' . Plugin::ID . '_order', true ) );
		$courier             = get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_COURIER, true );
		$machine             = get_post_meta( $order->get_id(), '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_MACHINE, true );
		$response            = [];
		$html                = '';

		try {
			$response = Plugin::get_api()->order( Plugin::get_token(), $korporacja_order_id );
		} catch ( \Exception $e ) {
			$html .= __( 'Nastąpił problem z pobraniem danych zamówienia', 'korporacja-kurierska' );
			Plugin::print_admin_notice( $e->getMessage() );
		}

		$html .= Plugin::load_template( 'order-detail', 'admin/meta-box', [
			'plugin'                  => new Plugin(),
			'order'                   => $order,
			'courier'                 => esc_html( $courier ),
			'machine'                 => self::get_machine_name( $courier, $machine ),
			'packages_value'          => get_post_meta( $order->get_id(), '_' . Plugin::ID . '_packages', true ),
			'korporacja_order_id'     => $korporacja_order_id,
			'korporacja_order_detail' => $response['orderDetails']
		] );

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Add order override meta box to WC Order
	 *
	 * @param $post
	 */
	public function order_override( $post_type, $post ) {
		if ( empty( get_post_meta( $post->ID, '_' . Plugin::ID . '_order', true ) ) ) {
			add_meta_box( $this->get_id( __FUNCTION__ ), 'Korporacja Kurierska  <br />' . __( 'Kontrola integracji', 'korporacja-kurierska' ), [
				$this,
				'order_override_content'
			], 'shop_order', 'side' );
		}
	}

	/**
	 * Render order override meta box in WC Order admin edit page
	 *
	 * @param $post
	 */
	public function order_override_content( $post ) {
		$order = wc_get_order( $post );

		echo Plugin::load_template( 'order-override', 'admin/meta-box', [
			'plugin' => $this,
			'order'  => $order,
			'status' => Plugin::check_kk_for_order( $post->ID )
		] );
	}

	/**
	 * Add packages meta box to WC Order
	 *
	 * @param $post_type
	 * @param $post
	 */
	public function packages( $post_type, $post ) {
		$support[] = Package_Template::POST_TYPE_NAME;
		if ( ! empty( Plugin::check_kk_for_order( $post->ID ) ) && $post_type == 'shop_order' ) {
			$support[] = $post_type;
		}

		add_meta_box( $this->get_id( __FUNCTION__ ), 'Korporacja Kurierska - ' . __( 'Paczki', 'korporacja-kurierska' ), [
			$this,
			'packages_content'
		], $support, 'normal' );
		add_filter( $this->get_hook( $this->get_id( __FUNCTION__ ) ), [ $this, 'korporacja_postbox' ] );

		if ( in_array( 'shop_order', $support ) ) {
			$korporacja_order_id = get_post_meta( $post->ID, '_' . Plugin::ID . '_order', true );
			if ( ! empty( $korporacja_order_id ) ) {
				add_filter( $this->get_hook( $this->get_id( __FUNCTION__ ) ), [ $this, 'disable_meta_box' ] );
			}
		}
	}

	/**
	 * Disable meta box via css class
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function disable_meta_box( $classes = [] ) {
		$classes[] = 'disable-meta-box no-spinner';

		return $classes;
	}

	/**
	 * Add class for Korporacja metabox
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function korporacja_postbox( $classes = [] ) {
		$classes[] = 'kk-postbox';

		return $classes;
	}

	/**
	 * Render order meta box in WC Order admin edit page
	 *
	 * @param $post
	 */
	public function packages_content( $post ) {
		$wc_assets = new \WC_Admin_Assets();
		$wc_assets->admin_scripts();
		wp_enqueue_script( 'woocommerce_admin' );
		$wc_assets->admin_styles();
		wp_enqueue_style( 'woocommerce_admin_styles' );

		echo Plugin::load_template( 'packages', 'admin/meta-box', [
			'plugin'         => $this,
			'courier'        => get_post_meta( $post->ID, '_' . Shipping_Method::ID . '_' . Shipping_Method::META_FIELD_COURIER, true ),
			'packages_value' => get_post_meta( $post->ID, '_' . Plugin::ID . '_packages', true )
		] );
	}

	/**
	 * Add call courier meta box to WC Order
	 *
	 * @param $post
	 */
	public function order_call_courier( $post_type, $post ) {
		$args         = get_post_meta( $post->ID, '_' . Plugin::ID . '_order_args', true );
		$courier_data = get_post_meta( $post->ID, '_' . Plugin::ID . '_order_courier', true );
		if ( ! empty( $args ) && ! empty( $args[ API::FIELD_NO_COURIER_ORDER ] ) ) {
			add_meta_box( $this->get_id( __FUNCTION__ ), 'Korporacja Kurierska  <br />' . __( 'Zamów kuriera', 'korporacja-kurierska' ), [
				$this,
				empty( $courier_data ) ? 'order_call_courier_content' : 'order_courier_detail_content'
			], 'shop_order', 'side' );
			add_filter( $this->get_hook( $this->get_id( __FUNCTION__ ) ), [ $this, 'korporacja_postbox' ] );
		}
	}

	/**
	 * Render call courier meta box
	 *
	 * @param $post
	 */
	public function order_call_courier_content( $post ) {
		$order = wc_get_order( $post->ID );
		if ( $order instanceof \WC_Order ) {
			$args = get_post_meta( $post->ID, '_' . Plugin::ID . '_order_args', true );
			echo Plugin::load_template( 'order-call-courier', 'admin/meta-box', [
				'order'      => $order,
				'order_args' => $args,
				'order_id'   => get_post_meta( $post->ID, '_' . Plugin::ID . '_order', true ),
				'form'       => Courier::get_courier_fields( [ API::FIELD_PICKUP_DATE ] )
			] );
		}
	}

	/**
	 * Render call courier meta box
	 *
	 * @param $post
	 */
	public function order_courier_detail_content( $post ) {
		echo Plugin::load_template( 'order-courier-detail', 'admin/meta-box', [
			'data' => get_post_meta( $post->ID, '_' . Plugin::ID . '_order_courier', true )
		] );
	}

	/**
	 * Get ID for metabox
	 *
	 * @param $name
	 *
	 * @return string
	 */
	private function get_id( $name ) {
		return Plugin::ID . '_' . $name;
	}

	/**
	 * Get hook name for metabox id
	 *
	 * @param $id
	 *
	 * @return string
	 */
	private function get_hook( $id ) {
		return Bridge::POSTBOX_CLASSES_SHOP_ORDER . $id;
	}

	/**
	 * Save packages data to orders and templates
	 *
	 * @param $post_id
	 */
	public function save_packages( $post_id ) {
		if ( in_array( get_post_type( $post_id ), [ 'shop_order', Package_Template::POST_TYPE_NAME ] ) ) {
			Plugin::update_post_meta( [ Plugin::ID . '_packages' ], $post_id );
		}
	}

	/**
	 * Save korporacja override for wc order
	 *
	 * @param $post_id
	 */
	public function save_override( $post_id ) {
		if ( in_array( get_post_type( $post_id ), [
				'shop_order',
				Package_Template::POST_TYPE_NAME
			] ) && isset( $_POST[ Plugin::ID . '_enable_submit' ] ) ) {
			Plugin::update_post_meta( [ Plugin::ID . '_enable' ], $post_id );
		}
	}

	/**
	 * Return machine name base of machine key
	 *
	 * @param int $courier_id
	 * @param string $machine_id
	 *
	 * @return mixed
	 */
	public static function get_machine_name( $courier_id, $machine_id ) {
		switch ( $courier_id ) {
			case API::COURIER_PACZKA_W_RUCHU:
				$machine_list = Courier::get_paczka_w_ruchu();
				break;

			case API::COURIER_PACZKOMATY:
				$machine_list = Courier::get_paczkomaty();
				break;

			default:
				return false;
				break;
		}

		if ( ! empty( $machine_list[ $machine_id ] ) ) {
			return esc_html( $machine_list[ $machine_id ] );
		}

		return esc_html( $machine_id ) . Plugin::load_template( 'error', 'admin', [ 'message' => __( 'Prawdopodobnie punkt wybrany przez klienta nie jest dostępny. Sprawdź punkt odbioru przesyłki.', 'korporacja-kurierska' ) ] );
	}
}
