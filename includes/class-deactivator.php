<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       checkout-x.com
 * @since      1.0.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Checkout X
 */

class Checkout_X_Deactivator {

  /**
   * Runs on plugin deactivation. Sends 'deactivated' event to Checkout X.
   *
   * Uses StatusNotifier to send http query to Checkout X app.
   * @since    1.0.0
   */

  public static function deactivate() {
    require_once plugin_dir_path(CHKX_PLUGIN_FILE) . 'includes/class-status-notifier.php';

    $notifier = new Checkout_X_Status_Notifier();
    $notifier->plugin_deactivated();
  }

}
