<?php
/**
 * One Page Checkout Compatibility.
 *
 * @since  1.0.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_MNM_OPC_Compatibility {

	public static function init() {

		// OPC support
		add_action( 'wcopc_mix-and-match_add_to_cart', array( __CLASS__, 'opc_single_add_to_cart_mnm' ) );
		add_filter( 'wcopc_allow_cart_item_modification', array( __CLASS__, 'opc_disallow_bundled_cart_item_modification' ), 10, 4 );
	}

	/**
	 * OPC Single-product bundle-type add-to-cart template
	 *
	 * @param  int  $opc_post_id
	 * @return void
	 */
	public static function opc_single_add_to_cart_mnm( $opc_post_id ) {

		global $product;

		// Enqueue script
		wp_enqueue_script( 'wc-add-to-cart-mnm' );
		wp_enqueue_style( 'wc-mnm-styles' );

		if ( $product->is_purchasable() ) {

			ob_start();

			// Load the add to cart template
			wc_get_template(
				'single-product/add-to-cart/mnm.php',
				array(
					'container'	      => $product,
					'container_size'  => $product->get_container_size(),
					'mnm_products'    => $product->get_available_children(),
				),
				'',
				WC_Mix_and_Match()->plugin_path() . '/templates/'
			);

			echo str_replace( array( '<form method="post" enctype="multipart/form-data"', '</form>' ), array( '<div', '</div>' ), ob_get_clean() );
		}
	}

	/**
	 * Prevent OPC from managing bundled items.
	 *
	 * @param  bool   $allow
	 * @param  array  $cart_item
	 * @param  string $cart_item_key
	 * @param  string $opc_id
	 * @return bool
	 */
	public static function opc_disallow_bundled_cart_item_modification( $allow, $cart_item, $cart_item_key, $opc_id ) {

		if ( ! empty( $cart_item[ 'mnm_container' ] ) ) {
			return false;
		}

		return $allow;
	}
}

WC_MNM_OPC_Compatibility::init();
