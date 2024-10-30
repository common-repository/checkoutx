<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       checkout-x.com
 * @since      1.0.0
 *
 * Displays info on how to connect the plugin to Checkout X
 *
 * @package    Checkout_X
 * @subpackage Checkout_X/admin
 * @author     Eri Digital
 */
class Checkout_X_Admin
{
    private $version;

    const CHECKOUTX_APP_URL = "https://app.checkout-x.com";
    const CHECKOUTX_WEBSITE_URL = "https://www.checkout-x.com";
    const HELP_CENTER_URL = "https://help.checkout-x.com";
    const BASE64_APP_ICON = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMzJweCIgaGVpZ2h0PSIzMnB4IiB2aWV3Qm94PSIwIDAgMzIgMzIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8dGl0bGU+aWNvbkA8L3RpdGxlPgogICAgPGcgaWQ9Imljb24iIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxwYXRoIGQ9Ik0yNS42MjUsMjQgQzI2LjAxNzg1NzEsMjQgMjYuMzQ1MjM4MSwyMy44NjY2NjY3IDI2LjYwNzE0MjksMjMuNiBDMjYuODY5MDQ3NiwyMy4zMzMzMzMzIDI3LDIzIDI3LDIyLjYgTDI3LDIyLjYgTDI3LDkuNCBDMjcsOSAyNi44NjkwNDc2LDguNjY2NjY2NjcgMjYuNjA3MTQyOSw4LjQgQzI2LjM0NTIzODEsOC4xMzMzMzMzMyAyNi4wMTc4NTcxLDggMjUuNjI1LDggTDI1LjYyNSw4IEw2LjM3NSw4IEM1Ljk4MjE0Mjg2LDggNS42NTQ3NjE5LDguMTMzMzMzMzMgNS4zOTI4NTcxNCw4LjQgQzUuMTMwOTUyMzgsOC42NjY2NjY2NyA1LDkgNSw5LjQgTDUsOS40IEw1LDIyLjYgQzUsMjMgNS4xMzA5NTIzOCwyMy4zMzMzMzMzIDUuMzkyODU3MTQsMjMuNiBDNS42NTQ3NjE5LDIzLjg2NjY2NjcgNS45ODIxNDI4NiwyNCA2LjM3NSwyNCBMNi4zNzUsMjQgTDI1LjYyNSwyNCBaIE0yNS4zMzQ5MzY1LDEzLjI4NTg5ODQgTDYuMzM0OTM2NDksMTMuMjg1ODk4NCBMNi4zMzQ5MzY0OSwxMC43ODU4OTg0IEM2LjQwMDkwODcxLDkuNzg1ODk4MzggNi42MTUzMTg0Myw5LjI4NTg5ODM4IDYuOTc4MTY1NjYsOS4yODU4OTgzOCBMMjQuOTQzMTc5Nyw5LjI5NDcxNDExIEMyNS4yNzY3NzYxLDkuNDczMTI0MzcgMjUuMjY4OTY0Myw5Ljc4NTg5ODM4IDI1LjMzNDkzNjUsMTAuNzg1ODk4NCBMMjUuMzM0OTM2NSwxMy4yODU4OTg0IFogTTI0LjY1NTgwODksMjIuMzQ4MDc2MiBMNi45NDIyNjcyNywyMi4zNDgwNzYyIEM2LjU3OTQyMDA1LDIyLjM0ODA3NjIgNi4zNjUwMTAzMywyMi4xNjA1NzYyIDYuMjk5MDM4MTEsMjEuNzg1NTc2MiBMNi4yOTkwMzgxMSwyMS43ODU1NzYyIEw2LjI5OTAzODExLDE2LjM0ODA3NjIgTDI1LjI5OTAzODEsMTYuMzQ4MDc2MiBMMjUuMjk5MDM4MSwyMS43ODU1NzYyIEMyNS4yMzMwNjU5LDIyLjE2MDU3NjIgMjUuMDE4NjU2MiwyMi4zNDgwNzYyIDI0LjY1NTgwODksMjIuMzQ4MDc2MiBMMjQuNjU1ODA4OSwyMi4zNDgwNzYyIFogTTE3LjI2Nzk0OTIsMTkuMDY5NTQyMyBMMTcuMjY3OTQ5MiwxNy44NTg2NjA5IEw3LjI2Nzk0OTE5LDE3Ljg1ODY2MDkgTDcuMjY3OTQ5MTksMTkuMDY5NTQyMyBMMTcuMjY3OTQ5MiwxOS4wNjk1NDIzIFogTTI0LjI0NTE5MDUsMjAuODE2OTg3MyBMMjQuMjQ1MTkwNSwxNy44MTY5ODczIEwyMS4yNDUxOTA1LDE3LjgxNjk4NzMgTDIxLjI0NTE5MDUsMjAuODE2OTg3MyBMMjQuMjQ1MTkwNSwyMC44MTY5ODczIFogTTExLjkwMTkyMzgsMjEuMTY3NjE4NSBMMTEuOTAxOTIzOCwxOS45NTY3MzcyIEw3LjkwMTkyMzc5LDE5Ljk1NjczNzIgTDcuOTAxOTIzNzksMjEuMTY3NjE4NSBMMTEuOTAxOTIzOCwyMS4xNjc2MTg1IFoiIGlkPSLvhJkiIGZpbGw9IiNGRkZGRkYiIGZpbGwtcnVsZT0ibm9uemVybyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTYuMDAwMDAwLCAxNi4wMDAwMDApIHJvdGF0ZSgzMzAuMDAwMDAwKSB0cmFuc2xhdGUoLTE2LjAwMDAwMCwgLTE2LjAwMDAwMCkgIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==";

    public function __construct($version)
    {
        $this->version = $version;

        add_action('admin_menu', array($this, 'add_menu'), 50);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('checkout-x-admin', plugin_dir_url(CHKX_PLUGIN_FILE) . 'assets/css/admin.css', array(), $this->version, 'all');
    }

    public function admin_settings_link($links)
    {
        $url = esc_url(
            add_query_arg(
                'page',
                'checkout-x-settings',
                get_admin_url() . 'admin.php'
            )
        );

        $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
        array_push(
            $links,
            $settings_link
        );
        return $links;
    }

    public function add_menu()
    {
        add_menu_page(
            'checkout-x',
            'Checkout X',
            'manage_options',
            'checkout-x-settings',
            null,
            self::BASE64_APP_ICON,
            59
        );

        add_submenu_page(
            'checkout-x-settings',
            'Checkout X settings',
            'General',
            'manage_options',
            'checkout-x-settings',
            array($this, 'general_menu_page')
        );

        add_submenu_page(
            'checkout-x-settings',
            'Checkout X help center',
            'Help center',
            'manage_options',
            'checkout-x-help-center',
            array($this, 'help_center_menu_page')
        );
    }

    public function general_menu_page()
    {
        $shop_id = get_option('checkout_x_shop_id');

        if (isset($shop_id)) {
            require_once 'partials/general-settings.php';
        } else {
            require_once 'partials/connect-checkout-x.php';
        }
    }

    public function help_center_menu_page()
    {
        wp_redirect(self::HELP_CENTER_URL);
        exit();
    }

    public function plugin_url()
    {
      return untrailingslashit( plugins_url( '/', CHKX_PLUGIN_FILE ) );
    }

    public function checkoutx_url($additional_path = "")
    {
        $shop_id = get_option('checkout_x_shop_id');

        if (!empty($shop_id)) {
          return esc_url(self::CHECKOUTX_APP_URL . "/shops/" . $shop_id . "/" . $additional_path);
        } else {
          return esc_url(self::CHECKOUTX_WEBSITE_URL);
        }
    }

    public function contact_us_checkoutx_url()
    {
      return esc_url(self::CHECKOUTX_WEBSITE_URL . "/contact_us");
    }
}
