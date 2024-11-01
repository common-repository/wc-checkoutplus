<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://pluginette.com
 * @since      1.0.0
 *
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/includes
 */

namespace CheckoutPlus;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/includes
 * @author     David Towoju <hello@pluginette.com>
 */
class i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wc-checkoutplus',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}


	/**
	 * Load translations
	 *
	 * @return void
	 */
	public static function load_translations() {
		return array(
			'header' => array(
				'title' => __( 'Checkout Fields', 'wc-checkoutplus' ),
				'link'  => array(
					'help'     => __( 'Help', 'wc-checkoutplus' ),
					'settings' => __( 'Settings', 'wc-checkoutplus' ),
					'save'     => __( 'Save', 'wc-checkoutplus' ),
				),
			),
			'nav'    => array(
				'menu' => array(
					'billing_fields'    => __( 'Billing Fields', 'wc-checkoutplus' ),
					'shipping_fields'   => __( 'Shipping Fields', 'wc-checkoutplus' ),
					'additional_fields' => __( 'Additional Fields', 'wc-checkoutplus' ),
				),
			),
		);
	}

}
