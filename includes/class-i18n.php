<?php

/**
 * Define the internationalization functionality
 *
 * Not used at the moment.
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       checkout-x.com
 * @since      1.0.0
 *
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Vlad <just.raeno@icloud.com>
 */

class Checkout_X_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'checkout-x',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
