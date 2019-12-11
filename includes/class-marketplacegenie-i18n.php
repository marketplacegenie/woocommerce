<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.marketplacegenie.co.za/app/woocommerce/
 * @since      1.0.0
 *
 * @package    Marketplacegenie
 * @subpackage Marketplacegenie/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Marketplacegenie
 * @subpackage Marketplacegenie/includes
 * @author     Marketplacegenie (Pty) Ltd <dominic@wardslaus.co.za>
 */
class Marketplacegenie_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'marketplacegenie',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
