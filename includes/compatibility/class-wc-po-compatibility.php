<?php
/**
 * Pre Orders Compatibility.
 *
 * @since  1.0.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_MNM_PO_Compatibility {

	public static function init() {

		// Pre-orders support
		add_filter( 'wc_pre_orders_cart_item_meta', array( __CLASS__, 'remove_bundled_pre_orders_cart_item_meta' ), 10, 2 );
		add_filter( 'wc_pre_orders_order_item_meta', array( __CLASS__, 'remove_bundled_pre_orders_order_item_meta' ), 10, 3 );
	}

	/**
	 * Remove bundled cart item meta "Available On" text.
	 *
	 * @param  array  $pre_order_meta
	 * @param  array  $cart_item_data
	 * @return array
	 */
	public static function remove_bundled_pre_orders_cart_item_meta( $pre_order_meta, $cart_item_data ) {

		if ( isset( $cart_item_data[ 'mnm_container' ] ) ) {
			$pre_order_meta = array();
		}

		return $pre_order_meta;
	}

	/**
	 * Remove bundled order item meta "Available On" text.
	 *
	 * @param  array    $pre_order_meta
	 * @param  array    $order_item
	 * @param  WC_Order $order
	 * @return array
	 */
	public static function remove_bundled_pre_orders_order_item_meta( $pre_order_meta, $order_item, $order ) {

		if ( isset( $order_item[ 'mnm_container' ] ) ) {
			$pre_order_meta = array();
		}

		return $pre_order_meta;
	}
}

WC_MNM_PO_Compatibility::init();
