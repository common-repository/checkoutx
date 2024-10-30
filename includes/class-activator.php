<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       checkout-x.com
 * @since      1.0.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Checkout X
 */

class Checkout_X_Activator
{
  /**
   * Runs on plugin activation. Sends 'activated' event to Checkout X
   *
   * Uses StatusNotifier to send http query to Checkout X app
   *
   * @since    1.0.0
   */

  public static function activate() {
    require_once plugin_dir_path(CHKX_PLUGIN_FILE) . 'includes/class-status-notifier.php';

    $notifier = new Checkout_X_Status_Notifier();
    $notifier->plugin_activated();
  }
}
