<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Setting {

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'init', [ $this, 'save_settings' ] );
	}

	public function init() {
		add_filter( Bridge::ADMIN_MENU, [ $this, 'add_menu_pages' ], 50 );
	}

	/**
	 * Plugin setting page variables
	 * @return array
	 */
	public function get_settings() {
		return apply_filters( 'wc_settings_' . Plugin::ID . '_settings', array_merge(
			$this->get_settings_api(),
			$this->get_settings_base(),
			$this->get_settings_profile(),
			$this->get_settings_couriers()
		) );
	}

	/**
	 * Save Plugin settings
	 */
	public function save_settings() {
		foreach ( $this->get_settings() as $key => $setting ) {
			if ( isset( $_POST[ Plugin::ID . '_' . $key ] ) ) {
				update_option( Plugin::ID . '_' . $key, Plugin::sanitize_value( $_POST[ Plugin::ID . '_' . $key ] ) );
			}
		}

		if ($this->api_settings_changed()) {
			Plugin::get_api_token();
		}
	}

	/**
	 * Check if something in api change
	 *
	 * @return bool
	 */
	public function api_settings_changed() {
		$changed = false;
		foreach ( $this->get_settings_api() as $key => $setting ) {
			if ( isset( $_POST[ Plugin::ID . '_' . $key ] ) ) {
				$changed = true;
			}
		}
		return $changed;
	}

	/**
	 * Add Korporacja Kurierska setting to Admin menu
	 */
	public function add_menu_pages() {
		add_submenu_page( Plugin::ID, __( 'Ustawienia', 'korporacja-kurierska' ), __( 'Ustawienia', 'korporacja-kurierska' ), 'manage_options', Plugin::ID, [
			$this,
			'setting_page'
		] );
	}

	/**
	 * Catch woocommerce buffer and return as string
	 *
	 * @param $setting
	 *
	 * @return string
	 */
	public function get_settings_content( $setting ) {
		ob_start();
		woocommerce_admin_fields( $setting );

		return ob_get_clean();
	}

	/**
	 * Render setting page for Korporacja Kurierska
	 */
	public function setting_page() {
		update_option( Plugin::ID . '_valid_token', null );
		update_option( Plugin::ID . '_api_token', null );
		$this->setting_header();
		if ( Plugin::get_token() ) {
			Plugin::print_admin_notice( __( 'Połączenie z API aktywne.', 'korporacja-kurierska' ), 'notice notice-success' );
		} else {
			Plugin::print_admin_notice( __( 'Brak połączenia z API.', 'korporacja-kurierska' ) );
		}

		echo Plugin::load_template( 'setting', 'admin/setting', [
			'settings' => [
				'api'     => [
					'name'    => __( 'API', 'korporacja_kurierska' ),
					'content' => $this->get_settings_content( $this->get_settings_api() )
				],
				'base'    => [
					'name'    => __( 'Ustawienia', 'korporacja_kurierska' ),
					'content' => $this->get_settings_content( $this->get_settings_base() )
				],
				'profile' => [
					'name'    => __( 'Adres nadawczy', 'korporacja_kurierska' ),
					'content' => $this->get_settings_content( $this->get_settings_profile() )
				],
				'courier' => [
					'name'    => __( 'Kurierzy', 'korporacja_kurierska' ),
					'content' => $this->get_settings_content( $this->get_settings_couriers() )
				],
				'support' => [
					'name'    => __( 'Wsparcie', 'korporacja_kurierska' ),
					'content' => $this->get_settings_content( $this->get_settings_support() )
				]
			]
		] );
	}

	/**
	 * Render setting header for Korporacja Kurierska
	 */
	public function setting_header() {
		echo Plugin::load_template( 'header', 'admin/setting', [
			'menu' => [
				[
					'url'  => menu_page_url( Plugin::ID, false ),
					'name' => __( 'Ustawienia', 'korporacja-kurierska' )
				],
				[
					'url'  => menu_page_url( Plugin::ID . '_templates', false ),
					'name' => __( 'Szablony paczek', 'korporacja-kurierska' )
				],
			]
		] );
	}

	/**
	 * Plugin api settings
	 *
	 * @return array
	 */
	public function get_settings_api() {
		return [
			'api_section_title' => [
				'name' => __( 'API', 'korporacja-kurierska' ),
				'type' => 'title',
				'desc' => __( 'Należy zwrócić uwagę, że hasło API jest hasłem niezależnym od podstawowego hasła użytkownika. Konta użytkownika nie mają automatycznie zdefiniowanych haseł dla środowiska API, dlatego przed rozpoczęciem integracji należy zdefiniować hasło w profilu swojego konta. Brak zdefiniowanego hasła uniemożliwia skorzystanie z API.', 'korporacja-kurierska' ),
				'id'   => Plugin::ID . '_section_title'
			],
			'api_email'         => [
				'name' => __( 'E-mail', 'korporacja-kurierska' ),
				'type' => 'email',
				'id'   => Plugin::ID . '_api_email'
			],
			'api_password'      => [
				'name' => __( 'Hasło API', 'korporacja-kurierska' ),
				'type' => 'password',
				'id'   => Plugin::ID . '_api_password'
			],
			/*'api_type'          => [
				'name'    => __( 'Rodzaj API', 'korporacja-kurierska' ),
				'type'    => 'radio',
				'desc'    => __( 'Domyślnie wybrane jest API testowe', 'korporacja-kurierska' ),
				'id'      => Plugin::ID . '_api_type',
				'options' => [
					'prod' => __( 'Produkcyjne', 'korporacja_kurirska' ),
					//'test' => __( 'Testowe', 'korporacja_kurirska' )
				],
			],*/
			'section_end'       => [
				'type' => 'sectionend',
				'id'   => Plugin::ID . '_api_section_end'
			]
		];
	}

	/**
	 * Plugin base settings
	 *
	 * @return array
	 */
	public function get_settings_base() {
		$order_statuses = wc_get_order_statuses();
		unset( $order_statuses['wc-pending'] );
		unset( $order_statuses['wc-on-hold'] );
		unset( $order_statuses['wc-cancelled'] );
		unset( $order_statuses['wc-refunded'] );
		unset( $order_statuses['wc-failed'] );

		wp_enqueue_script( 'select2');
		wp_enqueue_style( 'woocommerce_admin_styles' );

		return [
			'setting_section_title'          => [
				'name' => __( 'Ustawienia', 'korporacja-kurierska' ),
				'type' => 'title',
				'id'   => Plugin::ID . '_setting_section_title'
			],
			'setting_payment_type'           => [
				'name'    => __( 'Płatność', 'korporacja-kurierska' ),
				'type'    => 'select',
				'options' => [
					1 => __( 'płatność online za złożone zamówienie', 'korporacja-kurierska' ),
					2 => __( 'płatność z salda konta', 'korporacja-kurierska' ),
					3 => __( 'płatność abonamentem', 'korporacja-kurierska' ),
				],
				'id'      => Plugin::ID . '_setting_payment_type'
			],
			'setting_cod_bank'               => [
				'name' => __( 'Numer konta dla pobrań', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_setting_cod_bank',
			],
			'setting_cod_type'               => [
				'name'    => __( 'Domyślne pobranie', 'korporacja-kurierska' ),
				'type'    => 'select',
				'options' => array_merge( [ '' => __( 'Brak', 'korporacja-kurierska' ) ], API::STD ),
				'id'      => Plugin::ID . '_setting_cod_type',
			],
			'setting_paczkomat_machine'      => [
				'name'    => __( 'Domyślny paczkomat nadania', 'korporacja-kurierska' ),
				'type'    => 'select',
				'id'      => Plugin::ID . '_setting_paczkomat_machine',
				'options' => Courier::get_paczkomaty(),
				'class'   => 'add-select2-tab'
			],
			'setting_paczka_w_ruchu_machine' => [
				'name'    => __( 'Domyślny punkt nadania Paczka w Ruchu', 'korporacja-kurierska' ),
				'type'    => 'select',
				'id'      => Plugin::ID . '_setting_paczka_w_ruchu_machine',
				'options' => Courier::get_paczka_w_ruchu(),
				'class'   => 'add-select2-tab'
			],
			'setting_order_status'           => [
				'name'    => __( 'Po wysłaniu zmień status zamówienia', 'korporacja-kurierska' ),
				'type'    => 'select',
				'options' => array_merge( [ '' => __( 'Nie zmieniaj', 'korporacja-kurierska' ) ], $order_statuses ),
				'id'      => Plugin::ID . '_setting_order_status',
			],
			'setting_column_disabled'          => [
				'name'    => __( 'Kolumna informacyjna', 'korporacja-kurierska' ),
				'type'    => 'select',
				'options' => [
					0 => __( 'Włączona', 'korporacja-kurierska' ),
					1 => __( 'Wyłączona', 'korporacja-kurierska' )
				],
				'desc'    => __( 'Piktogramy przy zamówieniach WooCommerce.', 'korporacja-kurierska' ),
				'id'      => Plugin::ID . '_setting_column_disabled'
			],
			'setting_section_end'            => [
				'type' => 'sectionend',
				'id'   => Plugin::ID . '_setting_section_end'
			]
		];
	}

	/**
	 * Plugin additional setting available when API is active
	 *
	 * @return array
	 */
	public function get_settings_profile() {
		return [
			'profile_sender_section_title' => [
				'name' => __( 'Dane nadawcy', 'korporacja-kurierska' ),
				'type' => 'title',
				'desc' => __( 'Dane są wymagane i niezbędne do nadania paczki.', 'korporacja-kurierska' ),
				'id'   => Plugin::ID . '_profile_sender_section_title'
			],
			'profile_sender_first_name'    => [
				'name' => __( 'Imię', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_first_name',
				'api'  => 'senderName'
			],
			'profile_sender_last_name'     => [
				'name' => __( 'Nazwisko', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_last_name',
				'api'  => 'senderLastName'
			],
			'profile_sender_company'       => [
				'name' => __( 'Firma', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_company',
				'api'  => 'senderCompany'
			],
			'profile_sender_street'        => [
				'name' => __( 'Ulica', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_street',
				'api'  => 'senderStreet'
			],
			'profile_sender_house_number'  => [
				'name' => __( 'Numer domu', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_house_number',
				'api'  => 'senderHouseNumber'
			],
			'profile_sender_flat_number'   => [
				'name' => __( 'Numer mieszkania', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_flat_number',
				'api'  => 'senderFlatNumber'
			],
			'profile_sender_post_code'     => [
				'name' => __( 'Kod pocztowy', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_post_code',
				'api'  => 'senderPostCode'
			],
			'profile_sender_city'          => [
				'name' => __( 'Miasto', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_city',
				'api'  => 'senderCity'
			],
			'profile_sender_phone'         => [
				'name' => __( 'Telefon', 'korporacja-kurierska' ),
				'type' => 'text',
				'id'   => Plugin::ID . '_profile_sender_phone',
				'api'  => 'senderPhone'
			],
			'profile_sender_section_end'   => [
				'type' => 'sectionend',
				'id'   => Plugin::ID . '_profile_sender_section_end'
			]
		];
	}

	/**
	 * Plugin additional setting available when API is active
	 *
	 * @return array
	 */
	public function get_settings_couriers() {
		return [
			'couriers_section_title' => [
				'name' => __( 'Dostępni kurierzy', 'korporacja-kurierska' ),
				'type' => 'title',
				'id'   => Plugin::ID . '_settings_section_title',
				'desc' => 'Zawężenie listy kurierów.'
			],
			'couriers'               => [
				'name'    => __( 'Kurierzy', 'korporacja-kurierska' ),
				'type'    => 'multi_checkbox',
				'id'      => Plugin::ID . '_couriers',
				'options' => API::COURIERS,
			],
			'couriers_section_end'   => [
				'type' => 'sectionend',
				'id'   => Plugin::ID . '_couriers_section_end'
			],
		];
	}

	/**
	 * Plugin setting support page
	 *
	 * @return array
	 */
	public function get_settings_support() {
		return [
			'support_section_title' => [
				'name' => __( 'Wsparcie', 'korporacja-kurierska' ),
				'type' => 'title',
				'id'   => Plugin::ID . '_settings_section_title',
				'desc' => Plugin::load_template( 'support', 'admin/setting', [])
			],
			'support_section_end'   => [
				'type' => 'sectionend',
				'id'   => Plugin::ID . '_support_section_end'
			],
		];
	}
}
