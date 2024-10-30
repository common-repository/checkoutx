<?php

/**
 * Fired during plugin uninstall.
 *
 * This class runs all code that should be executed on plugin uininstall.
 *
 * @link       checkout-x.com
 * @since      1.1.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Checkout X
 */

class Checkout_X_Uninstallation 
{
  /**
   * Runs on plugin uninstall. Sends 'uninstall' event to Checkout X.
   * @since 1.1.0
   */
  public static function uninstall()
  {
    require_once plugin_dir_path(CHKX_PLUGIN_FILE) . 'includes/class-status-notifier.php';

    $notifier = new Checkout_X_Status_Notifier();
    $notifier->plugin_uninstalled();
  }
}
?>
