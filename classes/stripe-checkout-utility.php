<?php
/**
 * Description:
 *		1. Various utility functions created to support the Stripe Checkout plugin
 *
 * Last Updated: 1-SEP-21
 */

defined( 'ABSPATH' ) or exit;

class Stripe_Checkout_Utility
{
	public static function string_starts_with( $haystack, $needle ) {
		return strpos( $haystack, $needle ) === 0;
	}

	public static function string_contains( $haystack, $needle ) {
		return empty( $needle ) || strpos( $haystack, $needle ) !== false;
	}
}