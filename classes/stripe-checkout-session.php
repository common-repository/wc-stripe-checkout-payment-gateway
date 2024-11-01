<?php
/**
 * Description:
 *		1. Create a new checkout session in Stripe to collect payments for orders made by users in WooCommerce
 *		2. Checks if a customer record has already been created in Stripe. Otherwise, a new customer record will be created
 *		3. Redirect the user to the Stripe Checkout page after a checkout session has been created
 *
 * References:
 *		1. https://stripe.com/docs/api/checkout/sessions
 *		2. https://stripe.com/docs/api/customers
 *		3. https://woocommerce.github.io/code-reference/classes/WC-Order.html
 *
 * Last Updated: 1-SEP-21
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/../classes/stripe-checkout-utility.php';
require_once __DIR__ . '/../includes/stripe-php-master/init.php';

use Stripe_Checkout_Utility as Utility;

class Stripe_Checkout_Session
{
	private $gateway;
	private $order;
	private $stripe;

	/**
	 * Constructor for a new Stripe Checkout session
	 */
	public function __construct( $stripe_checkout_gateway, $woocommerce_order ) {
		$this->gateway	= $stripe_checkout_gateway;
		$this->order	= $woocommerce_order;

		// Setup a Stripe client to authenticate requests
		$api_keys		= $this->get_api_keys();
		$this->stripe	= new \Stripe\StripeClient( $api_keys['secret_key'] );
	}

	/**
	 * The main function of this class that creates a new checkout session and redirects the user to a Stripe Checkout page
	 */
	public function redirect() {
		// Create a new checkout session for a new order using the parameters below
		$parameters							= array();
		$parameters['cancel_url']			= $this->order->get_checkout_payment_url();
		$parameters['mode']					= 'payment';
		$parameters['payment_method_types']	= $this->get_payment_method_types();
		$parameters['success_url']			= $this->order->get_checkout_order_received_url();
		$parameters['customer']				= $this->update_customer();		// This should return a valid customer ID
		$parameters['line_items']			= $this->get_line_items();
		$parameters['payment_intent_data']	= $this->get_payment_intent_data();

		// The idempotency key prevents multiple checkout sessions from being created for the same order. See https://stripe.com/docs/api/idempotent_requests
		$idempotency_key = md5( serialize( $parameters ) );
		$idempotency_key = array( 'idempotency_key' => $idempotency_key );

		Logger::info( 'Creating a Stripe checkout session for Order #' . $this->order->get_order_number() );
		Logger::debug( array_merge( $parameters, $idempotency_key ) );

		$checkout_session = $this->stripe->checkout->sessions->create( $parameters, $idempotency_key );

		// Check if the newly created checkout session ID is valid (e.g. cs_test_83iIJRqgRAedLVmUyA3tlsLacYyMq77d4ZJbnqdDnCpazivb9wl4iwPv)
		if ( Utility::string_starts_with( $checkout_session['id'], 'cs_' ) ) {
			Logger::info( 'Stripe checkout session successfully created for Order #' . $this->order->get_order_number() );
			Logger::debug( $checkout_session );

			if ( $checkout_session['url'] ) {
				return $checkout_session['url'];	// Get the URL to the checkout session
			}
		}

		throw new Exception( 'An error has occured' );
	}

	/**
	 * Add or update customer details from WooCommerce to Stripe
	 */
	private function update_customer() {
		// Get the user associated with the order
		$wp_user	= $this->order->get_user()->to_array();
		$wp_user_id	= $wp_user['ID'];

		Logger::info( 'Starting Stripe checkout session for ' . $wp_user['user_email'] );

		// Check if the Stripe customer ID is available from the wp_usermeta table
		$stripe_customer_id = get_user_meta( $wp_user_id, 'stripe_customer_id', true );

		if ( Utility::string_starts_with( $stripe_customer_id, 'cus_' ) ) {
			return $stripe_customer_id;
		}

		// If the customer ID is not available, create a new record using the parameters below
		$parameters										= array();
		$parameters['description']						= $wp_user['display_name'] . ' from ' . get_site_url();		// This is displayed at the Stripe dashboard
		$parameters['email']							= $wp_user['user_email'];
		$parameters['metadata']['wordpress_user_id']	= $wp_user_id;
		$parameters['metadata']['wordpress_user_link']	= get_author_posts_url( $wp_user_id );
		$parameters['name']								= $wp_user['display_name'];

		$customer = $this->stripe->customers->create( $parameters );

		// Check if the newly created customer ID is valid (e.g. cus_IzwRZAqY76hlfk)
		if ( Utility::string_starts_with( $customer['id'], 'cus_' ) ) {
			// Add the newly created customer ID to the wp_usermeta table
			update_user_meta( $wp_user_id, 'stripe_customer_id', $customer['id'] );

			return $customer['id'];
		}

		throw new Exception( 'An error has occured' );
	}

	/**
	 * Get API keys from the payment gateway settings
	 */
	private function get_api_keys() {
		$publishable_key	= $this->gateway->get_option( 'live_mode_publishable_key' );
		$secret_key			= $this->gateway->get_option( 'live_mode_secret_key' );

		$test_mode = $this->gateway->get_option( 'test_mode' );

		if ( $test_mode === 'yes' ) {
			$publishable_key	= $this->gateway->get_option( 'test_mode_publishable_key' );
			$secret_key			= $this->gateway->get_option( 'test_mode_secret_key' );
		}

		if ( empty( trim( $publishable_key ) ) or empty( trim( $secret_key ) ) ) {
			throw new Exception( 'Please configure the API keys' );
		}

		return array(
			'publishable_key'	=> $publishable_key,
			'secret_key'		=> $secret_key
		);
	}

	/**
	 * Get payment intent data for the Stripe Checkout session
	 */
	private function get_payment_intent_data() {
		$payment_intent_data										= array();
		$payment_intent_data['description']							= 'Order #' . $this->order->get_order_number() . ' from ' . get_site_url();		// This is displayed at the Stripe dashboard
		$payment_intent_data['metadata']['woocommerce_order_no']	= $this->order->get_order_number();
		$payment_intent_data['metadata']['woocommerce_order_link']	= $this->order->get_edit_order_url();

		return $payment_intent_data;
	}

	/**
	 * Get active payment method types from a Stripe account to be used for the Stripe Checkout session
	 * See https://stripe.com/docs/api/accounts/retrieve
	 */
	private function get_payment_method_types() {
		$stripe_payment_methods = include __DIR__ . '/../includes/stripe-payment-methods.php';
		$active_payment_methods = array();

		$account_capabilities = $this->stripe->accounts->retrieve();
		$account_capabilities = $account_capabilities->capabilities->toArray();

		Logger::info( 'Stripe account capabilities retrieved' );
		Logger::debug( $account_capabilities );

		foreach ( $account_capabilities as $active_payment_method => $status ) {

			if ( $status === 'active' ) {

				foreach ( $stripe_payment_methods as $stripe_payment_method ) {

					if ( Utility::string_contains( $active_payment_method, $stripe_payment_method ) ) {
						array_push( $active_payment_methods, $stripe_payment_method );
					}

				}

			}

		}

		return $active_payment_methods;
	}

	/**
	 * Get line items for the Stripe Checkout session
	 */
	private function get_line_items() {
		$price_data							= array();
		$price_data['currency']				= strtolower( $this->order->get_currency() );		// Currency must be in lowercase as as defined by Stripe
		$price_data['product_data']['name']	= 'Order #' . $this->order->get_order_number();		// This is displayed to the customer
		$price_data['unit_amount']			= $this->get_unit_amount();

		$line_items							= array();
		$line_items[0]['price_data']		= $price_data;
		$line_items[0]['quantity']			= 1;

		return $line_items;
	}

	/**
	 * Calculate the unit amount for the Stripe Checkout session. A unit amount in Stripe is a non-negative integer in cents representing how much to charge
	 */
	private function get_unit_amount() {
		$stripe_zero_decimal_currencies	= include __DIR__ . '/../includes/stripe-zero-decimal-currencies.php';

		$order_currency	= strtoupper( $this->order->get_currency() );
		$order_total	= $this->order->get_total();

		// Check if the current order's currency is a zero decimal currency as defined by Stripe. See https://stripe.com/docs/currencies
		foreach ( $stripe_zero_decimal_currencies as $stripe_zero_decimal_currency ) {

			if ( $order_currency === $stripe_zero_decimal_currency ) {
				return $order_total * 1;
			}

		}

		// If the current order's currency is not a zero decimal currency, convert the current order's grand total amount to cents
		return $order_total * 100;
	}
}