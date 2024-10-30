<?php
/**
 * Extends WooCommerce webhooks system with additional topics for Checkout X app
 *
 * 
 * Defines following additional webhooks:
 * - product_category.created
 * - product_category.updated
 * - product_category.deleted
 * - shop.updated
 *
 * @link       checkout-x.com
 * @since      1.0.5
 * @package    Checkout_X
 * @subpackage Checkout_X/includes
 * @author     Vlad <just.raeno@icloud.com>
 */

class Checkout_X_CustomWebhooks
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.5
     * @access   protected
     * @var      Checkout_X_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.5
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     * @deprecated Not used in this class anymore
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.5
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Adds hook for shop.updated and hooks for product category CRUD events
     *
     * Also sets plugin name ( deprecated, could be removed ) and version from
     * contructor params
     *
     * @since    1.0.5
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->add_settings_hook();
        $this->add_product_cat_hooks();
    }

    /**
     * Generates payload for product category and shop events
     *
     * Follow the same logic as default webhook implementationi in WooCommerce:
     * - get webhook by ID from database
     * - temporarily set user to owner of webhook
     * - make a request to WooCommerce REST API, fetch value for payload and update
     * $payload param of the method
     * - switch back to original user
     *
     * Called by wooocmmerce_webhook_payload filter, it expects this method to
     * update $payload with proper value
     */
    public function add_custom_webhook_payload($payload, $resource, $resource_id, $webhook_id)
    {
        if (empty($payload)) {
            $webhook = wc_get_webhook($webhook_id);

            switch ( $resource ) {
                case 'product_category':

                    // Set user to webhook owner
                    $current_user = get_current_user_id();
                    wp_set_current_user( $webhook->get_user_id() );

                    // Bulk and quick edit action hooks return a product object instead of an ID.
                    // if ( 'product' === $resource && 'updated' === $event && is_a( $resource_id, 'WC_Product' ) ) {
                    // 	$resource_id = $resource_id->get_id();
                    // }

                    $version = str_replace( 'wp_api_', '', $webhook->get_api_version() );
                    $endpoint = "/wc/{$version}/products/categories/{$resource_id}";
                    $payload = wc()->api->get_endpoint_data($endpoint);

                    // Restore the current user.
                    wp_set_current_user( $current_user );
                    break;
                case 'shop':
                    // Set user to webhook owner
                    $current_user = get_current_user_id();
                    wp_set_current_user( $webhook->get_user_id() );

                    // fetch shop "general" WC settings
                    $version = str_replace( 'wp_api_', '', $webhook->get_api_version() );
                    $endpoint = "/wc/{$version}/settings/general";
                    $payload = wc()->api->get_endpoint_data($endpoint);

                    // payload comes as array of settings. Wrap it under
                    // top-level key "settings.general"
                    $payload = array('general' => $payload);

                    // Restore the current user.
                    wp_set_current_user( $current_user );
                    break;
                default:
                    break;
            }
        }
        return $payload;
    }

    /**
     * Maps webhook topic name to action name
     *
     * Called by `woocommerce_webhook_topic_hooks`, expected to extend
     * dictionary of $topic_hooks
     */
    public function add_custom_topic_hooks($topic_hooks)
    {
        $new_hooks = array(
            'product_category.created' => array(
                    'product_category_created_webhook',
                ),
            'product_category.updated' => array(
                'product_category_updated_webhook',
                ),
            'product_category.deleted' => array(
                'product_category_deleted_webhook',
                ),
            'shop.updated' => array(
              'shop_updated_webhook',
              ),
            );
        return array_merge( $topic_hooks, $new_hooks );
    }

    /**
     * Adds our custom events to the list of allowed events
     *
     * Called by `woocommerce_valid_webhook_events` filter. Extends
     * $topic_events array with our events.
     */
    public function add_custom_topic_events($topic_events)
    {
        $new_events = array(
            'product_category_created',
            'product_cagegory_updated',
            'product_category_deleted',
            'shop_updated',
        );
    
        return array_merge( $topic_events, $new_events );
    }

    /**
     * Maps webhooks topics to webhook displayed name in admin dashboard
     *
     * __() method fetches localized version of the string. We don't use i18n
     * but it's left here for consistency with default WooCommerce
     * implementation.
     * Called by `woocommerce_webhook_topics`
     *
     */
    public function add_custom_webhook_topics($topics)
    {
	    $new_topics = array( 
            'product_category.created' => __( 'Product Category Created', 'woocommerce' ),
            'product_category.updated' => __( 'Product Category Updated', 'woocommerce' ),
            'product_category.deleted' => __( 'Product Category Deleted', 'woocommerce' ),
            'shop.updated' => __( 'Shop Settings Updated', 'woocommerce'),
		);

	    return array_merge( $topics, $new_topics );
    }

    /**
     * Adds our resources ( shop & product category ) to the list of valid resources
     *
     * Required for WC REST API.. Function that validates webhook topic is here: wordpress/wp-content/plugins/woocommerce/includes/wc-webhook-functions.php#67
     */
    public function extend_valid_webhook_resources($resources)
    {
        $new_resources = array(
          'product_category',
          'shop',
        );
        return array_merge($resources, $new_resources);
    }

    /**
     * Triggers shop.updated webhook on any change to WooCommerce settings
     */
    public function send_webhook_on_settings_change($setting_changed)
    {
      if (preg_match('/woocommerce_/', $setting_changed))
      {
        do_action( 'shop_updated_webhook' );
      }
    }

    /**
     * Subscribes to any Wordpress option change
     */
    private function add_settings_hook()
    {
        add_action('updated_option', array($this, 'send_webhook_on_settings_change'), 10, 1);
    }


    /**
     * Subscribes to product_cat CRUD events and trggers relevant webhook topic
     */
    private function add_product_cat_hooks()
    {
        add_action( 'created_product_cat', function($term_id, $tt_id = '') {
            do_action( 'product_category_created_webhook', $term_id );
        }, 10, 2);

        add_action( 'edited_product_cat', function( $term_id, $tt_id = '', $taxonomy = '' ) {
            do_action( 'product_category_updated_webhook', $term_id);
        }, 10, 3);

        # deleted_product_cat ( which is similar but wait till cache is cleared ) is not firing
        add_action( 'delete_product_cat', function($term_id, $tt_id = '', $deleted_term = '') {
            do_action( 'product_category_deleted_webhook', $term_id );
        }, 10, 4);
    }
}
