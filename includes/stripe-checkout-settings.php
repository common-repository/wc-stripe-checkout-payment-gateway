<?php
/**
 * Description:
 *		1. Payment gateway settings that can be configured in the backend
 *
 * References:
 *		1. https://docs.woocommerce.com/document/payment-gateway-api
 *
 * Last Updated: 1-SEP-21
 */

defined( 'ABSPATH' ) or exit;

return array(
	'enabled'			=> array(
		'title'			=> __( 'Enable/Disable', 'woocommerce' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable Stripe Checkout', 'woocommerce' ),
		'default'		=> 'no',
	),
	'title'				=> array(
		'title'			=> __( 'Title', 'woocommerce' ),
		'type'			=> 'text',
		'desc_tip'		=> true,
		'description'	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'		=> __( 'Stripe Checkout', 'woocommerce' ),
	),
	'description'		=> array(
		'title'			=> __( 'Description', 'woocommerce' ),
		'type'			=> 'text',
		'desc_tip'		=> true,
		'description'	=> __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
		'default'		=> '',
		'placeholder'	=> __( 'Optional', 'woocommerce' ),
	),
	'icon_url'			=> array(
		'title'			=> __( 'Icon URL', 'woocommerce' ),
		'type'			=> 'text',
		'desc_tip'		=> true,
		'description'	=> __( 'This controls the icon which the user sees during checkout.', 'woocommerce' ),
		'default'		=> '',
		'placeholder'	=> __( 'Optional', 'woocommerce' ),
	),
	'live_mode_publishable_key' => array(
		'title'			=> __( 'Publishable Key (Live Mode)', 'woocommerce' ),
		'type'			=> 'text',
		'desc_tip'		=> true,
		'description'	=> __( 'Get your API keys from the dashboard.', 'woocommerce' ),
		'default'		=> '',
	),
	'live_mode_secret_key' => array(
		'title'			=> __( 'Secret Key (Live Mode)', 'woocommerce' ),
		'type'			=> 'text',
		'desc_tip'		=> true,
		'description'	=> __( 'Get your API keys from the dashboard.', 'woocommerce' ),
		'default'		=> '',
	),
	'test_mode'			=> array(
		'title'			=> __( 'Test Mode', 'woocommerce' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable test mode', 'woocommerce' ),
		'description'	=> __( 'Note: Please enter your test keys below if you enable test mode.', 'woocommerce' ),
		'default'		=> 'no',
	),
	'test_mode_publishable_key' => array(
		'title'			=> __( 'Publishable Key (Test Mode)', 'woocommerce' ),
		'type'			=> 'text',
		'desc_tip'		=> true,
		'description'	=> __( 'Get your API keys from the dashboard.', 'woocommerce' ),
		'default'		=> '',
	),
	'test_mode_secret_key' => array(
		'title'			=> __( 'Secret Key (Test Mode)', 'woocommerce' ),
		'type'			=> 'text',
		'desc_tip'		=> true,
		'description'	=> __( 'Get your API keys from the dashboard.', 'woocommerce' ),
		'default'		=> '',
	),
	'callback'			=> array(
		'title'			=> __( 'Callback', 'woocommerce' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable callback', 'woocommerce' ),
		'description'	=> __( 'Endpoint is ' . get_site_url( null, 'wc-api/wc_stripe_checkout/' ), 'woocommerce' ),
		'default'		=> 'no',
	),
	'webhook_key' => array(
		'title'			=> __( 'Webhook Key', 'woocommerce' ),
		'type'			=> 'text',
		'description'	=> __( 'Note: Please enter your webhook key above if callback is enabled.', 'woocommerce' ),
		'default'		=> '',
	),
	'logging'			=> array(
		'title'			=> __( 'Logging', 'woocommerce' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable logging', 'woocommerce' ),
		'description'	=> __( '<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' . __( 'View all logs', 'woocommerce' ) . '</a>' ),
		'default'		=> 'no',
	)
);