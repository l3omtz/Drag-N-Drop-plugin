<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

/**
 * Mix and Match order helper functions
 *
 * @class 	WC_Mix_and_Match_Helpers
 * @version 1.0.5
 * @since   1.0.0
 */

class WC_Mix_and_Match_Helpers {

	/**
	 * Calculates bundled product prices incl. or excl. tax depending on the 'woocommerce_tax_display_shop' setting.
	 *
	 * @param  WC_Product   $product    the product
	 * @param  double       $price      the product price
	 * @return double                   modified product price incl. or excl. tax
	 */
	public static function get_product_display_price( $product, $price ) {

		if ( $price == 0 ){
			return $price;
		}

		if ( get_option( 'woocommerce_tax_display_shop' ) == 'excl' ){
			$product_price = $product->get_price_excluding_tax( 1, $price );
		} else {
			$product_price = $product->get_price_including_tax( 1, $price );
		}

		return $product_price;
	}

	/**
	 * Get formatted variation data with WC < 2.4 back compat and proper formatting of text-based attribute names.
	 *
	 * @param  WC_Product_Variation  $variation   the variation
	 * @return string                             formatted attributes
	 */
	public static function get_formatted_variation_attributes( $variation, $flat = false ) {

		$variation_data = $variation->get_variation_attributes();
		$attributes     = $variation->parent->get_attributes();
		$description    = array();
		$return         = '';

		if ( ! $flat ) {
			$return = '<dl class="variation">';
		}

		foreach ( $attributes as $attribute ) {

			// Only deal with attributes that are variations
			if ( ! $attribute[ 'is_variation' ] ) {
				continue;
			}

			// Get current value for variation (if set)
			$variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute[ 'name' ] ) ] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute[ 'name' ] ) ] : '';

			$description_name         = esc_html( wc_attribute_label( $attribute[ 'name' ] ) );
			$description_value        = __( 'Any', 'woocommerce-mix-and-match-products' );

			// Get terms for attribute taxonomy or value if its a custom attribute
			if ( $attribute[ 'is_taxonomy' ] ) {

				$post_terms = wp_get_post_terms( $variation->id, $attribute[ 'name' ] );

				foreach ( $post_terms as $term ) {

					if ( $variation_selected_value === $term->slug ) {
						$description_value = apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) );
					}
				}

			} else {

				$options = function_exists( 'wc_get_text_attributes' ) ? wc_get_text_attributes( $attribute[ 'value' ] ) : array_map( 'trim', explode( WC_DELIMITER, $attribute[ 'value' ] ) );

				foreach ( $options as $option ) {

					if ( sanitize_title( $variation_selected_value ) === $variation_selected_value ) {
						if ( $variation_selected_value !== sanitize_title( $option ) ) {
							continue;
						}
					} else {
						if ( $variation_selected_value !== $option ) {
							continue;
						}
					}

					$description_value = esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) );
				}
			}

			if ( $flat ) {
				$description[] = $description_name . ': ' . rawurldecode( $description_value );
			} else {
				$description[] = '<dt>' . $description_name . ':</dt><dd>' . rawurldecode( $description_value ) . '</dd>';
			}
		}

		if ( $flat ) {
			$return .= implode( ', ', $description );
		} else {
			$return .= implode( '', $description );
		}

		if ( ! $flat ) {
			$return .= '</dl>';
		}

		return $return;
	}

/**
	 * Product types supported by the plugin.
	 * You can dynamically attach these product types to Mix and Match Product.
	 *
	 * @public
	 * @static
	 * @since  1.1.6
	 * @return array
	 */
	public static function get_supported_product_types() {
		return apply_filters( 'woocommerce_mnm_supported_products', array( 'simple', 'variation' ) );
	}

	/**
	 * Helper method to get the version of the currently installed WooCommerce
	 *
	 * @since 1.0.5
	 * @return string woocommerce version number or null
	 */
	private static function get_wc_version() {

		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.4 or greater
	 *
	 * @since 1.0.5
	 * @return boolean true if the installed version of WooCommerce is 2.2 or greater
	 */
	public static function is_wc_version_gte_2_4() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.4', '>=' );
	}

} //end class
