<?php
/**
 * Description:
 *		1. Handle info and debug level logging into WooCommerce
 *
 * References:
 *		1. https://developer.woocommerce.com/2017/01/26/improved-logging-in-woocommerce-2-7
 *
 * Last Updated: 1-SEP-21
 */

defined( 'ABSPATH' ) or exit;

class Logger
{
	// Check if logging to WooCommerce is enabled from the payment gateway settings page
	public static $enabled = false;

	private static $logger	= false;
	private static $context = array( 'source' => 'stripe-checkout' );

	public static function get_logger() {
		if ( empty( self::$logger ) ) {
			self::$logger = wc_get_logger();
		}

		return self::$logger;
	}

	public static function info( $message ) {
		// Proceed only if logging is enabled and the WooCommerce logger is available
		if ( self::$enabled and self::get_logger() ) {
			self::$logger->info( $message, self::$context );
		}
	}

	public static function debug( $variable ) {
		// Proceed only if logging is enabled and the WooCommerce logger is available
		if ( self::$enabled and self::get_logger() ) {
			$variable = wc_print_r( $variable, true );		// Convert the variable into a string representation

			self::$logger->debug( $variable, self::$context );
		}
	}
}