<?php
/**
 * MNM Item Product Quantity
 * @version  1.1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

global $product;

$mnm_id = $mnm_product->variation_id ? $mnm_product->variation_id : $mnm_product->id;

if ( $mnm_product->is_purchasable() && $mnm_product->is_in_stock() ) {

	$quantity = apply_filters( 'woocommerce_mnm_quantity_input', 0, $mnm_product );
	$quantity = isset( $_REQUEST[ 'mnm_quantity' ] ) && isset( $_REQUEST[ 'mnm_quantity' ][ $mnm_id ] ) && ! empty ( $_REQUEST[ 'mnm_quantity' ][ $mnm_id ] ) ? intval( $_REQUEST[ 'mnm_quantity' ][ $mnm_id ] ) : $quantity;

	ob_start();
	woocommerce_quantity_input( array(
		'input_name'  => 'mnm_quantity[' . $mnm_id . ']',
		'input_value' => $quantity,
		'min_value'   => $product->get_child_quantity( 'min', $mnm_id ),
		'max_value'   => $product->get_child_quantity( 'max', $mnm_id )
	) );
	echo str_replace( 'class="quantity"', 'class="quantity mnm-quantity"', ob_get_clean() );

} else {
	echo apply_filters( 'woocommerce_mnm_availability_html', $product->get_child_availability_html( $mnm_id ), $mnm_product );
}
