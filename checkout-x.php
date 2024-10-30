<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. Plugin version ready from here and set as constant.
 *
 * This file registers activation/deactivation and uninstall hooks for the
 * plugin. It also includes all of the dependencies used by the plugin and defines a functionthat starts the plugin.
 *
 * @link              https://www.checkout-x.com
 * @since             1.0.0
 * @package           Checkout_X
 *
 * @wordpress-plugin
 * Plugin Name:       Checkout X
 * Plugin URI:        https://www.checkout-x.com
 * Description:       Checkout X boosts your revenue with a high-converting, frictionless, mobile-first checkout experience for your WooCommerce store. Get less abandoned carts and more sales with a fast checkout that completes itself on any device to give you more conversions and average order value.
 * Version:           1.2.1
 * Author:            Checkout X
 * Tested up to:      5.8.2
 * WC tested up to:   6.0.0
 * Author URI:        https://www.checkout-x.com/?utm_source=partner&utm_medium=woocommerce-marketplace
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       checkout-x
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * defines constant to be used as a root in building absolute path when other 
 * files need include/reqiure some dependency
 */
if ( ! defined( 'CHKX_PLUGIN_FILE' ) ) {
  define( 'CHKX_PLUGIN_FILE', __FILE__ );
}

/**
 * fetches plugin version from plugin info above and sets it as constant
 * plugin version is sent on API status request and also used in assets
 * filenames digest
 */
if ( ! defined( 'WC_CHECKOUT_X_VERSION' ) ) {
  $plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ), false );
  define( 'WC_CHECKOUT_X_VERSION', $plugin_data['Version'] );
}

/** 
 * defines plugin name constant, used when ensuring that auto-update is enabled
 * for the plugin
 */
if ( ! defined( 'WC_CHECKOUT_X_PLUGIN_NAME' ) ) {
  define( 'WC_CHECKOUT_X_PLUGIN_NAME', 'checkout-x');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activator.php
 */
function activate_Checkout_X() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
  Checkout_X_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function deactivate_Checkout_X() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
  Checkout_X_Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstall.
 * This action is documented in includes/class-uninstallation.php
 */
function uninstall_Checkout_X() {
  require_once plugin_dir_path(__FILE__) . 'includes/class-uninstallation.php';
  Checkout_X_Uninstallation::uninstall();
}

register_activation_hook( __FILE__, 'activate_Checkout_X' );
register_deactivation_hook( __FILE__, 'deactivate_Checkout_X' );
register_uninstall_hook(__FILE__, 'uninstall_Checkout_X');

/**
 * The core plugin class that is used to define main logic,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-main.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Checkout_X() {

  $plugin = new Checkout_X();
  $plugin->run();

}
run_Checkout_X();
