<?php

namespace Korporacja;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bridge {
	const PLUGINS_LOADED = 'plugins_loaded';
	const ADMIN_ENQUEUE_SCRIPTS = 'admin_enqueue_scripts';
	const WC_ORDER_COLUMNS = 'manage_edit-shop_order_columns';
	const WC_ORDER_COLUMN_CONTENT = 'manage_shop_order_posts_custom_column';
	const WC_UPDATE_OPTIONS_SHIPPING = 'woocommerce_update_options_shipping_';
	const WC_SHIPPING_METHODS = 'woocommerce_shipping_methods';
	const PLUGIN_ACTION_LINKS = 'plugin_action_links_';
	const WC_SETTINGS_TABS_ARRAY = 'woocommerce_settings_tabs_array';
	const WC_SETTINGS_TABS_SETTINGS = 'woocommerce_settings_tabs_settings_';
	const WC_UPDATE_OPTIONS_SETTINGS = 'woocommerce_update_options_settings_';
	const WC_ADMIN_FIELD = 'woocommerce_admin_field_';
	const WC_CHECKOUT_FIELDS = 'woocommerce_checkout_fields';
	const ADD_META_BOXES = 'add_meta_boxes';
	const WC_PROCESS_SHOP_ORDER_META = 'woocommerce_process_shop_order_meta';
	const WP_AJAX = 'wp_ajax_';
	const WC_CHECKOUT_PROCESS = 'woocommerce_checkout_process';
	const WC_REVIEW_ORDER_AFTER_SHIPPING = 'woocommerce_review_order_after_shipping';
	const WC_CHECKOUT_UPDATE_ORDER_META = 'woocommerce_checkout_update_order_meta';
	const WC_ORDER_DETAILS_AFTER_ORDER_TABLE = 'woocommerce_order_details_after_order_table';
	const WC_EMAIL_ORDER_META = 'woocommerce_email_order_meta';
	const WC_AFTER_SHIPPING_RATE = 'woocommerce_after_shipping_rate';
	const WC_CART_TOTALS_ORDER_TOTAL_HTML = 'woocommerce_cart_totals_order_total_html';
	const ADMIN_MENU = 'admin_menu';
	const ADMIN_NOTICES = 'admin_notices';
	const POSTBOX_CLASSES_SHOP_ORDER = 'postbox_classes_shop_order_';
	const SAVE_POST = 'save_post';
}
