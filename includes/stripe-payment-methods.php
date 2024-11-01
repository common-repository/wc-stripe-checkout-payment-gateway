<?php
/**
 * Description:
 *		1. Supported payment method types in Stripe Checkout
 *
 * References:
 *		1. https://stripe.com/docs/api/checkout/sessions/create
 *
 * Last Updated: 1-SEP-21
 */

defined( 'ABSPATH' ) or exit;

return array(
	'alipay',
	'card',
	'ideal',
	'fpx',
	'bacs_debit',
	'bancontact',
	'giropay',
	'p24',
	'eps',
	'sofort',
	'sepa_debit',
	'grabpay',
	'afterpay_clearpay',
	'acss_debit',
	'wechat_pay',
	'boleto',
	'oxxo'
);