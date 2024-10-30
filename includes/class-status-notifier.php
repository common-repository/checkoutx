<?php

/**
 * Sends events to Checkout X when plugin state changes
 *
 * Currently sends three types of events:
 * - plugin activated
 * - plugin deactivated
 * - plugin uninstalled
 *
 * Uses `wp_remote_post` behind the scenes to make POST request to Checkout X
 * endpoint. Request body is JSON.
 *
 * Uses event secret stored inside of`checkout_x_event_secret` settingto generate 
 * signature and sends it as X-WC-CHECCHECKOUTX-PLUGIN-EVENT-SIGNATURE header,
 * necessary for request authentication on Checkout X 
 *
 * Base url for all requests id defined in CHECKOUTX_URL environment variable.
 * If no url present, use the production URL.
 *
 * @link       checkout-x.com
 * @since      1.1.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Checkout X
 */
class Checkout_X_Status_Notifier
{
  const DEFAULT_CHECKOUT_X_URL = 'https://app.checkout-x.com';

  public function plugin_activated()
  {
    $payload = array('event_type' => 'activated');
    $this->deliver_event($payload);
  }

  public function plugin_deactivated()
  {
    $payload = array('event_type' => 'deactivated');
    $this->deliver_event($payload);
  }

  public function plugin_uninstalled()
  {
    $payload = array('event_type' => 'uninstalled');
    $this->deliver_event($payload);
  }

  private function deliver_event($payload)
  {
    $url = $this->event_delivery_url();

    $body = trim(wp_json_encode($payload));
    $signature = $this->build_signature($body);

    $http_args = array(
      'blocking'    => false, # no need to wait, we don't care about response atm
      'body'        => $body,
      'headers'     => array(
        'Content-Type' => 'application/json',
        'X-WC-CHECKOUTX-PLUGIN-EVENT-SIGNATURE' =>$signature,
      )
    );

    wp_remote_post($url, $http_args);
  }

  /**
   * Build url to send event to.
   * 
   * Takes root url from ENV for dev/staging shops, we don't expect this environment variable
   * to be defined on real shops and we use production URL.
   *
   * Also relies on `checkout_x_shop_id` to be present and uses it as part of
   * event url.
   *
   */ 
  public function event_delivery_url()
  {
    $shop_id = get_option('checkout_x_shop_id');

    $checkout_x_url = getenv('CHECKOUTX_URL') ?: self::DEFAULT_CHECKOUT_X_URL;

    return $checkout_x_url . '/woo_commerce/shops/' . $shop_id . '/plugin_events';
  }


  /**
   * Builds signature similar to WooCommerce webhook signature to
   * authenticate requests from plugin on Checkout X side
   */
  public function build_signature($payload)
  {
    $event_secret = get_option('checkout_x_event_secret', '');

    return base64_encode(hash_hmac(
      'sha256',
      $payload,
      $event_secret,
      true )
    );
  }
}

?>
