<?php

/**
 * Declares controllers for all REST API namespaces that plugin extends
 *
 *
 * @link       checkout-x.com
 * @since      1.0.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Checkout X
 */
class Checkout_X_API
{
  public function register_api_routes($controllers)
  {
    # we use 'wc-' prefix in our API so WooCommerce considers it as an extension
    # to their API and allows us to do authorization with same
    # consumer_key/consumer_password credentials we use for WooCommerce
    # based on https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-rest-authentication.php#L65
    $controllers['wc-checkoutx']['v1'] = 'Checkout_X_API_Controller';
    return $controllers;
  }
}
