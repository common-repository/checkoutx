<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 * It also extends Wordpress REST API to include methods to check plugin status
 * and update plugin settings and extends WooCommerce webhooks to include additional 
 * webhooks required by Checkout X to work properly.
 *
 * @link       checkout-x.com
 * @since      1.0.0
 *
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Vlad <just.raeno@icloud.com>
 */

class Checkout_X
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Checkout_X_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        if (get_option("checkout_x_api_version") != "2") {
          $this->define_filters();
        }
        $this->define_custom_webhooks();
        $this->define_api();
        $this->configure_auto_update();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Checkout_X_Loader. Orchestrates the hooks of the plugin.
     * - Checkout_X_i18n. Defines internationalization functionality.
     * - Checkout_X_Admin. Defines all hooks for the admin area.
     * - Checkout_X_Public. Defines all hooks for the public side of the site.
     * - Checkout_X_API. Registers custom REST API namespace /wc-checkoutx/v1/.
     * - Checkout_X_API_Controller. Defines logic for REST API plugin endpoints.
     * - Checkout_X_CustomWebhooks. Extends WooCommerce webhooks with events
     *   Checkout X needs
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admin.php';

        /**
         * Classes responsible for Checkout X API on top of WooCommerce API
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-api.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-api-controller.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-public.php';
        
        /**
         * The class responsible for custom webhooks
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-custom-webhooks.php';
        $this->loader = new Checkout_X_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Checkout_X_i18n class in order to set the domain and to register the hook
     * with WordPress.
     * Currently plugin has almost no text and doesn't use i18n.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Checkout_X_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Checkout_X_Admin($this->get_version());

        $this->loader->add_filter('plugin_action_links_checkout-x/checkout-x.php', $plugin_admin, 'admin_settings_link');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Checkout_X_Public($this->get_plugin_name(), $this->get_version());

        if (get_option("checkout_x_api_version") == "2") {
          $this->loader->add_action("woocommerce_init", $plugin_public, "page_visit");
          $this->loader->add_action("woocommerce_add_to_cart", $plugin_public, "add_to_cart");
          $this->loader->add_action("template_redirect", $plugin_public, "redirect_to_checkout");
        } else {
          # loads plugin JS file on all shop pages
          # more info in assets/js/public.js documentation
          $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

          # adds meta tag with shop ID to <head>. Used by CHKX storefront JS.
          $this->loader->add_action('wp_head', $plugin_public, 'add_resources');

          // render cart content as json and sync with CHKX on every page load
          $this->loader->add_action('wp_footer', $plugin_public, 'add_cart_json');

          # redirect on '/checkout' page in menu
          $this->loader->add_action('template_redirect', $plugin_public, 'checkout_nav_menu_redirect');

          # clear cart when checkout is completed
          $this->loader->add_action('wp_ajax_chkx_clear_cart', $plugin_public, 'clear_cart');
          $this->loader->add_action('wp_ajax_nopriv_chkx_clear_cart', $plugin_public, 'clear_cart');
        }
    }

    /**
     * Registers hook that extends cart_fragments JSON with DOM element that
     * includes cart content for Checkout X storefront script
     */
    private function define_filters()
    {
        $plugin_public = new Checkout_X_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_filter('woocommerce_add_to_cart_fragments', $plugin_public, 'add_cart_json_fragments');
    }

    /**
     * Registers hook that extend WooCommerce webhooks with custom topics: 
     * - product_category CRUD
     * - shop.updated
     */
    private function define_custom_webhooks()
    {
        $custom_webhooks = new Checkout_X_CustomWebhooks($this->get_plugin_name(), $this->get_version());

        $this->loader->add_filter( 'woocommerce_webhook_topic_hooks', $custom_webhooks, 'add_custom_topic_hooks');
        $this->loader->add_filter( 'woocommerce_valid_webhook_events', $custom_webhooks, 'add_custom_topic_events');
        $this->loader->add_filter( 'woocommerce_webhook_topics' , $custom_webhooks, 'add_custom_webhook_topics');

        $this->loader->add_filter( 'woocommerce_webhook_payload', $custom_webhooks, 'add_custom_webhook_payload', 10, 4);

        // Required for WC REST API. 
        // Function that validates webhook topic is here: wordpress/wp-content/plugins/woocommerce/includes/wc-webhook-functions.php#67
        $this->loader->add_filter( 'woocommerce_valid_webhook_resources', $custom_webhooks, 'extend_valid_webhook_resources');

    }

    /**
     * Registers filter that externds REST API namespaces with plugin specific /wc-checkoutx
     */
    public function define_api()
    {
      $api = new Checkout_X_API();
      $this->loader->add_filter( 'woocommerce_rest_api_get_rest_namespaces', $api, 'register_api_routes');
    }

    /**
     * Registers filter that is triggered when Wordpress check whether auto
     * update is enabled for plugin or not
     */
    public function configure_auto_update()
    {
      $this->loader->add_filter( 'auto_update_plugin', $this, 'enable_auto_update', 10, 2);
    }


    /**
     * Triggered by `auto_update_plguin` filter. Returns true for our plugin to
     * indicate that plugin should always have auto-update enabled
     */
    public function enable_auto_update( $update, $item)
    {
      if ($item->slug == $this->get_plugin_name()) {
        # force auto update for our plugin
        return true;
      } else {
        # use normal API response to determine for other plugins
        return $update;
      }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return WC_CHECKOUT_X_PLUGIN_NAME;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Checkout_X_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return WC_CHECKOUT_X_VERSION;
    }

}
