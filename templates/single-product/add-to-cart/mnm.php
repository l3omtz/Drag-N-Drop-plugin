<?php
/**
 *  DRAG AND DROP Items
 *
 * @author 		Leo Martinez
 * @version     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}
?>

<?php if ( $product->has_available_children() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form method="post" enctype="multipart/form-data" class="mnm_form cart cart_group" id="left-pane">

		<table cellspacing="0" class="mnm_table">
			<thead>
				<tr>
					<th class="product-thumbnail">&nbsp;</th>
					<th class="product-name"><?php _e( 'Product', 'woocommerce-mix-and-match-products' );?></th>
					<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce-mix-and-match-products' );?></th>
				</tr>
			</thead>

			<tbody>

				<?php

					foreach ( $product->get_available_children() as $mnm_product ) {
						// Load the table row for each item
						wc_get_template(
							'single-product/mnm/mnm-item.php',
							array(
								'product' => $product,
								'mnm_product' => $mnm_product
							),
							'',
							WC_Mix_and_Match()->plugin_path() . '/templates/'
						);
					}
				?>
			</tbody>

		</table>

		<div class="mnm_cart cart" <?php echo $product->get_data_attributes(); ?>>

			<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

			<div class="mnm_button_wrap" style="display:block">

				<div class="mnm_price"></div>

				<?php
					$message = $container_size > 0 ? sprintf( _n( 'Please select %s item to continue&hellip;', 'Please select %s items to continue&hellip;', $container_size, 'woocommerce-mix-and-match-products' ), $container_size ) : __( 'Please select some items to continue&hellip;', 'woocommerce-mix-and-match-products' );
					$message = apply_filters( 'woocommerce_mnm_container_quantity_message', $message, $product );
				?>

				<div class="mnm_message"><div class="mnm_message_content woocommerce-info"><?php echo $message; ?></div></div>
				<?php

				// MnM Availability
				$availability = $product->get_availability();

				if ( $availability[ 'availability' ] ){
					echo apply_filters( 'woocommerce_stock_html', '<p class="stock ' . $availability[ 'class' ] . '">' . $availability[ 'availability' ] . '</p>', $availability[ 'availability' ] );
				}

		 		if ( ! $product->is_sold_individually() ){
		 			woocommerce_quantity_input( array(
		 				'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
		 				'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
		 			) );
		 		}
		 		?>

		 		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />

				<button type="submit" class="single_add_to_cart_button mnm_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>

			</div>
		</div>
		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php else : ?>

	<?php
	// Availability
	$availability      = $product->get_availability();
	$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . esc_html( $availability[ 'availability' ] ) . '</p>';

	echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $product );
?>

<?php endif; ?>
