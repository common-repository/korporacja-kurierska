<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Base_Plugin {

	const ID = 'korporacja_kurierska';
	const VERSION = '2.1.2';

	protected $plugin_namespace = "korporacja-kurierska";

	private $plugin_url;
	private $plugin_file_path;
	private $plugin_path;
	public static $api;

	public function __construct() {
		$this->init_base_variables();

		add_action( Bridge::PLUGINS_LOADED, [ $this, 'init_base_korporacja_kurierska' ], 1000 );
		add_action( Bridge::ADMIN_NOTICES, [ $this, 'transient_admin_notice' ] );
	}

	/**
	 * Setup base variables
	 */
	public function init_base_variables() {
		$reflection             = new \ReflectionClass( $this );
		$this->plugin_url       = plugin_dir_url( $reflection->getFileName() );
		$this->plugin_file_path = $reflection->getFileName();
		$this->plugin_path      = dirname( $reflection->getFileName() );

		$this->set_api( new API( get_option( self::ID . '_api_type' ) == "prod" ) );
		load_plugin_textdomain( 'korporacja-kurierska', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Plugin hooks
	 */
	public function init_base_korporacja_kurierska() {
		require_once( plugin_dir_path( __FILE__ ) . '../shipping-method.php' );

		add_action( Bridge::ADMIN_ENQUEUE_SCRIPTS, [ $this, 'enqueue_admin_scripts' ], 75 );
		add_action( Bridge::WC_UPDATE_OPTIONS_SHIPPING . self::ID, [
			$this,
			'process_admin_options'
		] );
		add_filter( Bridge::PLUGIN_ACTION_LINKS . plugin_basename( $this->get_plugin_file_path() ), [
			$this,
			'links_filter'
		] );
		add_action( Bridge::WC_ADMIN_FIELD . 'multi_checkbox', [ $this, 'render_multi_checkbox' ] );
	}

	/**
	 * Get plugin file path
	 *
	 * @return string
	 */
	public function get_plugin_file_path() {
		return $this->plugin_file_path;
	}


	/**
	 * Print error message depend on user page
	 *
	 * @param string $message
	 */
	public function print_notice( $message ) {
		if ( is_admin() ) {
			$this->print_admin_notice( $message );
		} else {
			$this->print_front_notice( $message );
		}
	}

	/**
	 * Print message in admin style
	 *
	 * @param string $message
	 * @param string $class
	 */
	public static function print_admin_notice( $message, $class = 'notice notice-error' ) {
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, esc_html( $message ) );
	}

	/**
	 * Print message in front style using WC notices
	 *
	 * @param string $message
	 * @param string $type
	 */
	public function print_front_notice( $message, $type = 'error' ) {
		wc_print_notice( esc_html( $message ), $type );
	}

	/**
	 * Render multi checkbox for WC fields
	 *
	 * @param array $value
	 */
	public function render_multi_checkbox( $value = [] ) {
		echo $this->load_template( 'multi-checkbox', 'fields', [
			'value' => $value
		] );
	}

	/**
	 * Enqueue admin css and js
	 */
	function enqueue_admin_scripts() {
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'korporacja-kurierska-admin', $this->get_plugin_url() . 'assets/build/js/scripts.js', [ 'jquery' ], self::VERSION );
		wp_enqueue_style( 'korporacja-kurierska-admin', $this->get_plugin_url() . 'assets/build/css/style.css', [], self::VERSION );
	}

	/**
	 * Get plugin URL
	 *
	 * @return string
	 */
	public function get_plugin_url() {
		return esc_url( trailingslashit( $this->plugin_url ) );
	}

	/**
	 * Return template html
	 *
	 * @param string $name
	 * @param string $path
	 * @param array $args
	 *
	 * @return string
	 */
	public static function load_template( $name, $path = '', $args = [] ) {
		$reflection    = new \ReflectionClass( '\Korporacja\Plugin' );
		$path          = trim( $path, '/' );
		$template_name = implode( '/', [ get_template_directory(), 'templates', $path, $name . '.php' ] );

		if ( ! file_exists( $template_name ) ) {
			$template_name = implode( '/', [
				dirname( $reflection->getFileName() ),
				'templates',
				$path,
				$name . '.php'
			] );
		}

		extract( $args );

		ob_start();
		include( $template_name );

		return ob_get_clean();
	}

	/**
	 * Save post meta base on given $_POST array keys
	 *
	 * @param array $keys
	 * @param int $post_id
	 */
	public static function update_post_meta( $keys = [], $post_id ) {
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $key ) {
				if ( isset( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, '_' . $key, self::sanitize_value( $_POST[ $key ] ) );
				}
			}
		}
	}

	/**
	 * Sanitize value base on type
	 *
	 * @param $value
	 *
	 * @return array|string
	 */
	public static function sanitize_value( $value ) {
		if ( ! is_array( $value ) ) {
			$value = sanitize_text_field( $value );
		} else {
			$value = self::sanitize_recursive( $value );
		}

		return $value;
	}

	/**
	 * @param array $array
	 *
	 * @return array
	 */
	public static function sanitize_recursive( $array = [] ) {
		if ( ! empty( $array ) ) {
			foreach ( $array as $key => $item ) {
				if ( is_array( $item ) ) {
					$array[ $key ] = self::sanitize_recursive( $item );
				} else {
					$array[ $key ] = sanitize_text_field( $item );
				}
			}
		}

		return $array;
	}

	/**
	 * Add links to WordPress plugin page
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$plugin_links = [
			'<a href="' . admin_url( 'admin.php?page=' . self::ID ) . '">' . __( 'Ustawienia', 'korporacja-kurierska' ) . '</a>',
			'<a href="https://www.korporacjakurierska.pl/strona/wyswietl/186/integracja-z-woocommerce">' . __( 'Dokumentacja', 'korporacja-kurierska' ) . '</a>',
			'<a href="https://www.korporacjakurierska.pl/strona/wyswietl/186/integracja-z-woocommerce">' . __( 'Wsparcie', 'korporacja-kurierska' ) . '</a>',
		];

		return array_merge( $plugin_links, $links );
	}

	/**
	 * @return API
	 */
	public static function get_api() {
		return self::$api;
	}

	/**
	 * @param API $api
	 */
	public static function set_api( API $api ) {
		self::$api = $api;
	}

	/**
	 * Print admin notice front transient
	 */
	public function transient_admin_notice() {
		$message = get_transient( self::ID . '_transient' );
		delete_transient( self::ID . '_transient' );

		if ( ! empty( $message ) ) {
			self::print_admin_notice( $message );
		}
	}
}
