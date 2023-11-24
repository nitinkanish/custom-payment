<?php
/**
 * Plugin Name: Lokipays Payment Gateway
 * Plugin URI: https://lokipays.com
 * Author: Lokipays
 * Author URI: https://lokipays.com
 * Description: Lokipays Payment Gateway.
 * Version: 0.1.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: lokipays-payments-woo
 * 
 * Class WC_Gateway_Lokipays file.
 *
 * @package WooCommerce\PayLeo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'lokipays_payment_init', 11 );
add_filter( 'woocommerce_currencies', 'lokipays_add_ugx_currencies' );
add_filter( 'woocommerce_currency_symbol', 'lokipays_add_ugx_currencies_symbol', 10, 2 );
add_filter( 'woocommerce_payment_gateways', 'add_to_woo_lokipays_payment_gateway');

function lokipays_payment_init() {
    if( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-wc-payment-gateway-lokipays.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/lokipays-order-statuses.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/lokipays-checkout-description-fields.php';
	}
}

function add_to_woo_lokipays_payment_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_Lokipays';
    return $gateways;
}

function lokipays_add_ugx_currencies( $currencies ) {
	$currencies['UGX'] = __( 'Ugandan Shillings', 'lokipays-payments-woo' );
	return $currencies;
}

function lokipays_add_ugx_currencies_symbol( $currency_symbol, $currency ) {
	switch ( $currency ) {
		case 'UGX': 
			$currency_symbol = 'UGX'; 
		break;
	}
	return $currency_symbol;
}