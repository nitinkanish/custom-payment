<?php

/**
 * Lokipays Payment.
 *
 * Provides a Lokipays Payment Gateway.
 *
 * @class       WC_Gateway_Lokipays
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 */
class WC_Gateway_Lokipays extends WC_Payment_Gateway
{

	public $accountId;
	public $callback_url;
	public $mid;
	public $instructions;
	public $test_mode;
	public $api_key;
	public $api_endpoint;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct()
	{
		// Setup general properties.
		$this->setup_properties();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title              = $this->get_option('title');
		$this->description        = $this->get_option('description');
		$this->accountId          = $this->get_option('accountId');
		$this->callback_url       = $this->get_option('callback_url');
		$this->mid       		  = $this->get_option('mid');
		$this->instructions       = $this->get_option('instructions');
		$this->test_mode = $this->get_option('test_mode') == "yes";
		$this->api_endpoint = $this->test_mode ? "https://stage.lokipays.com/api" : "https://lokipays.com/api";
		$this->api_key = $this->get_option('api_key');

		$this->has_fields         = false;
		$this->supports           = array(
			'products',
			// 'subscriptions',
			// 'subscription_cancellation',
			// 'subscription_suspension',
			// 'subscription_reactivation',
			// 'subscription_amount_changes',
			// 'subscription_date_changes',
			// 'multiple_subscriptions'
		);


		// $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
		// $this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
		add_filter('woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3);

		// Customer Emails.
		add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties()
	{
		$this->id                 = 'lokipays';
		$this->icon               = apply_filters('woocommerce_lokipays_icon', plugins_url('../assets/icon.png', __FILE__));
		$this->method_title       = __('Lokipays Payment', 'lokipays-payments-woo');
		$this->accountId            = __('Add Account Id', 'lokipays-payments-woo');
		$this->callback_url          = __('Add Callback Url', 'lokipays-payments-woo');
		$this->mid          = __('Add MID', 'lokipays-payments-woo');
		$this->method_description = __('Have your customers pay with Lokipays Payment Solution.', 'lokipays-payments-woo');
		$this->has_fields         = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled'            => array(
				'title'       => __('Enable/Disable', 'lokipays-payments-woo'),
				'label'       => __('Enable Lokipays Payments', 'lokipays-payments-woo'),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'test_mode'            => array(
				'title'       => __('Test Mode', 'lokipays-payments-woo'),
				'label'       => __('Enable Test Mode', 'lokipays-payments-woo'),
				'type'        => 'checkbox',
				'description' => 'Test Card Details:<br>Name on card: test user<br> Credit Card: 4000003268263775<br> Expiry: Any future date<br>CVV: 123<br>',
				'default'     => 'no',
			),
			'api_key'            => array(
				'title'       => __('API Key', 'lokipays-payments-woo'),
				'label'       => __('API Key', 'lokipays-payments-woo'),
				'type'        => 'text',
				'description' => '',
				'default'     => '',
			),
			'title'              => array(
				'title'       => __('Title', 'lokipays-payments-woo'),
				'type'        => 'text',
				'description' => __('Lokipays Payment method description that the customer will see on your checkout.', 'lokipays-payments-woo'),
				'default'     => __('Lokipays Payments', 'lokipays-payments-woo'),
				'desc_tip'    => true,
			),
			'accountId'             => array(
				'title'       => __('Account Id', 'lokipays-payments-woo'),
				'type'        => 'text',
				'description' => __('Add your Account Id', 'lokipays-payments-woo'),
				'desc_tip'    => true,
			),
			'callback_url'           => array(
				'title'       => __('Callback Url', 'lokipays-payments-woo'),
				'type'        => 'text',
				'description' => __('Add your Callback Url', 'lokipays-payments-woo'),
				'desc_tip'    => true,
			),
			'mid'           => array(
				'title'       => __('MID', 'lokipays-payments-woo'),
				'type'        => 'text',
				'description' => __('Add your mid', 'lokipays-payments-woo'),
				'desc_tip'    => true,
			),
			'description'        => array(
				'title'       => __('Description', 'lokipays-payments-woo'),
				'type'        => 'textarea',
				'description' => __('Lokipays Payment method description that the customer will see on your website.', 'lokipays-payments-woo'),
				'default'     => __('Lokipays Payments before delivery.', 'lokipays-payments-woo'),
				'desc_tip'    => true,
			),
			'instructions'       => array(
				'title'       => __('Instructions', 'lokipays-payments-woo'),
				'type'        => 'textarea',
				'description' => __('Instructions that will be added to the thank you page.', 'lokipays-payments-woo'),
				'default'     => __('Lokipays Payments before delivery.', 'lokipays-payments-woo'),
				'desc_tip'    => true,
			),
			// 'enable_for_methods' => array(
			// 	'title'             => __( 'Enable for shipping methods', 'lokipays-payments-woo' ),
			// 	'type'              => 'multiselect',
			// 	'class'             => 'wc-enhanced-select',
			// 	'css'               => 'width: 400px;',
			// 	'default'           => '',
			// 	'description'       => __( 'If lokipays is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'lokipays-payments-woo' ),
			// 	'options'           => $this->load_shipping_method_options(),
			// 	'desc_tip'          => true,
			// 	'custom_attributes' => array(
			// 		'data-placeholder' => __( 'Select shipping methods', 'lokipays-payments-woo' ),
			// 	),
			// ),
			// 'enable_for_virtual' => array(
			// 	'title'   => __( 'Accept for virtual orders', 'lokipays-payments-woo' ),
			// 	'label'   => __( 'Accept lokipays if the order is virtual', 'lokipays-payments-woo' ),
			// 	'type'    => 'checkbox',
			// 	'default' => 'yes',
			// ),
		);
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available()
	{
		$order          = null;
		$needs_shipping = false;

		// Test if shipping is needed first.
		if (WC()->cart && WC()->cart->needs_shipping()) {
			$needs_shipping = true;
		} elseif (is_page(wc_get_page_id('checkout')) && 0 < get_query_var('order-pay')) {
			$order_id = absint(get_query_var('order-pay'));
			$order    = wc_get_order($order_id);

			// Test if order needs shipping.
			if (0 < count($order->get_items())) {
				foreach ($order->get_items() as $item) {
					$_product = $item->get_product();
					if ($_product && $_product->needs_shipping()) {
						$needs_shipping = true;
						break;
					}
				}
			}
		}

		$needs_shipping = apply_filters('woocommerce_cart_needs_shipping', $needs_shipping);

		// Virtual order, with virtual disabled.
		// if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
		// 	return false;
		// }

		// Only apply if all packages are being shipped via chosen method, or order is virtual.
		// if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
		// 	$order_shipping_items            = is_object( $order ) ? $order->get_shipping_methods() : false;
		// 	$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

		// 	if ( $order_shipping_items ) {
		// 		$canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids( $order_shipping_items );
		// 	} else {
		// 		$canonical_rate_ids = $this->get_canonical_package_rate_ids( $chosen_shipping_methods_session );
		// 	}

		// 	if ( ! count( $this->get_matching_rates( $canonical_rate_ids ) ) ) {
		// 		return false;
		// 	}
		// }

		return parent::is_available();
	}

	/**
	 * Checks to see whether or not the admin settings are being accessed by the current request.
	 *
	 * @return bool
	 */
	private function is_accessing_settings()
	{
		if (is_admin()) {
			// phpcs:disable WordPress.Security.NonceVerification
			if (!isset($_REQUEST['page']) || 'wc-settings' !== $_REQUEST['page']) {
				return false;
			}
			if (!isset($_REQUEST['tab']) || 'checkout' !== $_REQUEST['tab']) {
				return false;
			}
			if (!isset($_REQUEST['section']) || 'lokipays' !== $_REQUEST['section']) {
				return false;
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return true;
		}

		return false;
	}

	/**
	 * Loads all of the shipping method options for the enable_for_methods field.
	 *
	 * @return array
	 */
	private function load_shipping_method_options()
	{
		// Since this is expensive, we only want to do it if we're actually on the settings page.
		if (!$this->is_accessing_settings()) {
			return array();
		}

		$data_store = WC_Data_Store::load('shipping-zone');
		$raw_zones  = $data_store->get_zones();

		foreach ($raw_zones as $raw_zone) {
			$zones[] = new WC_Shipping_Zone($raw_zone);
		}

		$zones[] = new WC_Shipping_Zone(0);

		$options = array();
		foreach (WC()->shipping()->load_shipping_methods() as $method) {

			$options[$method->get_method_title()] = array();

			// Translators: %1$s shipping method name.
			$options[$method->get_method_title()][$method->id] = sprintf(__('Any &quot;%1$s&quot; method', 'lokipays-payments-woo'), $method->get_method_title());

			foreach ($zones as $zone) {

				$shipping_method_instances = $zone->get_shipping_methods();

				foreach ($shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance) {

					if ($shipping_method_instance->id !== $method->id) {
						continue;
					}

					$option_id = $shipping_method_instance->get_rate_id();

					// Translators: %1$s shipping method title, %2$s shipping method id.
					$option_instance_title = sprintf(__('%1$s (#%2$s)', 'lokipays-payments-woo'), $shipping_method_instance->get_title(), $shipping_method_instance_id);

					// Translators: %1$s zone name, %2$s shipping method instance name.
					$option_title = sprintf(__('%1$s &ndash; %2$s', 'lokipays-payments-woo'), $zone->get_id() ? $zone->get_zone_name() : __('Other locations', 'lokipays-payments-woo'), $option_instance_title);

					$options[$method->get_method_title()][$option_id] = $option_title;
				}
			}
		}

		return $options;
	}

	/**
	 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
	 *
	 * @since  3.4.0
	 *
	 * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
	 * @return array $canonical_rate_ids    Rate IDs in a canonical format.
	 */
	private function get_canonical_order_shipping_item_rate_ids($order_shipping_items)
	{

		$canonical_rate_ids = array();

		foreach ($order_shipping_items as $order_shipping_item) {
			$canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
		}

		return $canonical_rate_ids;
	}

	/**
	 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
	 *
	 * @since  3.4.0
	 *
	 * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
	 * @return array $canonical_rate_ids  Rate IDs in a canonical format.
	 */
	private function get_canonical_package_rate_ids($chosen_package_rate_ids)
	{

		$shipping_packages  = WC()->shipping()->get_packages();
		$canonical_rate_ids = array();

		if (!empty($chosen_package_rate_ids) && is_array($chosen_package_rate_ids)) {
			foreach ($chosen_package_rate_ids as $package_key => $chosen_package_rate_id) {
				if (!empty($shipping_packages[$package_key]['rates'][$chosen_package_rate_id])) {
					$chosen_rate          = $shipping_packages[$package_key]['rates'][$chosen_package_rate_id];
					$canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
				}
			}
		}

		return $canonical_rate_ids;
	}

	/**
	 * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
	 *
	 * @since  3.4.0
	 *
	 * @param array $rate_ids Rate ids to check.
	 * @return boolean
	 */
	private function get_matching_rates($rate_ids)
	{
		// First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
		return array_unique(array_merge(array_intersect($this->enable_for_methods, $rate_ids), array_intersect($this->enable_for_methods, array_unique(array_map('wc_get_string_before_colon', $rate_ids)))));
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment($order_id)
	{
		$order = wc_get_order($order_id);

		if ($order->get_total() > 0) {
			$this->lokipays_payment_processing($order);
		}
	}

	public function getTransactionStatus($reference)
	{
		$url = $this->api_endpoint . '/Transaction/StatusByReference/' . $this->accountId . '/?reference=' . $reference;
		
		$headers = array(
			'accept' => 'text/plain; x-api-version=1.0',
			'x-api-key' => $this->api_key,
			'Content-Type' => 'application/json; x-api-version=1.0',
		);

		$args = array(
			'headers'     => $headers,
			'timeout'     => 3601000,
			'method'      => 'GET',
		);

		$response = wp_remote_get($url, $args);

		// Check for errors
		if (is_wp_error($response)) {

			return [
				'error' => $response->get_error_message()
			];

		}

		$response_code = wp_remote_retrieve_response_code($response);

		if ($response_code != 200) {
			return [
				'error' => 'Invalid Ressponse: Status Code: ' . $response_code
			];

		}


		$res_body = json_decode(wp_remote_retrieve_body($response), true);

		return $res_body;
	}

	private function buildErrorMessage($errorString)
	{
		// consumer.first name
		// consumer.last name
		// validation.credit_card.card_length_invalid
		// validation.credit_card.card_checksum_invalid
		// Credit card expiration month is invalid
		// Credit card expiration year is invalid
		// Credit card secure code is invalid
		$errors = [];

		if (str_contains($errorString, "consumer.first name")) {
			$errors['first_name'] = "First name is invalid";
		}

		if (str_contains($errorString, "consumer.last name")) {
			$errors['last_name'] = "Last name is invalid";
		}

		// with wrong length
		if (str_contains($errorString, "validation.credit_card.card_length_invalid")) {
			$errors['card'] = "Incorrect Card Details";
		}
		if (str_contains($errorString, "validation.credit_card.card_length_invalid,,Credit card expiration month is invalid")) {
			$errors['card'] = "Incorrect Card Expiration, please retry";
		}
		if (str_contains($errorString, "validation.credit_card.card_length_invalid,,Credit card expiration year is invalid")) {
			$errors['card'] = "Incorrect Expiration Year, please retry";
		}

		// with wrong credit card number
		if (str_contains($errorString, "validation.credit_card.card_checksum_invalid")) {
			$errors['card'] = "Incorrect Card Details, please retry";
		}
		if (str_contains($errorString, "validation.credit_card.card_checksum_invalid,Credit card expiration month is invalid")) {
			$errors['card'] = "Incorrect Card Expiration, please retry";
		}
		if (str_contains($errorString, "validation.credit_card.card_checksum_invalid,Credit card expiration year is invalid")) {
			$errors['card'] = "Incorrect Expiration Year, please retry";
		}
		if (str_contains($errorString, "Credit card expiration year is invalid")) {
			$errors['card'] = "Incorrect Expiration Year, please retry";
		}
		if (str_contains($errorString, "Credit card expiration month is invalid")) {
			$errors['card'] = "Incorrect Expiration Month, please retry";
		}

		if (count($errors) == 0) {
			$errors['general'] = $errorString;
		}

		return $errors;
	}

	private function lokipays_payment_processing($order)
	{
		$order_id = $order->get_ID();

		lokipays_log("\n\nProcessing Order: " . $order_id);

		$total = intval($order->get_total());

		$number_on_card = esc_attr($_POST['number_on_card']);
		$name_on_card = esc_attr($_POST['name_on_card']);
		$year = $_POST['expiry_year_on_card'];

		// Convert year to 2 digits, if user provided year in 4 digits.
		if (strlen($year) == 4) {
			$year = substr($year, -2);
		}

		$url = $this->api_endpoint . '/Transaction/CardDeposit';

		$headers = array(
			'accept' => 'text/plain; x-api-version=1.0',
			'x-api-key' => $this->api_key,
			'Content-Type' => 'application/json; x-api-version=1.0',
		);
		

		$body = json_encode([
			"description" => "Payment for Order: " . $order_id,
			"reference" => (string)$order_id,
			"amount" => $total,
			"accountId" => $this->accountId,
			"customer" => $order->get_billing_email(),
			"creditCard" => array(
				"name" => $name_on_card,
				"number" => $number_on_card,
				"expiryMonth" => $_POST['expiry_month_on_card'],
				"expiryYear" => $year,
				"cvv" => $_POST['cvv_on_card'],
			),
			"callbackUrl" => $this->callback_url,
			"mid" => $this->mid,
		]);

		lokipays_log("Request payload: " . $body);
		
		$args = array(
			'headers'     => $headers,
			'timeout'     => 3601000,
			'body'        => $body,
			'method'      => 'POST',
			'data_format' => 'body',
		);

		$response = wp_remote_post($url, $args);

		// Check for errors
		if (is_wp_error($response)) {

			lokipays_log("Failed: Response Error: " . $response->get_error_message());

			if (str_contains($response->get_error_message(), "cURL error 28: Operation timed out")) {
				wc_add_notice("There is some unusual delay in response, please retry after sometime.", 'error');
			} else {
				wc_add_notice($response->get_error_message(), 'error');
			}

			// Handle error
			return [
				"result" => "failure",
				"message" => $response->get_error_message(),
				"refresh" => false,
				"reload" => false,
			];
		}

		$response_code = wp_remote_retrieve_response_code($response);

		if ($response_code != 200) {

			$res_body = json_decode(wp_remote_retrieve_body($response));

			lokipays_log("Failed: Response Code: " . $response_code . wp_remote_retrieve_body($response));

			wc_add_notice($res_body->title ?? "Something went wrong, Please try again.", 'error');

			return [
				"result" => "failure",
				"message" => $res_body->title ?? "Something went wrong, Please try again.",
				"refresh" => false,
				"reload" => false,
			];
		}


		$res_body = json_decode(wp_remote_retrieve_body($response));

		lokipays_log("Response received: " . json_encode($res_body));

		if ($res_body->status == "Rejected") {
			lokipays_log("Failed: " . $res_body->value ?? "Something went wrong, Please try again.");

			$error_messages = $this->buildErrorMessage($res_body->value);
			$error_messages = implode("<br>", $error_messages);

			wc_add_notice($error_messages ?? "Something went wrong, Please try again.", 'error');

			return [
				"result" => "failure",
				"message" => $res_body->description ?? "Something went wrong, Please try again.",
				"refresh" => false,
				"reload" => false,
			];
		}

		if ($res_body->status == "Active") {
			$order->update_status('processing', 'Payment Approved - The transaction was approved and processed');
			$lokipays_transaction_id = str_replace("tx:", "", $res_body->key);
			update_post_meta($order->get_ID(), "_transaction_id", $lokipays_transaction_id);
			$order->payment_complete();
		} else {
			// Payment pending - further status will updated by webhook.
			$order->update_status('pending-payment', 'Payment pending');
		}

		// Remove cart.
		WC()->cart->empty_cart();

		lokipays_log("Waiting for webhook call...");

		// Return thankyou redirect.
		wp_send_json(array(
			'result'   => 'success',
			'redirect' => $this->get_return_url($order),
		));

		// $order->update_status(
		// 	apply_filters(
		// 		'woocommerce_lokipays_process_payment_order_status', 
		// 		$order->has_downloadable_item() ? 'wc-invoiced' : 'processing', $order
		// 	), 

		// 	__('Payments pending.', 'lokipays-payments-woo')
		// );

		// $url = 'https://lokipays.com/api/Transaction/CardDeposit/' . $this->accountId . '/' . $this->widget_id . '?number=' . $number_on_card . '&name=' . $name_on_card . '&amount=' . $total . '&mobile_money_company_id=' . $network_id . '&reason=' . 'Payment for Order: ' .$order_id;
		// var_dump($url);
		// var_dump("TESTING orderId:" . $order_id . " amount: " . $total .  " Name:" . $name_on_card . " UserId: " . $order->get_user_id());
		// // $response = wp_remote_post( $url, array( 'timeout' => 45 ) );

		// var_dump($body);

		// $url = "https://production-api-awoogzvxua-nw.a.run.app/api/create-support-request";
		// $body = array(
		// 	'data' => array( 'title' => $number_on_card),
		// );
		// $response = wp_remote_post($url, array(
		// 	'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
		// 	'body'        => json_encode($body),
		// 	'method'      => 'POST',
		// 	'data_format' => 'body',
		// ));
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page()
	{
		if ($this->instructions) {
			echo wp_kses_post(wpautop(wptexturize($this->instructions)));
		}
	}

	/**
	 * Change payment complete order status to completed for lokipays orders.
	 *
	 * @since  3.1.0
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 * @return string
	 */
	public function change_payment_complete_order_status($status, $order_id = 0, $order = false)
	{
		if ($order && 'lokipays' === $order->get_payment_method()) {
			$status = 'completed';
		}
		return $status;
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin  Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions($order, $sent_to_admin, $plain_text = false)
	{
		if ($this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method()) {
			echo wp_kses_post(wpautop(wptexturize($this->instructions)) . PHP_EOL);
		}
	}
}
