<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Package_Template {

	const POST_TYPE_NAME = 'kk_package_template';

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	public function init() {
		add_filter( Bridge::ADMIN_MENU, [ $this, 'add_menu_pages' ], 50 );
		add_action( Bridge::ADD_META_BOXES, [ $this, 'remove_wp_seo_meta_box' ], 100 );
	}

	function remove_wp_seo_meta_box() {
		remove_meta_box( 'wpseo_meta', self::POST_TYPE_NAME, 'normal' );
	}

	/**
	 * Add Korporacja Kurierska setting to Admin menu
	 */
	public function add_menu_pages() {
		add_submenu_page(
			Plugin::ID,
			__( 'Szablony paczek', 'korporacja-kurierska' ),
			__( 'Szablony paczek', 'korporacja-kurierska' ),
			'manage_options',
			'edit.php?post_type=' . self::POST_TYPE_NAME
		);

		add_submenu_page(
			Plugin::ID,
			__( 'Dodaj szablon', 'korporacja-kurierska' ),
			__( 'Dodaj szablon', 'korporacja-kurierska' ),
			'manage_options',
			'/post-new.php?post_type=' . self::POST_TYPE_NAME
		);
	}

	/**
	 * Register post type
	 */
	public function register_post_type() {
		$labels = [
			'name'               => __( 'Szablony paczek', 'korporacja-kurierska' ),
			'singular_name'      => __( 'Szablon paczki', 'korporacja-kurierska' ),
			'menu_name'          => __( 'Szablony', 'korporacja-kurierska' ),
			'name_admin_bar'     => __( 'Szablon', 'add new on admin bar', 'korporacja-kurierska' ),
			'add_new'            => __( 'Dodaj nowy', 'korporacja-kurierska' ),
			'add_new_item'       => __( 'Dodaj nowy szablon', 'korporacja-kurierska' ),
			'new_item'           => __( 'Nowy szablon', 'korporacja-kurierska' ),
			'edit_item'          => __( 'Edytuj szablon', 'korporacja-kurierska' ),
			'view_item'          => __( 'Zobacz szablon', 'korporacja-kurierska' ),
			'all_items'          => __( 'Wszystkie szablony', 'korporacja-kurierska' ),
			'search_items'       => __( 'Szukaj szablonu', 'korporacja-kurierska' ),
			'parent_item_colon'  => __( 'Szablon nadrzędny:', 'korporacja-kurierska' ),
			'not_found'          => __( 'Nie znaleziono szablonu.', 'korporacja-kurierska' ),
			'not_found_in_trash' => __( 'Brak szablonów w koszu.', 'korporacja-kurierska' )
		];

		$args = [
			'labels'             => $labels,
			'public'             => is_admin(),
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => self::POST_TYPE_NAME ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => [ 'title' ]
		];

		register_post_type( self::POST_TYPE_NAME, $args );
	}

	/**
	 * Return all templates
	 * @return array
	 */
	public static function get_posts() {
		return get_posts( [
			'posts_per_page'   => - 1,
			'offset'           => 0,
			'orderby'          => 'title',
			'order'            => 'DESC',
			'post_type'        => self::POST_TYPE_NAME,
			'post_status'      => 'publish',
			'suppress_filters' => true
		] );
	}

	/**
	 * Prepare data for Template select
	 * @return array
	 */
	public static function get_select_data() {
		$posts   = [];
		$posts[] = __( 'Wybierz', 'korporacja-kurierska' );

		foreach ( self::get_posts() as $post ) {
			$packages = get_post_meta( $post->ID, '_' . Plugin::ID . '_packages', true );
			if ( ! empty( $packages ) ) {
				$posts[ json_encode( $packages ) ] = $post->post_title;
			}
		}

		return $posts;
	}
}
