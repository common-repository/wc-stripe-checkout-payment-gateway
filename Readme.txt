=== Payment Gateway with Stripe for WooCommerce ===
Contributors: cloudbasemy
Tags: stripe, stripe checkout, woocommerce, payment gateway, pci-compliant, sca-ready
Requires at least: 5.8
Tested up to: 6.0
Requires PHP: 7.3
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Collect payments from your customers using Stripe Checkout in WooCommerce. Supports various payment methods including credit cards, Apple Pay, Google Pay.

== Description ==

This is a payment gateway built for WooCommerce using Stripe Checkout. Your customers are redirected to a secure page hosted by Stripe to make payments for orders made via your WooCommerce store. It works across multiple devices and is designed to help increase conversion.

= Main Features =

- Designed to remove friction—Real-time card validation with built-in error messaging

- Mobile-ready—Fully responsive design with Apple Pay and Google Pay

- International—Supports over 25 languages and multiple payment methods

- Customization and branding—Customizable buttons and background color for the payments page

- Fraud and compliance—Simplified PCI compliance, SCA-ready, and CAPTCHAs to mitigate card testing attacks

- Additional features—Apply discounts, collect taxes, send email receipts, and more

== Installation ==

Once the plugin is installed and activated, please do the following:

1. Enable the payment gateway in WooCommerce
2. Obtain your API keys from your Stripe account and set the publishable and secret keys
3. Enable the callback functionality and set your webhook key if you wish to receive events from Stripe

== Frequently Asked Questions ==

= What are the requirements to use this plugin? =

You must have a registered Stripe account before using this plugin. To register, please go to Stripe's website and enter your personal or business registration details.

= What are the supported payment methods? =

This plugin supports all major Credit Cards, Apple Pay, Google Pay, Afterpay / Clearpay, Alipay, SEPA Direct Debit, Bacs Direct Debit, BECS Direct Debit, iDEAL, Bancontact, Giropay, Sofort, EPS, Przelewy24, GrabPay, FPX.

= What are the supported currencies? =

This plugin supports payments in 135+ currencies, allowing you to charge customers in their native currency while receiving funds in yours. For a full list of supported currencies, please refer to this [link](https://stripe.com/docs/currencies).

= How are customer's credit card or banking details managed? =

This plugin does not store any credit card information or banking details (e.g. username, password) on your website. All payments are done via a secure, Stripe-hosted payment page. It has a built-in fraud prevention and is PCI compliant and SCA-ready.

= Does this plugin support subscriptions or recurring payments? =

This plugin only supports one-time payments and does not support subscriptions or recurring payments. We will probably include support for this feature in the future.

= Can I customize the Stripe Checkout page? =

Yes, you can customize the page from your Stripe account. For more information, please refer to this [link](https://stripe.com/docs/payments/checkout/customization).

= Do you provide support for this plugin? =

Yes, support is provided on a voluntary basis. You may report issues or send us your questions in the Support section of this plugin or contact us at [https://cloudbase.my/contact](https://cloudbase.my/contact)

== Screenshots ==
1. The Stripe Checkout settings page located at the WooCommerce backend
2. The Stripe Checkout payment method shown at the WooCommerce frontend once the plugin is enabled
3. Example of the Stripe Checkout payment page