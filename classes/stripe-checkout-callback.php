<?php
/**
 * Description:
 *		1. Create a new callback object to handle incoming events sent by Stripe to a webhook endpoint
 *		2. Verify an incoming Stripe event before it is processed further
 *		3. Process and update the status of a WooCommerce order if the payment was successful or has failed
 *
 * References:
 *		1. https://stripe.com/docs/webhooks
 *		2. https://docs.woocommerce.com/document/payment-gateway-api
 *
 * Last Updated: 1-SEP-21
 */

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/../classes/stripe-checkout-utility.php';
require_once __DIR__ . '/../includes/stripe-php-master/init.php';

use Stripe_Checkout_Utility as Utility;

class Stripe_Checkout_Callback
{
	private $gateway;

	/**
	 * Constructor for a new callback object
	 */
	public function __construct( $stripe_checkout_gateway ) {
		$this->gateway	= $stripe_checkout_gateway;
	}

	/**
	 * The main function of this class that handles incoming events sent by Stripe
	 */
	public function process() {
		http_response_code(403);	// By default, send a 403 error response to Stripe to indicate that there was a problem processing the incoming event

		$event = $this->verify_stripe_event();	// Firstly, verify if the incoming Stripe event is valid

		$payment_intent = $event['data']['object'];		// Retrieve the payment intent after a Stripe event has been verified

		// Process the payment intent only if it was successful or has failed. Other events are ignored for the time being
		if ( Utility::string_starts_with( $payment_intent['id'], 'pi_') ) {
			// Get the order from WooCommerce based on the order number found in the payment intent's metadata
			$order_no	= $payment_intent['metadata']['woocommerce_order_no'];
			$order		= wc_get_order( $order_no );

			if ( $event['type'] === 'payment_intent.succeeded' ) {

				if ( $payment_intent['status'] === 'succeeded' ) {
					$this->payment_success( $payment_intent, $order );
				}

			} elseif ( $event['type'] === 'payment_intent.payment_failed' ) {

				if ( $payment_intent['status'] === 'requires_payment_method' ) {
					$this->payment_failed( $payment_intent, $order );
				}

			}

		}

		// If there was no problem in processing the event, send a successful 200 response to Stripe to confirm receipt of an event
		http_response_code(200);
	}

	/**
	 * Verify a Stripe event's payload with the signature header and webhook key
	 */
	private function verify_stripe_event() {
		$payload		= file_get_contents( 'php://input' );
		$signature		= $_SERVER['HTTP_STRIPE_SIGNATURE'];
		$webhook_key	= $this->gateway->get_option( 'webhook_key' );

		// This will throw an exception if the Stripe event has an invalid payload or signature
		return \Stripe\Webhook::constructEvent( $payload, $signature, $webhook_key );
	}

	/**
	 * Handle successful payment events
	 */
	private function payment_success( $payment_intent, $order ) {
		// Do not process the order if payment has been successfully received before
		if ( $order->has_status( array( 'processing', 'completed' ) ) ) {
			return;
		}

		// Retrieve the charge object from the payment intent
		$charge = $payment_intent['charges']['data'];
		$charge = $charge[0];

		// Check the payment method type. See https://stripe.com/docs/api/charges/retrieve
		$payment_method_details	= $charge['payment_method_details'];
		$payment_method_type	= $charge['payment_method_details']['type'];

		$order->add_meta_data( 'successful_payment_method_details', json_encode( $payment_method_details, JSON_PRETTY_PRINT ), true );

		$order->add_order_note( __( 'Payment method type is ' . $payment_method_type, 'woocommerce' ) );

		// Let WooCommerce handle the status. Order will be marked as either 'Completed' or 'Processing' if payment is successful
		$order->payment_complete( $payment_intent['id'] );

		Logger::info( 'Payment intent successful for Order #' . $order->get_order_number() );
		Logger::debug( $payment_intent );
	}

	/**
	 * Handle failed payment events
	 */
	private function payment_failed( $payment_intent, $order ) {
		$error_message			= $payment_intent['last_payment_error']['message'];

		// Check the payment method type. See https://stripe.com/docs/api/payment_intents/object
		$payment_method_details	= $payment_intent['last_payment_error']['payment_method'];
		$payment_method_type	= $payment_intent['last_payment_error']['payment_method']['type'];

		$order->add_meta_data( 'failed_payment_method_details', json_encode( $payment_method_details, JSON_PRETTY_PRINT ), true );

		$order->add_order_note( __( 'Payment method type is ' . $payment_method_type, 'woocommerce' ) );
		$order->add_order_note( __( $error_message, 'woocommerce' ) );

		// If the payment fails and the order has already been created in WooCommerce, set the order status to 'Failed'
		$order->update_status( 'failed' );

		Logger::info( 'Payment intent failed for Order #' . $order->get_order_number() );
		Logger::debug( $payment_intent );
	}
}