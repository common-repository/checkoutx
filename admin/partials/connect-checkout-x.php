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
<div class="wrap connection-screen">

  <section class='chkx--admin-section chkx--mb-3'>
    <h1 class='chkx--header'>
      <img class="chkx--header-logo" src="<?php echo esc_url( $this->plugin_url() ); ?>/assets/images/app_icon.png" alt="app_logo" />
      <span>Checkout X</span>
    </h1>
  </section>

  <section class="chkx--install-step">
    <span>1. Install Checkout X plugin on your store</span>

    <img height="26" class='chkx--installed-mark' src="<?php echo esc_url( $this->plugin_url() ); ?>/assets/images/checkmark.png" alt='checkmark'/>
    <span class="chkx--badge chkx--ml-1">Installed</span>
  </section>

  <section class='chkx--connect-step chkx--my-4'>
    <header class='chkx--header'>2. Connect Checkout X with your store</header>

    <section class="chkx--mb-3">
      <p>1. Click the Get started button to create an account in Checkout X</p>
        <a class="button button-primary chkx--button-primary-big" href="<?php echo $this->checkoutx_url(); ?>" target="_blank">Open Checkout X and get started</a>
    </section>

    <section>
      <p class='chkx--w-400'>2. Our intuitive helper will guide you step by step to connect and set up Checkout X.</p>
    </section>
  </section>

  <section>
    <div class="chkx--cards-row chkx--align-center chkx--bg-white chkx--w-800">
      <article class='chkx--promo-card'>
        <img height="40" width="24" src="<?php echo esc_url( $this->plugin_url() ); ?>/assets/images/trending.png" alt='trending_icon'></img>
        <header class='chkx--header'>Boost your conversion rate</header>
        <p>With Checkout X your shoppers can complete a purchase within 25 seconds where the industry average is 66 seconds.</p>
      </article>
      <article class='chkx--promo-card'>
        <img src="<?php echo esc_url( $this->plugin_url() ); ?>/assets/images/hand_icon.png" height="40" alt='hand_icon'></img>
        <header class='chkx--header'>Easy installation</header>
        <p>No coding skills required to connect and set up Checkout X. We’ve create a guide to help you with the set up process.</p>
      </article>
      <article class='chkx--promo-card'>
        <img height="40" src="<?php echo esc_url( $this->plugin_url() ); ?>/assets/images/growth_icon.png" alt='growth_icon'></img>
        <header class="chkx--header">Sell more with Upsells</header>
        <p>3 out of 4 retailers start making more sales automagically, just by leveraging the power post-purchase upsells.</p>
      </article>
    </div>
  </section>
</div>
