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

define( 'LOKIPAY_ENDPOINT', 'https://stage.lokipays.com/api' );
define( 'LOKIPAY_APIKEY', '14-a0873dfd-9c06-4cc0-93ef-9c8b875ae6bb' );

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'lokipays_payment_init', 11 );
add_filter( 'woocommerce_currencies', 'lokipays_add_ugx_currencies' );
add_filter( 'woocommerce_currency_symbol', 'lokipays_add_ugx_currencies_symbol', 10, 2 );
add_filter( 'woocommerce_payment_gateways', 'add_to_woo_lokipays_payment_gateway');

add_action('rest_api_init', 'register_lokipays_webhook_endpoint');

function register_lokipays_webhook_endpoint() {
    register_rest_route('lokipays/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'handle_webhook',
				'permission_callback' => '__return_true',
    ));
}

/*
Approved Payload
{
"statusType":2,
"status":"Approved",
“description”:”Billing descriptor”,
"actionedById":1,
"affectedContactIds":[]
"transaction": {
"id":1341,
"createdById":2,
"type":"UCD",
"date":"2021-04-20T20:34:00.7606173+02:00",
"reference":234234234,
"currencyCode":"USD",
"amount":100.00
}
}

Rejected Payload:
{
"statusType":3,
"status":"Rejected",
“description”:”Reason for rejection”
"actionedById":1,
"affectedContactIds":[]
"transaction": {
"id":0,
"createdById":2,
"type":"UCD",
"date":"2021-04-20T20:34:00.7606173+02:00",
"reference":234234234,
"currencyCode":"USD",
"amount":100.00
}
}

*/

function handle_webhook($request) {
	$payload = $request->get_json_params();
	try {
    lokipays_log('Webhook received: ' . json_encode($payload));

		
		if (!isset($payload['statusType'])) {
			lokipays_log("Invalid Response:");
		}

		$order_id = $payload['transaction']['id'] ?? 0;
		
		if ($order_id == 0) {
			lokipays_log("Transaction ID Missing:");
			return new WP_REST_Response("Transaction ID Missing:", 200);
		}

		lokipays_log("Processing Webhook Order: " . $order_id);
		lokipays_log("Status: " . $payload['status']);

		try {
			$order = new WC_Order($order_id);
		} catch (Exception $e) {
			lokipays_log("Error: " . $e->getMessage());
			return new WP_REST_Response($e->getMessage(), 200);
		} catch (Error $e) {
			lokipays_log("Error: " . $e->getMessage());
			return new WP_REST_Response($e->getMessage(), 200);
		}

	
		switch ($payload['statusType']) {
			case 0: // None - used for testing
				return new WP_REST_Response('None - used for testing', 200);
				break;

			case 1: // 1 = New - A new pending transaction has been created
				lokipays_log("A new pending transaction has been created");
				break;

			case 2: // 2 = Approved- The transaction was approved and processed
				$order->update_status('completed', 'Payment Approved- The transaction was approved and processed');
				$order->payment_complete();
				return new WP_REST_Response('Successfully', 200);
				break;

			case 3: // 3 = Rejected - The transaction was rejected by acquirer
				$order->update_status('failed', 'Payment Rejected - ' . $payload['reason'] ?? "The transaction was rejected by acquirer");
				return new WP_REST_Response('Payment Rejected', 200);
				break;

			case 4: // 4 = Cancelled - The transaction was cancelled by the creator
				$order->update_status('cancelled', 'Payment Cancelled - ' . $payload['reason'] ?? "The transaction was cancelled by the creator");
				return new WP_REST_Response('Payment Cancelled', 200);
				break;
		}
    
    return new WP_REST_Response('No action performed.', 200);
	} catch (Error $e) {
		lokipays_log($e->getMessage());
	}
}


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

function lokipays_dd($data, $die = true) {
	echo '<pre>'; print_r($data); echo '</pre>';
	if ($die) {
		exit();
	}
}

function lokipays_log($message) {
	$date = date('Y-m-d');
	$log_file = plugin_dir_path(__FILE__) . 'logs/' . $date . '_LOGS.txt';
	$log_entry = date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL;
	file_put_contents($log_file, $log_entry, FILE_APPEND);
}
