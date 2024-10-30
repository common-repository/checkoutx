<?php

/**
 * Declares endpoints for plugin REST API extension
 *
 * Defines two endpoints in /wc-checkoutx/v1/ namespace:
 * - GET /status
 *   No authentication required. Checkout X uses this endpoint to ensure that shop 
 *   has plugin installed and checkcurrent version of the plugin.
 *   Returns
 *   - 200 with JSON body { "version": "<plugin version>" } 
 *
 *
 * - PUT /settings
 *   Requires authentcation. Only accessible by admins. 
 *   Has three mandatory query params:
 *   - shop_id - Checkout X shop hashid
 *   - js_script_url - url to load Checkout X storefront script from
 *   - event_secret - secret to generate request signature when sending notifications
 *   to Checkout X ( i.e. on pludin activation / deactivation )
 *   Updates plugin settings with values passed in request.
 *   Returns:
 *   - 200 with JSON body { "result": "success" } when shop settings
 *   successfully updated to given values or already was valid
 *   - 403 with "result": "error" and current status of each setting when any
 *   of the settings failed update
 *
 * All endpoints have custom header added:
 * - X-CHECKOUT-X-PLUGIN-VERSION - contains current version of plugin
 *
 * @link       checkout-x.com
 * @since      1.0.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Checkout X
 */

class Checkout_X_API_Controller
{
  /**
   * wc- prefix here is mandatory to indicate WooCommerce that this endpoint is
   * third-party extension of Woo REST API. All endpoints within this namespace
   * support standard WooCommerce authentication automatically
   */
  protected $namespace = 'wc-checkoutx';

  protected $api_version = 'v1';

  /**
   * Registers endpoints and defines params, callback and permission check
   * callback for each of them
   */
  public function register_routes()
  {
    register_rest_route(
      $this->namespace,
      '/' . $this->api_version . '/status',
      array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => array($this, 'get_status'),
        'permission_callback' => '__return_true' # allow to check for version w/o API keys
      )
    );

    register_rest_route(
      $this->namespace,
      '/' . $this->api_version . '/settings',
      array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => array($this, 'update_settings'),
        'args' => array(
          'shop_id' => array(
            'required' => true
          ),
          'js_script_url' => array(
            'required' => true
          ),
          'event_secret' => array(
            'required' => true
          ),
        ),
        'permission_callback' => array($this, 'check_is_admin')
      )
    );
  }

  /**
   * GET /status callback
  */
  public function get_status()
  {
    $data = array(
      "version" => WC_CHECKOUT_X_VERSION,
      "options" => array(
        "js_script_url" => get_option("checkout_x_storefront_url", null),
        "api_version" => get_option("checkout_x_api_version", null),
        "app_url" => get_option("checkout_x_app_url", null),
        "api_url" => get_option("checkout_x_api_url", null),
        "website_url" => get_option("checkout_x_website_url", null),
      ),
    );
    return $this->wrap_response($data, 200);
  }

  /**
   * PUT /settings callback
   *
   * Uses get_option first to fetch current value of the setting. Need it to
   * respond with success when setting already had proper value.
   * update_option returns false when called with same value as setting already
   * has
   *
   * 200 when all settings successfully updated or aleady have proper value
   * 403 when failed to update any of settings
   */
  public function update_settings($request)
  {
    $new_shop_id = $request->get_param('shop_id');
    $current_shop_id = get_option( 'checkout_x_shop_id', '');

    $new_js_script_url = $request->get_param('js_script_url');
    $current_js_script_url = get_option( 'checkout_x_storefront_url', '');

    $new_event_secret = $request->get_param('event_secret');
    $current_event_secret = get_option('checkout_x_event_secret', '');

    $new_api_version = $request->get_param("api_version");
    $current_api_version = get_option("checkout_x_api_version", "");

    $new_checkout_app_url = $request->get_param("app_url");
    $current_checkout_app_url = get_option("checkout_x_app_url", "");

    $new_checkout_api_url = $request->get_param("api_url");
    $current_checkout_api_url = get_option("checkout_x_api_url", "");

    $new_checkout_website_url = $request->get_param("website_url");
    $current_checkout_website_url = get_option("checkout_x_website_url", "");

    $shop_id_updated = ($new_shop_id == $current_shop_id) || update_option('checkout_x_shop_id', $new_shop_id);
    $script_url_updated = ($new_js_script_url == $current_js_script_url) || update_option('checkout_x_storefront_url', $new_js_script_url);
    $event_secret_updated = ($new_event_secret == $current_event_secret) || update_option('checkout_x_event_secret', $new_event_secret);
    $api_version_updated = ($new_api_version == $current_api_version) || update_option('checkout_x_api_version', $new_api_version);
    $app_url_updated = ($new_checkout_app_url == $current_checkout_app_url) || update_option('checkout_x_app_url', $new_checkout_app_url);
    $api_url_updated = ($new_checkout_api_url == $current_checkout_api_url) || update_option('checkout_x_api_url', $new_checkout_api_url);
    $website_url_updated = ($new_checkout_website_url == $current_checkout_website_url) || update_option('checkout_x_website_url', $new_checkout_website_url);

    if ($shop_id_updated
      && $script_url_updated
      && $event_secret_updated
      && $api_version_updated
      && $app_url_updated
      && $api_url_updated
      && $website_url_updated
    ) {
      return $this->wrap_response(array('result' => 'success'), 200);
    } else {
      $payload = array(
        'result' => 'error',
        'shop_id_updated' => $shop_id_updated,
        'script_url_updated' => $script_url_updated,
        'event_secret_updated' => $event_secret_updated,
        'api_version' => $api_version_updated,
        'app_url' => $app_url_updated,
        'api_url' => $api_url_updated,
        'website_url' => $website_url_updated,
      );
      return $this->wrap_response($payload, 403);
    }
  }

  /**
   * checks that current user has 'Administrator' level access
   * https://wordpress.org/support/article/roles-and-capabilities/#administrator
   */
  public function check_is_admin()
  {
    return current_user_can('administrator');
  }

  /**
   * Adds custom data to responses of all endpoints. Currently adds header with
   * plugin version only.
   */
  private function wrap_response($data, $status = HTTP_STATUS_OK, $headers = array())
  {
    return new WP_REST_Response(
      $data,
      $status,
      array_merge($headers, array(
        'X-CHECKOUT-X-PLUGIN-VERSION' => WC_CHECKOUT_X_VERSION
      ))
    );
  }
}
