<?php
/**
 * Functions for the WooCommerce Mix and Match templating system.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit; 	
}

if ( ! function_exists( 'woocommerce_template_mnm_product_title' ) ) {

	/**
	 * Get the MNM item product title
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_template_mnm_product_title( $mnm_product ) {

		wc_get_template( 
			'single-product/mnm/mnm-product-title.php', 
			array( 
				'mnm_product' => $mnm_product,
			),
			'',
			WC_Mix_and_Match()->plugin_path() . '/templates/'
		);

	}
}

if ( ! function_exists( 'woocommerce_template_mnm_product_thumbnail' ) ) {

	/**
	 * Get the MNM item product thumbnail
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_template_mnm_product_thumbnail( $mnm_product ) {

		wc_get_template( 
			'single-product/mnm/mnm-product-thumbnail.php', 
			array( 'mnm_product' => $mnm_product ),
			'',
			WC_Mix_and_Match()->plugin_path() . '/templates/'
		);

	}
}


if ( ! function_exists( 'woocommerce_template_mnm_product_attributes' ) ) {

	/**
	 * Get the MNM item product's attributes
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_template_mnm_product_attributes( $mnm_product ) {

		if( $mnm_product->is_type( array( 'variation' ) ) ) {
			wc_get_template( 
				'single-product/mnm/mnm-product-' . $mnm_product->product_type . '-attributes.php', 
				array( 'mnm_product' => $mnm_product ),
				'',
				WC_Mix_and_Match()->plugin_path() . '/templates/'
			);

		} 

	}
}

if ( ! function_exists( 'woocommerce_template_mnm_product_quantity' ) ) {

	/**
	 * Get the MNM item product quantity
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_template_mnm_product_quantity( $mnm_product ) {

		wc_get_template( 
			'single-product/mnm/mnm-product-quantity.php', 
			array( 
				'mnm_product' => $mnm_product,
			),
			'',
			WC_Mix_and_Match()->plugin_path() . '/templates/'
		);

	}
}