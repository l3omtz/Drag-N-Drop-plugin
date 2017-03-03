<?php
/**
 * Cost of Goods Compatibility.
 *
 * @since  1.0.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_MNM_COG_Compatibility {

	public static function init() {

		// Cost of Goods support
		add_filter( 'wc_cost_of_goods_save_checkout_order_item_meta_item_cost', array( __CLASS__, 'cost_of_goods_checkout_order_bundled_item_cost' ), 10, 3 );
		add_filter( 'wc_cost_of_goods_save_checkout_order_meta_item_cost', array( __CLASS__, 'cost_of_goods_checkout_order_bundled_item_cost' ), 10, 3 );
		add_filter( 'wc_cost_of_goods_set_order_item_cost_meta_item_cost', array( __CLASS__, 'cost_of_goods_set_order_item_bundled_item_cost' ), 10, 3 );
	}

	/**
	 * Cost of goods compatibility: Zero order item cost for bundled products that belong to statically priced bundles.
	 *
	 * @param  double $cost
	 * @param  array  $values
	 * @param  string $cart_item_key
	 * @return double
	 */
	public static function cost_of_goods_checkout_order_bundled_item_cost( $cost, $values, $cart_item_key ) {

		if ( ! empty( $values[ 'bundled_by' ] ) ) {

			$cart_contents   = WC()->cart->get_cart();
			$bundle_cart_key = $values[ 'bundled_by' ];

			if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
				if ( ! $cart_contents[ $bundle_cart_key ][ 'data' ]->is_priced_per_product() ) {
					return 0;
				}
			}

		} elseif ( ! empty( $values[ 'bundled_items' ] ) ) {
			if ( $values[ 'data' ]->is_priced_per_product() ) {
				return 0;
			}
		}

		return $cost;
	}

	/**
	 * Cost of goods compatibility: Zero order item cost for bundled products that belong to statically priced bundles.
	 *
	 * @param  double   $cost
	 * @param  array    $item
	 * @param  WC_Order $order
	 * @return double
	 */
	public static function cost_of_goods_set_order_item_bundled_item_cost( $cost, $item, $order ) {

		if ( ! empty( $item[ 'bundled_by' ] ) ) {

			// find bundle parent
			$parent_item = WC_PB()->order->get_bundled_order_item_container( $item, $order );

			$per_product_pricing = ! empty( $parent_item ) && isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $parent_item[ 'product_id' ], '_per_product_pricing_active', true );

			if ( $per_product_pricing === 'no' ) {
				return 0;
			}

		} elseif ( ! isset( $item[ 'bundled_by' ] ) && isset( $item[ 'stamp' ] ) ) {

			$per_product_pricing = isset( $item[ 'per_product_pricing' ] ) ? $item[ 'per_product_pricing' ] : get_post_meta( $item[ 'product_id' ], '_per_product_pricing_active', true );

			if ( $per_product_pricing === 'yes' ) {
				return 0;
			}
		}

		return $cost;
	}
}

WC_MNM_COG_Compatibility::init();
