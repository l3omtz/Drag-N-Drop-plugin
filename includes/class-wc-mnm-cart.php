<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mix and Match cart functions and filters.
 *
 * @class 	WC_Mix_and_Match_Cart
 * @version 1.0.5
 * @since   1.0.0
 */

class WC_Mix_and_Match_Cart {

	/**
	 * __construct function.
	 *
	 * @return object
	 */
	public function __construct() {

		// Validate mnm add-to-cart
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 10, 6 );

		// validate mnm cart update
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'update_cart_validation' ), 10, 4 );

		// Add mnm configuration data to all mnm items
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );

		// Add mnm items to the cart
		add_action( 'woocommerce_add_to_cart', array( $this, 'add_mnm_items_to_cart' ), 10, 6 );

		// Modify price and shipping details for bundled items
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item_filter' ), 10, 2 );

		// Preserve data in cart
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_data_from_session' ), 10, 3 );

		// Sync quantities of packed items with container quantity
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_quantity_in_cart' ), 10, 2 );
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'update_quantity_in_cart' ) );

		// Put back cart item data to allow re-ordering of container
		add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again' ), 10, 3 );

		// Filter cart widget items
		add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_widget_filter' ), 10, 3 );

		// Filter cart item count
		add_filter( 'woocommerce_cart_contents_count',  array( $this, 'cart_contents_count' ) );

		// Control modification of packed items' quantity
		add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'cart_item_remove_link' ), 10, 2 );

		// change packed item quantity output
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'cart_item_quantity' ), 10, 2 );

		// hide packed item price
		add_filter( 'woocommerce_cart_item_price', array( $this, 'cart_item_price' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'cart_item_subtotal' ), 10, 3 );

		// remove/restore children cart items when parent is removed/restored
		add_action( 'woocommerce_cart_item_removed', array( $this, 'cart_item_removed' ), 10, 2 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'cart_item_restored' ), 10, 2 );

		// Shipping fix - ensure that non-virtual containers/children, which are shipped, have a valid price that can be used for insurance calculations.
		// Additionally, allow bundled item weights to be added to the container weight.
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'mnm_shipping_packages_fix' ), 1, 5 );
	}


	/**
	 * Adds mnm contents to the cart.
	 * @param  string 	$item_cart_key
	 * @param  int 		$product_id
	 * @param  int 		$quantity
	 * @param  int 		$variation_id
	 * @param  array 	$variation
	 * @param  array 	$cart_item_data
	 * @return void
	 */
	function add_mnm_items_to_cart( $item_cart_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

		if ( isset( $cart_item_data[ 'mnm_config' ] ) ) {

			$mnm_cart_item_data = array(
				'mnm_container' => $item_cart_key,
			);

			// Now add all items - yay!
			foreach ( $cart_item_data[ 'mnm_config' ] as $item_id => $mnm_item_data ) {

				$mnm_product_id     = $mnm_item_data[ 'product_id' ];
				$mnm_variation_id   = $mnm_item_data[ 'variation_id' ];
				$mnm_variations     = $mnm_item_data[ 'variation' ];

				$item_quantity      = $mnm_item_data[ 'quantity' ];
				$mnm_quantity       = $item_quantity * $quantity;

				// Allow filtering child cart item data (for example if the parent cart item data array already contains extension-specific configuration info)
				$mnm_cart_item_data = (array) apply_filters( 'woocommerce_mnm_child_cart_item_data', $mnm_cart_item_data, $cart_item_data, $item_id, $product_id );

				// Prepare for adding children to cart
				do_action( 'woocommerce_mnm_before_mnm_add_to_cart', $mnm_product_id, $mnm_quantity, $mnm_variation_id, $mnm_variations, $mnm_cart_item_data );

				// Add to cart
				$mnm_item_cart_key = $this->mnm_add_to_cart( $product_id, $mnm_product_id, $mnm_quantity, $mnm_variation_id, $mnm_variations, $mnm_cart_item_data );

				if ( $mnm_item_cart_key ) {

					if ( ! isset( WC()->cart->cart_contents[ $item_cart_key ][ 'mnm_contents' ] ) ) {

						WC()->cart->cart_contents[ $item_cart_key ][ 'mnm_contents' ] = array();

					} elseif ( ! in_array( $mnm_item_cart_key, WC()->cart->cart_contents[ $item_cart_key ][ 'mnm_contents' ] ) ) {

						WC()->cart->cart_contents[ $item_cart_key ][ 'mnm_contents' ][] = $mnm_item_cart_key;
					}
				}

				// Finish
				do_action( 'woocommerce_mnm_after_mnm_add_to_cart', $mnm_product_id, $mnm_quantity, $mnm_variation_id, $mnm_variations, $mnm_cart_item_data );

			}

		}

	}


	/**
	 * Add a mnm child to the cart. Must be done without updating session data, recalculating totals or calling 'woocommerce_add_to_cart' recursively.
	 * For the recursion issue, see: https://core.trac.wordpress.org/ticket/17817.
	 *
	 * @param int          $container_id
	 * @param int          $product_id
	 * @param string       $quantity
	 * @param int          $variation_id
	 * @param array        $variation
	 * @param array        $cart_item_data
	 * @return string|false
	 */
	public function mnm_add_to_cart( $container_id, $product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_item_data ) {

		// Load cart item data when adding to cart
		$cart_item_data = ( array ) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );

		// Generate a ID based on product ID, variation ID, variation data, and other cart item data
		$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

		// See if this product and its options is already in the cart
		$cart_item_key = WC()->cart->find_product_in_cart( $cart_id );

		// Get the product
		$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

		// If cart_item_key is set, the item is already in the cart and its quantity will be handled by update_quantity_in_cart().
		if ( ! $cart_item_key ) {

			$cart_item_key = $cart_id;

			// Add item after merging with $cart_item_data - allow plugins and wc_cp_add_cart_item_filter to modify cart item
			WC()->cart->cart_contents[ $cart_item_key ] = apply_filters( 'woocommerce_add_cart_item', array_merge( $cart_item_data, array(
				'product_id'   => absint( $product_id ),
				'variation_id' => absint( $variation_id ),
				'variation'    => $variation,
				'quantity'     => $quantity,
				'data'         => $product_data
			) ), $cart_item_key );

		}

		// use this hook for compatibility instead of the 'woocommerce_add_to_cart' action hook to work around the recursion issue
		// when the recursion issue is solved, we can simply replace calls to 'mnm_add_to_cart()' with direct calls to 'WC_Cart::add_to_cart()' and delete this function
		do_action( 'woocommerce_mnm_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data, $container_id );

		return $cart_item_key;
	}


	/**
	 * Validates that all MnM items chosen can be added-to-cart before actually starting to add items.
	 * @param  bool 	$add
	 * @param  int 	$product_id
	 * @param  int 	$product_quantity
	 * @return bool
	 */
	public function add_to_cart_validation( $passed_validation, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		// The container product
		$product = wc_get_product( $product_id );

		// Ordering again?
		$order_again = isset( $_GET[ 'order_again' ] ) && isset( $_GET[ '_wpnonce' ] ) && wp_verify_nonce( $_GET[ '_wpnonce' ], 'woocommerce-order_again' );

		// Prevent bundled items from getting added if container was not added
		if ( $order_again && isset( $cart_item_data[ 'mnm_container' ] ) && $cart_item_data[ 'mnm_container' ] === false ) {
			return false;
		}

		if ( $product->is_type( 'mix-and-match' ) ) {

			// Grab bundled items
			$mnm_items = $product->get_children();

			if ( empty( $mnm_items ) ) {
				return false;
			}

			// Count the total bundled items
			$total_items_in_container = 0;

			// If a stock-managed product / variation exists in the bundle multiple times, its stock will be checked only once for the sum of all bundled quantities.
			// The stock manager class keeps a record of stock-managed product / variation ids
			$mnm_stock = new WC_Mix_and_Match_Stock_Manager( $product );

			// Loop through the items
			foreach ( $mnm_items as $id => $mnm_item ) {

				// Check that a product has been selected
				if ( isset( $_REQUEST[ 'mnm_quantity' ][ $id ] ) && $_REQUEST[ 'mnm_quantity' ][ $id ] !== '' ) {
					$item_quantity = $_REQUEST[ 'mnm_quantity' ][ $id ];
				// for ordering again get the quantity from the config array
				} elseif ( $order_again && isset( $cart_item_data[ 'mnm_config' ][ $id ][ 'quantity' ] ) ) {
					$item_quantity = $cart_item_data[ 'mnm_config' ][ $id ][ 'quantity' ];
				// if the ID isn't in the posted data something is rotten in Denmark
				} else {
					continue;
				}

				// total quantity in single container
				$total_items_in_container += $item_quantity;
				
				// total quantity of items in all containers: for stock purposes
				$quantity = $item_quantity * $product_quantity;

				// product is_purchasable - only for per item pricing
				if ( $product->is_priced_per_product() && ! $mnm_item->is_purchasable() ) {
					wc_add_notice( sprintf( __( 'The configuration you have selected cannot be added to the cart since &quot;%s&quot; cannot be purchased.', 'woocommerce-mix-and-match-products' ), $mnm_item->get_title() ), 'error' );
					return false;
				}

				// stock management
				if ( $mnm_item->is_type( 'variation' ) ) {
					$mnm_stock->add_item( $mnm_item->id, $id, $quantity );
				} else {
					$mnm_stock->add_item( $id, false, $quantity );
				}

				// individual item validation
				if ( ! apply_filters( 'woocommerce_mnm_item_add_to_cart_validation', true, $product, $mnm_item, $item_quantity, $product_quantity ) ) {
					return false;
				}

			} // end foreach


			// the number of items allowed to be in the container
			$limit = intval( get_post_meta( $product_id, '_mnm_container_size', true ) );

			$error_message = false;

			// validate that an unlimited container has some items
			if ( $limit === 0 && $total_items_in_container === 0 ) {
				$error_message = sprintf( __( 'You have not selected any items. Please choose at least 1 item for &quot;%s&quot;.', 'woocommerce-mix-and-match-products' ), $product->get_title() );
			// validate the number of items in the container matches exactly
			} elseif ( $limit > 0 && $total_items_in_container > $limit ) {
				$error_message = sprintf( _n( 'You have selected too many items. Please choose %d item for &quot;%s&quot;.', 'You have selected too many items. Please choose %d items for &quot;%s&quot;.', $limit, 'woocommerce-mix-and-match-products' ), $limit, $product->get_title() );
			} elseif ( $limit > 0 && $total_items_in_container < $limit ) {
				$error_message = sprintf( _n( 'You have selected too few items. Please choose %d item for &quot;%s&quot;.', 'You have selected too few items. Please choose %d items for &quot;%s&quot;.', $limit, 'woocommerce-mix-and-match-products' ), $limit, $product->get_title() );
			}

			// change the quantity error message
			$error_message = apply_filters( 'woocommerce_mnm_container_quantity_error_message', $error_message, $mnm_stock, $product );
			if( $error_message ){
				wc_add_notice( $error_message, 'error' );
				return false;
			}

			// Check stock for stock-managed bundled items, allowing extensions to also validate the container
			// If out of stock, don't proceed
			if ( ! apply_filters( 'woocommerce_mnm_add_to_cart_validation', $mnm_stock->validate_stock(), $mnm_stock, $product ) ) {
				return false;
			}
		}

		return $passed_validation;
	}


	/**
	 * Validates that all MnM items can be updated before updating the container
	 * @param  bool 	$passed_validation
	 * @param  int 		$cart_item_key
	 * @param  array 	$values
	 * @param  int 		$quantity
	 * @return bool
	 */
	public function update_cart_validation( $passed_validation, $cart_item_key, $values, $product_quantity ) {

		$product = $values[ 'data' ];

		if ( ! $product ) {
			return false;
		}

		$existing_quantity   = $values[ 'quantity' ];
		$additional_quantity = $product_quantity - $existing_quantity;

		// don't check bundled items individually, will be checked by parent container
		if ( isset( $values[ 'mnm_container' ] ) && $values[ 'mnm_container' ] != '' ) {
			return $passed_validation;
		}

		if ( $product->is_type( 'mix-and-match' ) && isset( $values[ 'mnm_config' ] ) && is_array( $values[ 'mnm_config' ] ) ) {

				// Grab bundled items
			$mnm_items = $product->get_children();

			if ( empty( $mnm_items ) ) {
				return false;
			}

			// If a stock-managed product / variation exists in the bundle multiple times, its stock will be checked only once for the sum of all bundled quantities.
			// The stock manager class keeps a record of stock-managed product / variation ids
			$mnm_stock = new WC_Mix_and_Match_Stock_Manager( $product );

			// loop through the items
			foreach ( $values[ 'mnm_config' ] as $id => $data ) {

				// double check it is an allowed item - is this needed? wasn't it checked on its way into the cart?
				if ( ! array_key_exists( $id, $mnm_items ) ) {
					return false;
				}

				// quantity per container
				$item_quantity = $data[ 'quantity' ];

				// total quantity
				$quantity = $item_quantity * $additional_quantity;

				// Get the bundled product/variation
				$mnm_item = wc_get_product( $id );

				// must be some kinda fake product
				if ( ! $mnm_item ) {
					return false;
				}

				// stock management
				if ( $mnm_item->is_type( 'variation' ) ) {
					$mnm_stock->add_item( $mnm_item->id, $id, $quantity );
				} else {
					$mnm_stock->add_item( $id, false, $quantity );
				}

			} // end foreach


			// Check stock for stock-managed bundled items
			// If out of stock, don't proceed
			if ( ! $mnm_stock->validate_stock( true ) ) {
				return false;
			}
		}

		return $passed_validation;
	}


	/**
	 * Adds configuration-specific cart-item data
	 * @param  array 	$cart_item_data
	 * @param  int 	$product_id
	 * @return void
	 */
	public function add_cart_item_data( $cart_item_data, $product_id ) {

		// Get product type
		$terms        = get_the_terms( $product_id, 'product_type' );
		$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

		if ( $product_type === 'mix-and-match' && isset( $_REQUEST[ 'mnm_quantity' ] ) && is_array( $_REQUEST[ 'mnm_quantity' ] ) ) {

			// Create a unique array with the mnm configuration
			$config = array();

			foreach ( $_REQUEST[ 'mnm_quantity' ] as $id => $mnm_quantity ) {

				if ( (int) $mnm_quantity <= 0 ) {
					continue;
				}

				$mnm_item = wc_get_product( $id );

				if ( ! $mnm_item ) {
					continue;
				}

				// going to need this info for updating cart and ordering again
				$mnm_product_id   = $mnm_item->id;
				$mnm_variation_id = '';
				$mnm_variation    = '';
				$mnm_variation    = array();

				if ( $mnm_item->is_type( 'variation' ) ) {
					$mnm_product_id   = $mnm_item->id;
					$mnm_variation_id = $mnm_item->variation_id;
					$mnm_variation    = $mnm_item->get_variation_attributes();
				}

				$config[ $id ] = array(
					'product_id'   => $mnm_product_id ,
					'variation_id' => $mnm_variation_id,
					'quantity'     => (int) $mnm_quantity,
					'variation'    => $mnm_variation,
				);

			}

			// add the array to the container item's data
			$cart_item_data[ 'mnm_config' ] = $config;

			// add an empty contents array to the item's data
			$cart_item_data[ 'mnm_contents' ] = array();

		}

		return $cart_item_data;

	}


	/**
	 * Modifies mnm cart item virtual status and price depending on pricing and shipping options.
	 *
	 * @param  array                    $cart_item
	 * @param  WC_Product_Mix_and_Match $parent
	 * @return array
	 */
	private function set_mnm_cart_item( $cart_item, $parent ) {

		$per_product_pricing  = $parent->is_priced_per_product();
		$per_product_shipping = $parent->is_shipped_per_product();

		// if the container has a static price, set its price to zero
		if ( ! $per_product_pricing ) {
			$cart_item[ 'data' ]->price = 0;
		}

		// if is not shipped individually, mark it as virtual and save weight to be optionally added to the container
		if ( $cart_item[ 'data' ]->needs_shipping() ) {

			$item_id = $cart_item[ 'variation_id' ] > 0 ? $cart_item[ 'variation_id' ] : $cart_item[ 'product_id' ];

			if ( false === apply_filters( 'woocommerce_mnm_item_shipped_individually', $per_product_shipping, $cart_item[ 'data' ], $item_id, $parent ) ) {

				if ( apply_filters( 'woocommerce_mnm_item_has_bundled_weight', false, $cart_item[ 'data' ], $item_id, $parent ) ) {
					$cart_item[ 'data' ]->bundled_weight = $cart_item[ 'data' ]->get_weight();
				}

				$cart_item[ 'data' ]->bundled_value = $cart_item[ 'data' ]->price;
				$cart_item[ 'data' ]->virtual       = 'yes';
			}
		}

		return apply_filters( 'woocommerce_mnm_cart_item', $cart_item, $parent );
	}


	/**
	 * Modifies mnm cart item data. Container price is equal to the base price in Per-Item Pricing mode.
	 *
	 * @param array $cart_item
	 */
	private function set_mnm_container_cart_item( $cart_item ) {

		$container = $cart_item[ 'data' ];

		if ( $container->is_priced_per_product() ) {
			$cart_item[ 'data' ]->price         = $container->get_base_price();
			$cart_item[ 'data' ]->sale_price    = $container->get_base_sale_price();
			$cart_item[ 'data' ]->regular_price = $container->get_base_regular_price();
		}

		return apply_filters( 'woocommerce_mnm_container_cart_item', $cart_item, $container );
	}


	/**
	 * Modifies mnm cart item data.
	 * Important for the first calculation of totals only.
	 *
	 * @param  array 	$cart_item
	 * @param  string 	$cart_item_key
	 * @return array
	 */
	public function add_cart_item_filter( $cart_item, $cart_item_key ) {

		$cart_contents = WC()->cart->get_cart();

		// if item is mnm container
		if ( isset( $cart_item[ 'mnm_contents' ] ) ) {
			$cart_item = $this->set_mnm_container_cart_item( $cart_item );
		}

		// if part of mnm container
		if ( ! empty( $cart_item[ 'mnm_container' ] ) ) {

			$container_cart_key = $cart_item[ 'mnm_container' ];

			if ( WC()->cart->find_product_in_cart( $container_cart_key ) ) {

				$parent    = $cart_contents[ $container_cart_key ][ 'data' ];
				$cart_item = $this->set_mnm_cart_item( $cart_item, $parent );

				// Add item key to parent items
				array_push( WC()->cart->cart_contents[ $container_cart_key ][ 'mnm_contents' ], $cart_item_key );
			}
		}

		return $cart_item;
	}


	/**
	 * Load all MnM-related session data.
	 * @param  array 	$cart_item
	 * @param  array 	$item_session_values
	 * @param  string 	$key
	 * @return void
	 */
	public function get_cart_data_from_session( $cart_item, $session_values, $key ) {

		// parent container config
		if ( isset( $session_values[ 'mnm_config' ] ) ) {
			$cart_item[ 'mnm_config' ] = $session_values[ 'mnm_config' ];
		}

		// cart keys of items in parent container
		if ( isset( $session_values[ 'mnm_contents' ] ) ) {

			if ( $cart_item[ 'data' ]->product_type === 'mix-and-match' ) {

				if ( ! isset( $cart_item[ 'mnm_contents' ] ) ) {
					$cart_item[ 'mnm_contents' ] = $item_session_values[ 'mnm_contents' ];
				}

				$cart_item = $this->set_mnm_container_cart_item( $cart_item );

			} else {

				if ( isset( $cart_item[ 'mnm_contents' ] ) ) {
					unset( $cart_item[ 'mnm_contents' ] );
				}
			}
		}

		// bundled items
		if ( isset( $session_values[ 'mnm_container' ] ) ) {

			$container_cart_key = $session_values[ 'mnm_container' ];
			$cart_contents      = WC()->cart->cart_contents;

			if ( WC()->cart->find_product_in_cart( $container_cart_key ) && isset( $cart_contents[ $container_cart_key ][ 'mnm_contents' ] ) ) {

				$cart_item[ 'mnm_container' ] = $container_cart_key;

				$parent        = $cart_contents[ $container_cart_key ][ 'data' ];
				$cart_item     = $this->set_mnm_cart_item( $cart_item, $parent );

			} else {

				if ( isset( $cart_item[ 'mnm_container' ] ) ) {
					unset( $cart_item[ 'mnm_container' ] );
				}
			}
		}

		return $cart_item;
	}


	/**
	 * Keeps MNM item quantities in sync with container item.
	 * @param  string 	$cart_item_key
	 * @param  integer $quantity
	 * @return void
	 */
	public function update_quantity_in_cart( $cart_item_key, $quantity = 0 ) {

		if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {

			$mnm_container = WC()->cart->cart_contents[ $cart_item_key ];

			$mnm_contents = ! empty( $mnm_container[ 'mnm_contents' ] ) ? $mnm_container[ 'mnm_contents' ] : '';

			if ( ! empty( $mnm_contents ) ) {

				$container_quantity = ( $quantity == 0 || $quantity < 0 ) ? 0 : $mnm_container[ 'quantity' ];

				// change the quantity of all MnM items that belong to the same config
				foreach ( $mnm_contents as $mnm_child_key ) {

					$mnm_item = WC()->cart->cart_contents[ $mnm_child_key ];

					if ( ! $mnm_item ) {
						continue;
					}

					if ( $mnm_item[ 'data' ]->is_sold_individually() && $quantity > 0 ) {

						WC()->cart->set_quantity( $mnm_child_key, 1 );

					} else {

						// get quantity per container from parent container config
						$mnm_id = ! empty( $mnm_item[ 'variation_id' ] ) ? $mnm_item[ 'variation_id' ] : $mnm_item[ 'product_id' ];

						$child_qty_per_container = isset( $mnm_container[ 'mnm_config' ][ $mnm_id ][ 'quantity' ] ) ? $mnm_container[ 'mnm_config' ][ $mnm_id ][ 'quantity' ] : 0;

						WC()->cart->set_quantity( $mnm_child_key, $child_qty_per_container * $container_quantity  );

					}
				}
			}
		}
	}


	/**
	 * Reinitialize cart item data for re-ordering purchased orders.
	 * @param  mixed 		$cart_item_data
	 * @param  mixed 		$order_item
	 * @param  WC_Order 	$order
	 * @return mixed
	 */
	public function order_again( $cart_item_data, $order_item, $order ) {

		// add data to container
		if ( isset( $order_item[ 'mnm_config' ] ) && isset( $order_item[ 'mnm_cart_key' ] ) ) {

			$cart_item_data[ 'mnm_config' ]   = maybe_unserialize( $order_item[ 'mnm_config' ] );
			$cart_item_data[ 'mnm_cart_key' ] = $order_item[ 'mnm_cart_key' ];
			$cart_item_data[ 'mnm_contents' ] = array();
		}

		// add data to bundled item - find bundled item's parent
		if ( isset( $order_item[ 'mnm_container' ] ) ) {

			$cart_item_data[ 'mnm_container' ] = $this->get_bundled_cart_item_container_key( $order_item );
		}

		return $cart_item_data;
	}


	/**
	 * Find the parent of a bundled item in an cart.
	 * @param  array    $item
	 * @return array
	 */
	function get_bundled_cart_item_container_key( $item ) {

		// find container item
		foreach ( WC()->cart->get_cart() as $cart_key => $cart_item ) {

			$is_parent = isset( $item[ 'mnm_container' ] ) && isset( $cart_item[ 'mnm_cart_key' ] ) && $item[ 'mnm_container' ] == $cart_item[ 'mnm_cart_key' ] ? true : false;

			if ( $is_parent ) {
				return $cart_key;
			}
		}

		return false;
	}

	/**
	 * Do not show mix and matched items in cart widget.
	 * @param  bool 	$show
	 * @param  array 	$cart_item
	 * @param  string 	$cart_item_key
	 * @return bool
	 */
	public function cart_widget_filter( $show, $cart_item, $cart_item_key ) {

		if ( isset( $cart_item[ 'mnm_container' ] ) ) {
			$show = false;
		}

		return $show;
	}


	/**
	 * Filters the reported number of cart items.
	 * counts only MnM containers
	 * @param  int 	$count
	 * @return int
	 */
	public function cart_contents_count( $count ) {

		$cart_items = WC()->cart->get_cart();
		$subtract 	= 0;

		foreach ( $cart_items as $key => $value ) {

			if ( isset( $value[ 'mnm_container' ] ) ) {
				$subtract += $value[ 'quantity' ];
			}

		}

		return $count - $subtract;
	}


	/**
	 * MnM items can't be removed individually from the cart.
	 * @param  string 	$link
	 * @param  string 	$cart_item_key
	 * @return string
	 */
	public function cart_item_remove_link( $link, $cart_item_key ) {

		if ( isset( WC()->cart->cart_contents[ $cart_item_key ][ 'mnm_container' ] ) && ! empty( WC()->cart->cart_contents[ $cart_item_key ][ 'mnm_container' ] ) ) {
			$link = '';
		}

		return $link;
	}


	/**
	 * Modifies the cart.php formatted quantity for items in the container.
	 * @param  string 	$quantity
	 * @param  string 	$cart_item_key
	 * @return string
	 */
	public function cart_item_quantity( $quantity, $cart_item_key ) {

		$cart = WC()->cart->get_cart();

		if ( isset( $cart[ $cart_item_key ][ 'mnm_container' ] ) && ! empty( $cart[ $cart_item_key ][ 'mnm_container' ] ) ) {

			if ( WC()->cart->find_product_in_cart( $cart[ $cart_item_key ][ 'mnm_container' ] ) ) {
				$quantity = $cart[ $cart_item_key ][ 'quantity' ];
			}
		}

		return $quantity;
	}


	/**
	 * Modifies the cart.php formatted html prices visibility for items in the container.
	 * @param  string 	$price
	 * @param  array 	$cart_item
	 * @param  string 	$cart_item_key
	 * @return string
	 */
	public function cart_item_price( $price, $cart_item, $cart_item_key ) {

		// bundled child items
		if ( isset( $cart_item[ 'mnm_container' ] ) && ! empty( $cart_item[ 'mnm_container' ] ) ) {

			$container_cart_key = $cart_item[ 'mnm_container' ];

			$cart = WC()->cart->get_cart();

			if ( WC()->cart->find_product_in_cart( $container_cart_key ) && WC()->cart->cart_contents[ $container_cart_key ][ 'data' ]->is_priced_per_product() == false && $cart_item[ 'data' ]->price == 0 ) {
				$price = '';
			}
		}

		// parent container
		if ( isset( $cart_item[ 'mnm_contents' ] ) && ! empty( $cart_item[ 'mnm_contents' ] ) ) {

			if ( $cart_item[ 'data' ]->is_priced_per_product() == true ) {

				$mnm_items_price     = 0;
				$mnm_container_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $cart_item[ 'data' ]->get_price_excluding_tax( $cart_item[ 'quantity' ] ) : $cart_item[ 'data' ]->get_price_including_tax( $cart_item[ 'quantity' ] );

				foreach ( $cart_item[ 'mnm_contents' ] as $mnm_item_key ) {

					$item_values = WC()->cart->cart_contents[ $mnm_item_key ];
					$item_id     = isset( $item_values[ 'variation_id' ] ) ? $item_values[ 'variation_id' ] : $item_values[ 'product_id' ];
					$product     = $item_values[ 'data' ];

					$bundled_item_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $product->get_price_excluding_tax( $item_values[ 'quantity' ] ) : $product->get_price_including_tax( $item_values[ 'quantity' ] );
					$mnm_items_price    += $bundled_item_price;

				}

				$price = $mnm_container_price + $mnm_items_price / $cart_item[ 'quantity' ];
				return wc_price( $price );

			}

		}

		return $price;
	}


	/**
	 * Modifies the cart.php template formatted subtotal appearance.
	 * @param  string 	$subtotal
	 * @param  array 	$cart_item
	 * @param  string 	$cart_item_key
	 * @return string
	 */
	public function cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {

		if ( isset( $cart_item[ 'mnm_container' ] ) && ! empty( $cart_item[ 'mnm_container' ] ) ) {

			$container_cart_key = $cart_item[ 'mnm_container' ];

			if ( WC()->cart->find_product_in_cart( $container_cart_key ) && WC()->cart->cart_contents[ $container_cart_key ][ 'data' ]->is_priced_per_product() == false && $cart_item[ 'data' ]->price == 0 ) {
				$subtotal = '';
			} else {
				return __( 'Subtotal', 'woocommerce-mix-and-match-products' ) . ': ' . $subtotal;
			}

		}

		if ( isset( $cart_item[ 'mnm_contents' ] ) && ! empty( $cart_item[ 'mnm_contents' ] ) ) {

			if ( $cart_item[ 'data' ]->is_priced_per_product() == true ) {

				$mnm_items_price     = 0;
				$mnm_container_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $cart_item[ 'data' ]->get_price_excluding_tax( $cart_item[ 'quantity' ] ) : $cart_item[ 'data' ]->get_price_including_tax( $cart_item[ 'quantity' ] );

				foreach ( $cart_item[ 'mnm_contents' ] as $mnm_item_key ) {

					$item_values = WC()->cart->cart_contents[ $mnm_item_key ];
					$item_id     = isset( $item_values[ 'variation_id' ] ) ? $item_values[ 'variation_id' ] : $item_values[ 'product_id' ];
					$product     = $item_values[ 'data' ];

					$bundled_item_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $product->get_price_excluding_tax( $item_values[ 'quantity' ] ) : $product->get_price_including_tax( $item_values[ 'quantity' ] );
					$mnm_items_price    += $bundled_item_price;

				}

				$subtotal = $mnm_container_price + $mnm_items_price;

				return $this->format_product_subtotal( $cart_item[ 'data' ], $subtotal );
			}

		}

		return $subtotal;
	}

	/**
	 * Outputs a formatted subtotal ( @see cart_item_subtotal() ).
	 * @static
	 * @param  obj    $product  the WC_Product
	 * @param  string $subtotal formatted subtotal
	 * @return string modified  formatted subtotal
	 */
	public static function format_product_subtotal( $product, $subtotal ) {

		$cart = WC()->cart;

		$taxable = $product->is_taxable();

		// Taxable
		if ( $taxable ) {

			if ( $cart->tax_display_cart == 'excl' ) {

				$product_subtotal = wc_price( $subtotal );

				if ( $cart->prices_include_tax && $cart->tax_total > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}

			} else {

				$product_subtotal = wc_price( $subtotal );

				if ( ! $cart->prices_include_tax && $cart->tax_total > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			}

		// Non-taxable
		} else {

			$product_subtotal = wc_price( $subtotal );
		}

		return $product_subtotal;
	}


	/**
	 * Remove bundled cart items with parent.
	 *
	 * @param  string  $cart_item_key
	 * @param  WC_Cart $cart
	 * @return void
	 */
	function cart_item_removed( $cart_item_key, $cart ) {

		if ( ! empty( $cart->removed_cart_contents[ $cart_item_key ][ 'mnm_contents' ] ) ) {

			$mnm_item_cart_keys = $cart->removed_cart_contents[ $cart_item_key ][ 'mnm_contents' ];

			foreach ( $mnm_item_cart_keys as $mnm_item_cart_key ) {

				if ( ! empty( $cart->cart_contents[ $mnm_item_cart_key ] ) ) {
					$remove = $cart->cart_contents[ $mnm_item_cart_key ];
					$cart->removed_cart_contents[ $mnm_item_cart_key ] = $remove;
					unset( $cart->cart_contents[ $mnm_item_cart_key ] );
					do_action( 'woocommerce_cart_item_removed', $mnm_item_cart_key, $cart );
				}
			}
		}
	}


	/**
	 * Restore bundled cart items with parent.
	 *
	 * @param  string  $cart_item_key
	 * @param  WC_Cart $cart
	 * @return void
	 */
	function cart_item_restored( $cart_item_key, $cart ) {

		if ( ! empty( $cart->cart_contents[ $cart_item_key ][ 'mnm_contents' ] ) ) {

			$mnm_item_cart_keys = $cart->cart_contents[ $cart_item_key ][ 'mnm_contents' ];

			foreach ( $mnm_item_cart_keys as $mnm_item_cart_key ) {

				if ( ! empty( $cart->removed_cart_contents[ $mnm_item_cart_key ] ) ) {
					$remove = $cart->removed_cart_contents[ $mnm_item_cart_key ];
					$cart->cart_contents[ $mnm_item_cart_key ] = $remove;
					unset( $cart->removed_cart_contents[ $mnm_item_cart_key ] );
					do_action( 'woocommerce_cart_item_restored', $mnm_item_cart_key, $cart );
				}
			}
		}
	}


	/**
	 * Shipping fix - ensure that non-virtual containers/children, which are shipped, have a valid price that can be used for insurance calculations.
	 * Additionally, allow bundled item weights to be added to the container weight.
	 *
	 * Note: If the container has a static price but the contents are shipped individually, the only working solution is to spread the total value among the bundled items.
	 *
	 * @param  array  $packages
	 * @return array
	 */
	function mnm_shipping_packages_fix( $packages ) {

		if ( ! empty( $packages ) ) {

			foreach ( $packages as $package_key => $package ) {

				if ( ! empty( $package[ 'contents' ] ) ) {

					foreach ( $package[ 'contents' ] as $cart_item => $cart_item_data ) {

						if ( isset( $cart_item_data[ 'mnm_contents' ] ) ) {

							$bundle     = clone $cart_item_data[ 'data' ];
							$bundle_qty = $cart_item_data[ 'quantity' ];

							// Physical container (bundled shipping):
							// - if the container is priced per-item, sum the prices of the children into the parent
							// - optionally, append the weight of the children into the parent

							if ( ! $bundle->is_shipped_per_product() ) {

								$bundled_value  = $bundle->get_price();
								$bundled_weight = 0;

								foreach ( $cart_item_data[ 'mnm_contents' ] as $child_item_key ) {

									if ( isset( $package[ 'contents' ][ $child_item_key ] ) ) {

										$bundled_product     = clone $package[ 'contents' ][ $child_item_key ][ 'data' ];
										$bundled_product_qty = $package[ 'contents' ][ $child_item_key ][ 'quantity' ];

										if ( isset( $bundled_product->bundled_value ) ) {
											$bundled_value += $bundled_product->bundled_value * $bundled_product_qty;
											$bundled_product->price  = 0;
											$bundled_product->weight = 0;
											$packages[ $package_key ][ 'contents' ][ $child_item_key ][ 'data' ] = $bundled_product;
										}

										if ( isset( $bundled_product->bundled_weight ) ) {
											$bundled_weight += $bundled_product->bundled_weight * $bundled_product_qty;
										}
									}
								}

								$bundle->price  = $bundled_value / $bundle_qty;
								$bundle->weight += $bundled_weight / $bundle_qty;

								if ( isset( $bundle->bundled_weight ) ) {
									$bundle->bundled_weight += $bundled_weight / $bundle_qty;
								}

								if ( isset( $bundle->bundled_value ) ) {
									$bundle->bundled_value += $bundled_value / $bundle_qty;
								}

								$packages[ $package_key ][ 'contents' ][ $cart_item ][ 'data' ] = $bundle;

							// Virtual container (non-bundled shipping enabled) that is priced statically:
							// Distribute the price of the parent uniformly among the children

							} elseif ( $bundle->is_shipped_per_product() && ! $bundle->is_priced_per_product() ) {

								$total_value   = $bundle->get_price() * $bundle_qty;
								$child_count   = 0;
								$bundled_items = array();

								foreach ( $cart_item_data[ 'mnm_contents' ] as $child_item_key ) {

									if ( isset( $package[ 'contents' ][ $child_item_key ] ) ) {

										$bundled_product     = $package[ 'contents' ][ $child_item_key ][ 'data' ];
										$bundled_product_qty = $package[ 'contents' ][ $child_item_key ][ 'quantity' ];

										if ( $bundled_product->needs_shipping() ) {
											$child_count += $bundled_product_qty;
											$total_value += $bundled_product->get_price() * $bundled_product_qty;
											$bundled_items[] = $child_item_key;
										}
									}
								}

								foreach ( $bundled_items as $child_item_key ) {

									$bundled_product        = clone $package[ 'contents' ][ $child_item_key ][ 'data' ];
									$bundled_product->price = round( $total_value / $child_count, wc_get_price_decimals() );

									$packages[ $package_key ][ 'contents' ][ $child_item_key ][ 'data' ] = $bundled_product;
								}

								$bundle->price = 0;
								$packages[ $package_key ][ 'contents' ][ $cart_item ][ 'data' ] = $bundle;
							}
						}
					}
				}
			}
		}

		return $packages;
	}

} //end class
