<?php
/**
 * Mix and Match order functions and filters.
 *
 * @class 	WC_Mix_and_Match_Order
 * @version 1.0.5
 * @since   1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Mix_and_Match_Order {

	/**
	 * Setup order class
	 */
	public function __construct() {

		// Filter price output shown in cart, review-order & order-details templates
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'order_item_subtotal' ), 10, 3 );

		// Bundle containers should not affect order status
		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'container_items_need_no_processing' ), 10, 3 );

		// Modify order items to include bundle meta
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );

		// Hide bundle configuration metadata in order line items
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_item_meta' ) );

		// Filter order item count
		add_filter( 'woocommerce_get_item_count',  array( $this, 'order_item_count' ), 10, 3 );
		add_filter( 'woocommerce_admin_order_item_count',  array( $this, 'order_item_count_string' ), 10, 2 );
		add_filter( 'woocommerce_admin_html_order_item_class',  array( $this, 'html_order_item_class' ), 10, 2 );
		add_filter( 'woocommerce_admin_order_item_class',  array( $this, 'html_order_item_class' ), 10, 2 );

		/**
		 * Order API Modifications
		 */

		add_filter( 'woocommerce_get_product_from_item', array( $this, 'get_product_from_item' ), 10, 3 );
	}


	/**
	 * Find the parent of a bundled item in an order.
	 *
	 * @param  	array    $item
	 * @param  	WC_Order $order
	 * @return array
	 */
	public function get_bundled_order_item_container( $item, $order ) {

		// find container item
		foreach ( $order->get_items() as $order_item ) {

			$is_parent = isset( $item[ 'mnm_container' ] ) && isset( $order_item[ 'mnm_cart_key' ] ) && $item[ 'mnm_container' ] === $order_item[ 'mnm_cart_key' ];

			if ( $is_parent ) {

				$parent_item = $order_item;

				return $parent_item;
			}
		}

		return false;
	}


	/**
	 * Modify the subtotal of order-items (order-details.php) depending on the bundles's pricing strategy.
	 *
	 * @param  string   $subtotal   the item subtotal
	 * @param  array    $item       the items
	 * @param  WC_Order $order      the order
	 * @return string               modified subtotal string.
	 */
	public function order_item_subtotal( $subtotal, $item, $order ) {

		// If it's a bundled item
		if ( isset( $item[ 'mnm_container' ] ) ) {

			// find bundle parent
			$parent_item = $this->get_bundled_order_item_container( $item, $order );

			$per_product_pricing = ! empty( $parent_item ) && isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $parent_item[ 'product_id' ] , '_mnm_per_product_pricing', true );

			if ( $per_product_pricing == 'no' ) {
				return '';
			} else {
				return  '<small>' . __( 'Item subtotal', 'woocommerce-mix-and-match-products' ) . ': ' . $subtotal . '</small>';
			}
		}

		// If it's a container
		if ( isset( $item[ 'mnm_config' ] ) ) {

			if ( isset( $item[ 'subtotal_updated' ] ) ) {
				return $subtotal;
			}

			$sub_item_id	= '';

			foreach ( $order->get_items() as $order_item_id => $order_item ) {

				$is_child = isset( $order_item[ 'mnm_container' ] ) && $order_item[ 'mnm_container' ] == $item[ 'mnm_cart_key' ] ? true : false;

				if ( $is_child ) {

					$item[ 'line_subtotal' ] 		+= $order_item[ 'line_subtotal' ];
					$item[ 'line_subtotal_tax' ] 	+= $order_item[ 'line_subtotal_tax' ];

				}
			}

			$item[ 'subtotal_updated' ] = 'yes';

			// modified bundle line subtotal
			return $order->get_formatted_line_subtotal( $item );

		}

		return $subtotal;
	}


	/**
	 * Filters the reported number of order items.
	 * Do not count bundled items.
	 *
	 * @param  int          $count      initial reported count
	 * @param  string       $type       line item type
	 * @param  WC_Order     $order      the order
	 * @return int                      modified count
	 */
	public function order_item_count( $count, $type, $order ) {

		$subtract = 0;

		foreach ( $order->get_items() as $item ) {

			// If it's a bundled item
			if ( isset( $item[ 'mnm_container' ] ) ) {
				$subtract += $item[ 'qty' ];
			}
		}

		$new_count = $count - $subtract;

		return $new_count;

	}


	/**
	 * Filters the string of order item count.
	 * Include bundled items as a suffix.
	 *
	 * @see 	order_item_count
	 * @param  int          $count      initial reported count
	 * @param  WC_Order     $order      the order
	 * @return int                      modified count
	 */
	public function order_item_count_string( $count, $order ) {

		$add = 0;

		foreach ( $order->get_items() as $item ) {

			// If it's a bundled item
			if ( isset( $item[ 'mnm_container' ] ) ) {
				$add += $item[ 'qty' ];
			}
		}

		if ( $add > 0 ) {
			return sprintf( __( '%1$s, %2$s mixed', 'woocommerce-mix-and-match-products' ), $count, $add );
		}

		return $count;

	}


	/**
	 * Filters the order item admin class.
	 *
	 * @param  string       $class     class
	 * @param  array        $item      the order item
	 * @return string                  modified class
	 */
	public function html_order_item_class( $class, $item ) {

		// if it is a mnm container
		if ( isset( $item[ 'mnm_config' ] ) && ! empty( $item[ 'mnm_config' ] ) ) {
			$class .= ' mnm_table_container';
		}

		// If it's a bundled item
		if ( isset( $item[ 'mnm_container' ] ) && ! empty( $item[ 'mnm_container' ] ) ) {
			$class .= ' mnm_table_item';
		}

		return $class;

	}


	/**
	 * Bundle Containers need no processing - let it be decided by bundled items only.
	 *
	 * @param  boolean      $is_needed   product needs processing: true/false
	 * @param  WC_Product   $product     the product
	 * @param  int          $order_id    the order id
	 * @return boolean                   modified product needs processing status
	 */
	public function container_items_need_no_processing( $is_needed, $product, $order_id ) {

		if ( $product->is_type( 'mix-and-match' ) ) {
			return false;
		}

		return $is_needed;
	}


	/**
	 * Hides bundle metadata.
	 *
	 * @param  array    $hidden     hidden meta strings
	 * @return array                modified hidden meta strings
	 */
	public function hidden_order_item_meta( $hidden ) {
		return array_merge( $hidden, array( '_mnm_config', '_mnm_container', '_per_product_pricing', '_per_product_shipping', '_mnm_cart_key', '_bundled_shipping', '_bundled_weight' ) );
	}


	/**
	 * Add bundle info meta to order items.
	 *
	 * @param  int      $order_item_id      order item id
	 * @param  array    $cart_item_values   cart item data
	 * @return void
	 */
	public function add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key ) {

		// add data to the bundled items
		if ( isset( $cart_item_values[ 'mnm_container' ] ) ) {

			wc_add_order_item_meta( $order_item_id, '_mnm_container', $cart_item_values[ 'mnm_container' ] );

			$product_key = WC()->cart->find_product_in_cart( $cart_item_values[ 'mnm_container' ] );

			if ( ! empty( $product_key ) ) {

				$product_name = WC()->cart->cart_contents[ $product_key ][ 'data' ]->get_title();

				wc_add_order_item_meta( $order_item_id, __( 'Part of', 'woocommerce-mix-and-match-products' ), $product_name );
			}

			do_action( 'woocommerce_mnm_item_add_order_item_meta', $cart_item_values, $cart_item_key, $order_item_id );

		}

		// add data to the container item
		if ( isset( $cart_item_values[ 'mnm_config' ] ) && ! isset( $cart_item_values[ 'mnm_container' ] ) ) {

			$container_size = $cart_item_values[ 'data' ]->get_container_size();
			$container_size = apply_filters( 'woocommerce_mnm_order_item_container_size_meta_value', $container_size === 0 ? __( 'Unlimited', 'woocommerce-mix-and-match-products' ) : $container_size, $order_item_id, $cart_item_values, $cart_item_key );

			wc_add_order_item_meta( $order_item_id, __( 'Container size', 'woocommerce-mix-and-match-products' ), $container_size );

			wc_add_order_item_meta( $order_item_id, '_mnm_config', $cart_item_values[ 'mnm_config' ] );

			wc_add_order_item_meta( $order_item_id, '_mnm_cart_key', $cart_item_key );

			$per_product_pricing = $cart_item_values[ 'data' ]->is_priced_per_product() ? 'yes' : 'no';
			wc_add_order_item_meta( $order_item_id, '_per_product_pricing', $per_product_pricing );

			$per_product_shipping = $cart_item_values[ 'data' ]->is_shipped_per_product() ? 'yes' : 'no';
			wc_add_order_item_meta( $order_item_id, '_per_product_shipping', $per_product_shipping );

			do_action( 'woocommerce_mnm_container_add_order_item_meta', $cart_item_values, $cart_item_key, $order_item_id );

		}

		// Store shipping data - useful when exporting order content
		if ( isset( $cart_item_values[ 'mnm_contents' ] ) || isset( $cart_item_values[ 'mnm_container' ] ) ) {

			foreach ( WC()->cart->get_shipping_packages() as $package ) {

				foreach ( $package[ 'contents' ] as $pkg_item_id => $pkg_item_values ) {

					if ( $pkg_item_id === $cart_item_key ) {

						$bundled_shipping = $pkg_item_values[ 'data' ]->needs_shipping() ? 'yes' : 'no';
						$bundled_weight   = $pkg_item_values[ 'data' ]->get_weight();

						wc_add_order_item_meta( $order_item_id, '_bundled_shipping', $bundled_shipping );

						if ( $bundled_shipping === 'yes' ) {
							wc_add_order_item_meta( $order_item_id, '_bundled_weight', $bundled_weight );
						}
					}
				}
			}
		}
	}

	/* -------------------------- */
	/* Order API Modifications
	/* -------------------------- */

	/**
	 * Restore price, virtual status and weights/dimensions of bundle containers/children depending on the "per-item pricing" and "per-item shipping" settings.
	 * Virtual containers/children are assigned a zero weight and tiny dimensions in order to maintain the value of the associated item in shipments (for instance, when a bundle has a static price but is shipped per item).
	 *
	 * @param  WC_Product $product
	 * @param  array      $item
	 * @param  WC_Order   $order
	 * @return WC_Product
	 */
	public function get_product_from_item( $product, $item, $order ) {

		if ( apply_filters( 'woocommerce_mnm_filter_product_from_item', false, $order ) ) {

			// Restore base price.
			if ( ! empty( $product ) && $product->product_type === 'mix-and-match' && isset( $item[ 'mnm_config' ] ) && isset( $item[ 'mnm_cart_key' ] ) && isset( $item[ 'per_product_pricing' ] ) && $item[ 'per_product_pricing' ] === 'yes' ) {
				$product->price         = $product->get_base_price();
				$product->regular_price = $product->get_base_regular_price();
				$product->sale_price    = $product->get_base_sale_price();
			}

			// Modify shipping properties.
			if ( ( isset( $item[ 'mnm_config' ] ) || isset( $item[ 'mnm_container' ] ) ) && isset( $item[ 'bundled_shipping' ] ) ) {
				if ( $item[ 'bundled_shipping' ] === 'yes' ) {
					if ( isset( $item[ 'bundled_weight' ] ) ) {
						$product->weight = $item[ 'bundled_weight' ];
					}
				} else {

					// Virtual container converted to non-virtual with zero weight and tiny dimensions if it has non-virtual bundled children.
					if ( isset( $item[ 'mnm_config' ] ) && isset( $item[ 'mnm_cart_key' ] ) ) {

						$bundle_key               = $item[ 'mnm_cart_key' ];
						$non_virtual_child_exists = false;

						foreach ( $order->get_items( 'line_item' ) as $child_item_id => $child_item ) {
							if ( isset( $child_item[ 'mnm_container' ] ) && $child_item[ 'mnm_container' ] === $bundle_key && isset( $child_item[ 'bundled_shipping' ] ) && $child_item[ 'bundled_shipping' ] === 'yes' ) {
								$non_virtual_child_exists = true;
								break;
							}
						}

						if ( $non_virtual_child_exists ) {
							$product->virtual = 'no';
						}
					}

					$product->weight = 0;
					$product->length = $product->height = $product->width = 0.001;
				}
			}
		}

		return $product;
	}
}
