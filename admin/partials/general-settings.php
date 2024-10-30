<?php
/**
* Provide a admin area view for the plugin
*
* This file is used to markup the admin-facing aspects of the plugin.
*
* @link       checkout-x.com
* @since      1.0.10
*
* @package    Checkout_X
* @subpackage Checkout_X/admin/partials
*/
?>

<div class="wrap general-settings">
  <section class='chkx--admin-section chkx--mb-3'>
    <h1 class='chkx--header'>
      <img class="chkx--header-logo" src="<?php echo esc_url( $this->plugin_url() ); ?>/assets/images/app_icon.png" alt="app_logo" />
      <span>Checkout X</span>
    </h1>
  </section>

  <section>
    <header class="chkx--header">Checkout X Settings</header>
    <span class='chkx--badge chkx--ml-3'>Connected</span>

    <p class='chkx--w-400'>
      Checkout X uses it own shipping and tax methods. To work properly we recommend you to do all changes in Checkout X dashboard
    </p>
  </section>

  <section class="chkx--my-4">
    <header class='chkx--header chkx-mt-5px'>Plugin version: <?php echo $this->version; ?></header>
    <a href="/wp-admin/plugins.php" class="button button-secondary chkx--ml-3">Update plugin</a>
  </section>

  <a class='button button-primary chkx--button-primary-big' href="<?php echo esc_url($this->checkoutx_url()); ?>" target="_blank">Open Checkout X</a>

  <section class='chkx--admin-section chkx--my-4'>
    <header class="chkx--header chkx--mb-3">Boost your Average order value</header>

    <div class="chkx--cards-row">
      <article class="chkx--card chkx--bg-white">
        <header class='chkx--header'><span class="dashicons dashicons-arrow-up-alt"></span>Post Purchase Upsells</header>
        <p>Create a one click buy post purchase upsells. After your customer completes the checkout a sequence of upsells will be shown to him. Increase your AOV now.</p>
        <hr class="solid"/>
        <a href="<?php echo $this->checkoutx_url('upsells'); ?>" target="_blank" class="button">Configure</a>
      </article>

      <article class="chkx--card chkx--bg-white">
        <header class='chkx--header'><span class="dashicons dashicons-tag"></span>Automatic discounts</header>
        <p>Offer your customers discounts that apply automatically at checkout and on cart. You can create percentage, fixed amount, or buy X get Y automatic discounts.</p>
        <hr class="solid"/>
        <a href="<?php echo $this->checkoutx_url('automatic_discounts'); ?>" target="_blank" class="button">Create a discount</a>
      </article>

      <article class="chkx--card chkx--bg-white">
        <header class='chkx--header'><span class="dashicons dashicons-admin-links"></span>Buy link</header>
        <p>With just one click your shoppers can access a special promotion made for them. Use the Buy links in your marketing campaigns or across different sales channels.</p>
        <hr class="solid"/>
        <a href="<?php echo $this->checkoutx_url('buylinks'); ?>" target="_blank" class="button">Create a buy link</a>
      </article>
      <article class="chkx--card chkx--bg-white">
        <header class='chkx--header'><span class="dashicons dashicons-flag"></span>Request a tool</header>
        <p>You can request a tool by writting to our support team.</p>
        <hr class="solid"/>
        <a href="<?php echo $this->contact_us_checkoutx_url(); ?>" target="_blank" class="button">Contact our support</a>
      </article>
    </div>
  </section>
</div>
