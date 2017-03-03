<?php
/**
 * MNM Item Table Row
 * @version  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}
?>
<tr class="mnm_item" data-regular_price="<?php echo $product->is_priced_per_product() ? WC_Mix_and_Match_Helpers::get_product_display_price( $mnm_product, $mnm_product->get_regular_price() ) : 0; ?>" data-price="<?php echo $product->is_priced_per_product() ? WC_Mix_and_Match_Helpers::get_product_display_price( $mnm_product, $mnm_product->get_price() ) : 0; ?>">

	<td class="product-thumbnail">

		<?php

			/**
			 * woocommerce_mnm_item_thumbnail hook
			 *
			 * @hooked woocommerce_template_mnm_product_thumbnail - 10
			 */
			do_action( 'woocommerce_mnm_row_item_thumbnail', $mnm_product, $product );
		?>

	</td>

	<td class="product-name">
		<?php
			/**
			 * woocommerce_mnm_row_item_column_two hook
			 *
			 * @hooked woocommerce_template_mnm_product_title - 10
			 * @hooked woocommerce_template_mnm_product_attributes - 20
			 */
			do_action( 'woocommerce_mnm_row_item_description', $mnm_product, $product );
		?>

	</td>

	<td class="product-quantity">
		<?php
			/**
			 * woocommerce_mnm_row_item_column_three hook
			 *
			 * @hooked woocommerce_template_mnm_product_quantity - 10
			 */
			do_action( 'woocommerce_mnm_row_item_quantity', $mnm_product, $product );
		?>

	</td>

</tr>
