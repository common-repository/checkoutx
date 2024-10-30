(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

  // exported to window object and called by storefront script
	var checkoutXPlugin = {
	  // calls method on backend to empty cart and when server responds - trigger 
	  // wc_fragment_refresh event to update UI
		clearWoocommerceCart :function(){
			var data = {
				'action': 'chkx_clear_cart'
			};
			var url = checkout_x_data.admin_ajax_url;

			var promise = jQuery.ajax({
				url: url,
				type: 'POST',
				data: data,
			});
			promise.then(function() {
			  $(document.body).trigger('wc_fragment_refresh');
			});
			return promise;
		}
	};

  // observe changes on #checkout-x-content element. If it gets updated by
  // wc_ajax refreshed_cart_fragments - sync cart with Checkout X
  // TODO: move to storefront script itself
	$(function() {
	  var observer = new MutationObserver(function() {
	    CHKX.cartApi.syncCart();
	  });

	  var checkoutXContent = document.getElementById('checkout-x-content');
	  if (checkoutXContent) {
	    observer.observe(checkoutXContent,{
	      childList: true,
	    });
	  }
	})
	window.CheckoutXPlugin = checkoutXPlugin;
})( jQuery );
