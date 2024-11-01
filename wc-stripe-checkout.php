<?php
/**
 * Plugin Name:			Payment Gateway with Stripe for WooCommerce
 * Description:			This plugin allows your website to collect payments using Stripe Checkout in WooCommerce. Users are redirected to a prebuilt payment page hosted on Stripe to make payments.
 * Version:				1.0.0
 * Requires at least:	5.8
 * Requires PHP:		7.3
 * Author:				Cloudbase
 * Author URI:			https://cloudbase.my
 * License:				GPLv3 or later
 * License URI:			https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Last Updated:		8-SEP-21
 */

defined( 'ABSPATH' ) or exit;

/**
 * Check if WooCommerce is active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Add new links (e.g. Settings) that can be accessed via the Installed Plugins page
 */
function stripe_checkout_plugin_links ( $links ) {
	$new_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . plugin_basename( __DIR__ ) ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>'
	);

	return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'stripe_checkout_plugin_links' );

/**
 * Add the Stripe Checkout payment gateway to the list of available payment methods in WooCommerce
 */
function add_stripe_checkout( $methods ) {
	$methods[] = 'WC_Stripe_Checkout';

	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_stripe_checkout' );

/**
 * Load the payment gateway class which also extends the WooCommerce base gateway class
 */
function initialize_stripe_checkout_class() {
	require_once __DIR__ . '/classes/stripe-checkout-gateway.php';
}
add_action( 'plugins_loaded', 'initialize_stripe_checkout_class' );