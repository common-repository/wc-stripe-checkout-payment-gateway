<?php
/**
 * Description:
 *		1. Create a new payment gateway object for Stripe Checkout. This will be loaded as an additional plugin that hooks into WooCommerce
 *		2. Set options that will show at the Stripe Checkout payment gateway settings page in the backend
 *		3. Create a new Stripe Checkout session to handle payment and processing for orders made by users in WooCommerce
 *		4. Create a new Stripe callback object to handle incoming events sent by Stripe when the WooCommerce API is triggered for this payment gateway
 *
 * References:
 *		1. https://docs.woocommerce.com/document/payment-gateway-api
 *		2. https://docs.woocommerce.com/document/wc_api-the-woocommerce-api-callback
 *
 * Last Updated: 8-SEP-21
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/../classes/woocommerce-logger.php';

class WC_Stripe_Checkout extends WC_Payment_Gateway
{
	/**
	 * Constructor for a new payment gateway object
	 */
	public function __construct() {
		$this->id					= 'wc-stripe-checkout';		// Unique ID for this gateway
		$this->has_fields			= false;	// This is a form based payment gateway so no payment fields are shown on the checkout page

		// This is shown at the installed payment methods page of WooCommerce in the backend
		$this->method_title			= __( 'Stripe Checkout', 'woocommerce' );
		$this->method_description	= __( 'Stripe Checkout redirects customers to a prebuilt payment page hosted on Stripe to make payments.', 'woocommerce' );

		// Load the settings of this payment gateway in the backend
		$this->init_form_fields();
		$this->init_settings();

		// This controls the settings which the user sees during checkout
		$this->title		= $this->get_option( 'title' );
		$this->description	= $this->get_option( 'description' );
		$this->icon			= $this->get_option( 'icon_url' );

		// Check if logging to WooCommerce is enabled
		Logger::$enabled = ( $this->get_option( 'logging' ) === 'yes' ) ? true : false;

		// Add a hook to save the settings of this payment gateway
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Add a hook to handle callbacks when the WooCommerce API is triggered for this payment gateway
		add_action( 'woocommerce_api_wc_stripe_checkout', array( $this, 'process_callback' ) );
	}

	/**
	 * Get payment gateway settings that will be shown in the backend
	 */
	public function init_form_fields() {
		$this->form_fields = include __DIR__ . '/../includes/stripe-checkout-settings.php';
	}

	/**
	 * Handle the processing and payment of an order by creating a new Stripe Checkout session. Finally, redirect the user to the Stripe Checkout page
	 */
	public function process_payment( $order_id ) {
		include_once __DIR__ . '/../classes/stripe-checkout-session.php';

		$order = wc_get_order( $order_id );

		// Pass this payment gateway object and the current WooCommerce order to the new Stripe checkout session
		$stripe_checkout_session = new Stripe_Checkout_Session( $this, $order );

		return array(
			'result'	=> 'success',
			'redirect'	=> $stripe_checkout_session->redirect()		// Redirect the user to the Stripe Checkout page
		);
	}

	/**
	 * Handle incoming events sent by Stripe when the WooCommerce API is triggered for this payment gateway
	 */
	public function process_callback() {

		if ( $this->get_option( 'callback' ) === 'yes' ) {
			include_once __DIR__ . '/../classes/stripe-checkout-callback.php';

			$callback = new Stripe_Checkout_Callback( $this );	// Pass this payment gateway object to the new Stripe callback object
			$callback->process();
		}
	}
}