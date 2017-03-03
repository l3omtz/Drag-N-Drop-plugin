<?php
/**
 * WooCommerce Mix and Match Products Template Hooks
 *
 * Action/filter hooks used for WooCommerce Mix and Match Products functions/templates
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit; 	
}

/**
 * MnM Item Row
 *
 * @see woocommerce_template_mnm_product_thumbnail()
 * @see woocommerce_template_mnm_product_title()
 * @see woocommerce_template_mnm_product_attributes()
 * @see woocommerce_template_mnm_product_quantity()
 */
add_action( 'woocommerce_mnm_row_item_thumbnail', 'woocommerce_template_mnm_product_thumbnail' );
add_action( 'woocommerce_mnm_row_item_description', 'woocommerce_template_mnm_product_title' );
add_action( 'woocommerce_mnm_row_item_description', 'woocommerce_template_mnm_product_attributes', 20 );
add_action( 'woocommerce_mnm_row_item_quantity', 'woocommerce_template_mnm_product_quantity' );
