<?php

/**
 * Defines all logic related to shop pages and cart handling
 *
 * Loads public.js script on every shop page. Adds meta tag with shop hashid on
 * every store page.
 *
 * Main logic of Checkout X is handled here:
 * - we include JSON encoded cart content on the page inside script
 * tag. This script tag is inserted every time any shop page is loaded
 * ( product page, cart page, etc). Also we add key with scrip tag id to
 * cart_fragments, when cart content is changed via AJAX call => script tag
 * content is updated via `get_refreshed_fragments` flow.
 * - JSON encoded cart content is used by Checkout X to sync cart to latest state
 *
 * When user goes to checkout page - we redirect him to Checkout X checkout page
 * if proper cookie is found on the page.
 *
 * Also adds logic to clear cart when order on Checkout X side is completed.
 *
 * @link       checkout-x.com
 * @since      1.0.0
 * @package    Checkout_X
 * @subpackage Checkout_X/public
 * @author     Checkout X
 */

const CHECKOUT_URL_COOKIE = 'checkout_x_checkout_url';

class Checkout_X_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(CHKX_PLUGIN_FILE) . 'assets/css/public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(CHKX_PLUGIN_FILE) . 'assets/js/public.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name,
            'checkout_x_data',
            array(
                'admin_ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('clear_cart_nonce'),
            )
        );
    }

    /**
     * Adds meta tag with shop ID to be used by Checkout X storefront script
     */
    public function add_resources()
    {
        $connect_js_url = $this->get_storefront_script_url();
        $shop_id = $this->get_shop_id();

        # DEPRECATED: data-checkout-x-id inside script tag is used by existing
        # shops, new shops will use meta tag instead. Remove data-checkout-x-id
        # attribute when all shops are updated.
        echo "
          <meta name='checkout-x-shop-id' content=\"$shop_id\" />
          <script type='text/javascript' data-checkout-x-id=\"$shop_id\" src=\"$connect_js_url\"></script>
        ";
    }

    /**
     * Fetches shop ID from settings
     */
    public function get_shop_id()
    {
        return get_option('checkout_x_shop_id');
    }

    /**
     * Fetches Checkout X storefront script url value from settings
     */
    public function get_storefront_script_url()
    {
        return get_option('checkout_x_storefront_url');
    }

    /*
     * Adds parent tag with cart content inside on the page.
     *
     * Cart content element is placed within "#checkout-x-content" div to
     * reduce JS load when checking updates. We use MutationObserver
     * and having parent element allows to observe only child elements.
     */
    public function add_cart_json()
    {
        $cart_content = $this->cart_content();
        echo "<div id='checkout-x-content' style='display: none;'>$cart_content</div>";
    }

    /*
     * Adds cart content to cart fragments.
     *
     * Key here is jQuery selector of element. Value - content of element to
     * replace found selector with. WooCommerce iterates over each key in
     * refreshed fragments
     */
    public function add_cart_json_fragments($fragments)
    {
        $fragments['#checkout-x-cart-content'] = $this->cart_content();
        return $fragments;
    }

    /**
     * Loads cart from WC root function or global $woocommerce variable
     *
     * TODO: WC function is available from version 2.1 of WooCommerce, most probably
     * we don't need it since we support WooCommerce version only > 4
     */
    public function maybe_load_cart() {

      if (function_exists('WC')) {
        if (empty(WC()->cart)) {
          WC()->cart = new WC_Cart();
        }
      } else {
        global $woocommerce;
        if (empty( $woocommerce->cart)) {
          $woocommerce->cart = new WC_Cart();
        }
      }
    }

    public function maybe_init_session() {
        if (function_exists( 'WC' ) ) {
            if (!WC()->session->has_session()) {
                WC()->session->set_customer_session_cookie(true);
            }
        }
    }

    /**
     * Generates script tag with cart content encoded as JSON
     *
     *
     * We also add "properties" key to each item with "product_id" entry. It's
     * necessary for WooCommerce since Checkout X expect every checkout item to
     * be unique and uses combination of variant_id and properties to validate
     * uniqueness. For WooCommerce shop variant_id could be same for different
     * products.
     */
    private function cart_content()
    {
        $this->maybe_load_cart();

        $cart_content = WC()->cart->get_cart_contents();
        foreach($cart_content as &$cart_item) {
            $properties = array(
                "product_id" => $cart_item['product_id']
            );

            $cart_item["properties"] = $properties;
        };

        $cart_json = wp_json_encode($cart_content);

        // script tag with application/json tag allows us to put JSON content
        // directly on the page w/o escaping/encoding it
        return "
          <script type='application/json' id='checkout-x-cart-content'>$cart_json</script>
        ";
    }

    /**
     * Redirects to Checkout X checkout page if cart is not empty and user
     * reaches /checkout page
     */
    public function checkout_nav_menu_redirect()
    {
      if (is_checkout() && !$this->isCartEmpty()) {
        $checkout_url = wc_get_page_permalink( 'checkout' );
        $new_checkout_url = $this->checkoutx_checkout_url($checkout_url);

        if ($new_checkout_url != $checkout_url) {
          wp_redirect($new_checkout_url);
          exit();
        }
      }
    }

    /**
     * Build checkout_url based on cookie set by Checkout X script
     *
     * Returns original url if cookie is not set
     */
    public function checkoutx_checkout_url($original_url)
    {
      if (isset($_COOKIE[CHECKOUT_URL_COOKIE])) {
        return $_COOKIE[CHECKOUT_URL_COOKIE];
      } else {
        return $original_url;
      }
    }

    /**
     * Empties WooCommerce cart.
     *
     * Available for public.js via wp_localize_script. Called when Checkout X API
     * indicates that checkout is already completed.
     */
    public function clear_cart()
    {
        $this->maybe_init_session();
        $this->maybe_load_cart();

        if (!$this->isCartEmpty()) {
            WC()->cart->empty_cart();
        }

        echo json_encode(WC()->cart->get_cart_contents());

        # this method is called via AJAX so it's safe to die here
        wp_die();
    }

    private function isCartEmpty()
    {
      return WC()->cart->get_cart_contents_count() == 0;
    }

    public function redirect_to_checkout() {
      if (!is_checkout() || is_null(WC()->cart) || WC()->cart->is_empty() || !$this->is_checkout_x_enabled()) {
        return null;
      }

      $cart = WC()->cart;
      $coupons = $cart->get_applied_coupons();
      $discount_code = isset($coupons[0]) ? $coupons[0] : null;

      $data = array(
        "shop_id" => get_option("checkout_x_shop_id"),
        "items" => $this->build_checkout_items($cart),
        "discount_code" => $discount_code,
        "client_id" => $this->client_id(),
      );

      $response = wp_remote_post($this->api_v2_url("checkouts"), array(
        "timeout" => 5,
        "headers" => array(
          "content-type" => "application/json",
        ),
        "cookies" => array(),
        "body" => json_encode($data),
      ));

      if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
      } else {
        if ($response["response"]["code"] == 201) {
          $json_response = json_decode($response["body"]);
          $checkout_url = $json_response->url;
          WC()->session->set("checkout_x_checkout_id", $json_response->id);
          wp_redirect($checkout_url);
          exit();
        }
      }
    }

    public function add_to_cart() {
      if (!$this->is_checkout_x_enabled()) {
        return null;
      }

      $cart = WC()->cart;
      $data = array(
        "shop_id" => get_option("checkout_x_shop_id"),
        "client_id" => $this->client_id(),
        "items" => $this->build_checkout_items($cart),
        "event" => "added_to_cart",
      );

      $response = wp_remote_post($this->api_v2_url("session_events"), array(
        "timeout" => 2,
        "headers" => array(
          "content-type" => "application/json",
        ),
        "cookies" => array(),
        "body" => json_encode($data),
      ));
    }

    public function page_visit() {
      if (is_null(WC()->cart) || is_null(WC()->session) || is_ajax()) {
        return null;
      }

      $data = array(
        "shop_id" => get_option("checkout_x_shop_id"),
        "client_id" => $this->client_id(),
        "event" => "landed_on_shop_page",
        "checkout_id" => WC()->session->get("checkout_x_checkout_id"),
      );

      $response = wp_remote_post($this->api_v2_url("session_events"), array(
        "timeout" => 2,
        "headers" => array(
          "content-type" => "application/json",
        ),
        "cookies" => array(),
        "body" => json_encode($data),
      ));

      if (!is_wp_error($response)) {
        if ($response["response"]["code"] == 200) {
          $json_response = json_decode($response["body"]);

          WC()->session->set("checkout_x_enabled", $json_response->enabled);

          if ($json_response->clear_cart) {
            WC()->cart->empty_cart();
            WC()->session->set("checkout_x_checkout_id", null);
          }
        }
      }
    }

    private function build_checkout_items($cart) {
      $items = array();

      foreach ($cart->get_cart() as $key => $cart_item) {
        $items[] = array(
          "product_id" => $cart_item["product_id"],
          "variant_id" => $cart_item["variation_id"],
          "quantity" => $cart_item["quantity"],
          "properties" => $cart_item["variation"],
        );
      }

      return $items;
    }

    private function client_id() {
      $client_id = WC()->session->get("checkout_x_client_id");
      if (is_null($client_id)) {
        $client_id = bin2hex(openssl_random_pseudo_bytes(30));
        WC()->session->set("checkout_x_client_id", $client_id);
      }

      return $client_id;
    }

    private function api_v2_url($path) {
      $base_url = get_option("checkout_x_api_url");
      $v2_url = path_join($base_url, "v2");
      $endpoint_url = path_join($v2_url, $path);

      return $endpoint_url;
    }

    private function is_checkout_x_enabled() {
      return WC()->session->get('checkout_x_enabled');
    }
}
