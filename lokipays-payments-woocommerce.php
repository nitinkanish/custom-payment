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

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

add_action('plugins_loaded', 'lokipays_payment_init', 11);
add_filter('woocommerce_currencies', 'lokipays_add_ugx_currencies');
add_filter('woocommerce_currency_symbol', 'lokipays_add_ugx_currencies_symbol', 10, 2);
add_filter('woocommerce_payment_gateways', 'add_to_woo_lokipays_payment_gateway');
add_action('woocommerce_admin_order_data_after_billing_address', 'add_custom_button_after_billing_address');
add_action('rest_api_init', 'register_lokipays_webhook_endpoint');
add_action('rest_api_init', 'register_lokipays_transaction_status');
add_action('admin_enqueue_scripts', 'lokipays_enqueue_custom_script');

function register_lokipays_webhook_endpoint()
{
	register_rest_route('lokipays/v1', '/webhook', array(
		'methods' => 'POST',
		'callback' => 'handle_webhook',
		'permission_callback' => '__return_true',
	));
}

function register_lokipays_transaction_status()
{
	register_rest_route('lokipays/v1', '/transaction/status', array(
		'methods' => 'GET',
		'callback' => 'lokipays_transaction_status',
		'permission_callback' => '__return_true',
	));
}

function lokipays_enqueue_custom_script()
{
	global $post_type;

	// Check if we are on the order details page in the admin
	if ($post_type === 'shop_order' && is_admin()) {
		wp_enqueue_script('lokipays-admin-custom-plugin-script', plugin_dir_url(__FILE__) . 'assets/app.js', array('jquery'), '1.0', true);
	}
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

function lokipays_transaction_status()
{
	if (!isset($_GET['id'])) {
		return new WP_REST_Response("Invalid Transaction ID", 400);
	}

	$lokipays_pg = new WC_Gateway_Lokipays();

	$response = $lokipays_pg->getTransactionStatus($_GET['id']);

	return new WP_REST_Response($response, 200);
}

function handle_webhook($request)
{
	try {
		$payload = $request->get_json_params();
		lokipays_log('Webhook received: ' . json_encode($payload));


		if (!isset($payload['statusType'])) {
			lokipays_log("Invalid Response:");
		}

		$order_id = $payload['transaction']['reference'] ?? 0;

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

		if (isset($payload['transaction']['id'])) {
			$lokipays_transaction_id = str_replace("tx:", "", $payload['transaction']['id']);
			update_post_meta($order_id, "_transaction_id", $lokipays_transaction_id);
		}

		switch ($payload['statusType']) {
			case 0: // None - used for testing
				return new WP_REST_Response('None - used for testing', 200);
				break;

			case 1: // 1 = New - A new pending transaction has been created
				lokipays_log("A new pending transaction has been created");
				break;

			case 2: // 2 = Approved- The transaction was approved and processed
				$order->update_status('processing', 'Payment Approved- The transaction was approved and processed');
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
	} catch (Exception $e) {
		lokipays_log("Webhook Exception: " . $e->getMessage());
		return new WP_REST_Response(["success" => false, "message" => $e->getMessage()], 500);
	} catch (Error $e) {
		lokipays_log("Webhook Fatal Error: " . $e->getMessage());
		return new WP_REST_Response(["success" => false, "message" => $e->getMessage()], 500);
	}
}

function add_custom_button_after_billing_address($order)
{
	ob_start();
?>
	// Output the button after the billing address
	<div>
		<p class="form-field form-field-wide">
			<label for="lokipays-transaction-status-check">Lokipays Transaction Status</label>
			<button type="button" data-id="<?= $order->get_ID() ?>" id="lokipays-transaction-status-check">Check</button>
		</p>
		<p id="lokipays-transaction-status"></p>
	</div>
<?php
	echo ob_get_clean();
}

function lokipays_payment_init()
{
	if (class_exists('WC_Payment_Gateway')) {
		require_once plugin_dir_path(__FILE__) . '/includes/class-wc-payment-gateway-lokipays.php';
		require_once plugin_dir_path(__FILE__) . '/includes/lokipays-order-statuses.php';
		require_once plugin_dir_path(__FILE__) . '/includes/lokipays-checkout-description-fields.php';
	}
}

function add_to_woo_lokipays_payment_gateway($gateways)
{
	$gateways[] = 'WC_Gateway_Lokipays';
	return $gateways;
}

function lokipays_add_ugx_currencies($currencies)
{
	$currencies['UGX'] = __('Ugandan Shillings', 'lokipays-payments-woo');
	return $currencies;
}

function lokipays_add_ugx_currencies_symbol($currency_symbol, $currency)
{
	switch ($currency) {
		case 'UGX':
			$currency_symbol = 'UGX';
			break;
	}
	return $currency_symbol;
}

function lokipays_dd($data, $die = true)
{
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	if ($die) {
		exit();
	}
}

function lokipays_log($message)
{
	$date = date('Y-m-d');
	$log_file = plugin_dir_path(__FILE__) . 'logs/' . $date . '_LOGS.txt';
	$log_entry = date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL;
	file_put_contents($log_file, $log_entry, FILE_APPEND);
}

function plugin_url()
{
	return untrailingslashit(plugins_url('/', __FILE__));
}

function plugin_abspath()
{
	return trailingslashit(plugin_dir_path(__FILE__));
}

// Support for block:
// Registers WooCommerce Blocks integration.
add_action('woocommerce_blocks_loaded', 'lokipays_woocommerce_block_support');
function lokipays_woocommerce_block_support()
{
	if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
		require_once 'includes/blocks/class-wc-payments-blocks-lokipays.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
				$payment_method_registry->register(new WC_Gateway_Blocks_Support_LokiPays());
			}
		);
	}
}
