<?php
/**
 * Points and Rewards Compatibility.
 *
 * @since  1.0.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_MNM_PnR_Compatibility {

	public static function init() {

		// Points earned for per-product priced bundles
		add_filter( 'woocommerce_points_earned_for_cart_item', array( __CLASS__, 'points_earned_for_bundled_cart_item' ), 10, 3 );
		add_filter( 'woocommerce_points_earned_for_order_item', array( __CLASS__, 'points_earned_for_bundled_order_item' ), 10, 5 );

		// Change earn points message for per-product-priced bundles
		add_filter( 'wc_points_rewards_single_product_message', array( __CLASS__, 'points_rewards_bundle_message' ), 10, 2 );
	}

	/**
	 * Return zero points for bundled cart items if container item has product level points.
	 *
	 * @param  int     $points
	 * @param  string  $cart_item_key
	 * @param  array   $cart_item_values
	 * @return int
	 */
	public static function points_earned_for_bundled_cart_item( $points, $cart_item_key, $cart_item_values ) {

		if ( isset( $cart_item_values[ 'mnm_container' ] ) ) {

			$cart_contents = WC()->cart->get_cart();

			$bundle_cart_id = $cart_item_values[ 'mnm_container' ];
			$bundle         = $cart_contents[ $bundle_cart_id ][ 'data' ];

			// check if earned points are set at product-level
			$mnm_points = WC_Points_Rewards_Product::get_product_points( $bundle );

			$per_product_priced_bundle = $bundle->is_priced_per_product();

			$has_mnm_points = is_numeric( $mnm_points ) ? true : false;

			if ( $has_mnm_points || $per_product_priced_bundle == false  ){
				$points = 0;
			} else {
				$points = WC_Points_Rewards_Manager::calculate_points( $cart_item_values[ 'data' ]->get_price() );
			}
		}

		return $points;
	}

	/**
	 * Return zero points for bundled cart items if container item has product level points.
	 *
	 * @param  int        $points
	 * @param  string     $item_key
	 * @param  array      $item
	 * @param  WC_Order   $order
	 * @return int
	 */
	public static function points_earned_for_bundled_order_item( $points, $product, $item_key, $item, $order ) {

		if ( isset( $item[ 'mnm_container' ] ) ) {

			// find container item
			foreach ( $order->get_items() as $order_item ) {

				$is_parent = ( isset( $order_item[ 'mnm_cart_key' ] ) && $item[ 'mnm_container' ] == $order_item[ 'mnm_cart_key' ] ) ? true : false;

				if ( $is_parent ) {

					$parent_item       = $order_item;
					$bundle_product_id = $parent_item[ 'product_id' ];

					// check if earned points are set at product-level
					$mnm_points = get_post_meta( $bundle_product_id, '_wc_points_earned', true );

					$per_product_priced_bundle = isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $bundle_product_id, '_mnm_per_product_pricing', true );

					if ( ! empty( $mnm_points ) || $per_product_priced_bundle !== 'yes' ){
						$points = 0;
					} else {
						$points = WC_Points_Rewards_Manager::calculate_points( $product->get_price() );
					}

					break;
				}
			}
		}

		return $points;
	}

	/**
	 * Points and Rewards single product message for per-product priced Bundles.
	 *
	 * @param  string                    $message
	 * @param  WC_Points_Rewards_Product $points_n_rewards
	 * @return string
	 */
	public static function points_rewards_bundle_message( $message, $points_n_rewards ) {

		global $product;

		if ( $product->product_type === 'mix-and-match' ) {

			if ( ! $product->is_priced_per_product() ){
				return $message;
			}

			// Will calculate points based on min_bundle_price
			$mnm_points = WC_Points_Rewards_Product::get_points_earned_for_product_purchase( $product );

			$message = $points_n_rewards->create_at_least_message_to_product_summary( $mnm_points );

		}

		return $message;
	}

	/**
	 * Filter option_wc_points_rewards_single_product_message in order to force 'WC_Points_Rewards_Product::render_variation_message' to display nothing.
	 *
	 * @param  WC_Bundled_Item  $bundled_item
	 * @return void
	 */
	public static function points_rewards_remove_price_html_messages( $bundled_item ) {
		add_filter( 'option_wc_points_rewards_single_product_message', array( __CLASS__, 'return_empty_message' ) );
	}

	/**
	 * Restore option_wc_points_rewards_single_product_message. Forced in order to force 'WC_Points_Rewards_Product::render_variation_message' to display nothing.
	 *
	 * @param  WC_Bundled_Item  $bundled_item
	 * @return void
	 */
	public static function points_rewards_restore_price_html_messages( $bundled_item ) {
		remove_filter( 'option_wc_points_rewards_single_product_message', array( __CLASS__, 'return_empty_message' ) );
	}

	/**
	 * @see points_rewards_remove_price_html_messages
	 * @param  string  $message
	 * @return void
	 */
	public static function return_empty_message( $message ) {
		return false;
	}
}

WC_MNM_PnR_Compatibility::init();
