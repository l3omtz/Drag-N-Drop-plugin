<?php
/**
 * MNM Item Product Thumbnail
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}
?>

<div class="mnm_image ui-draggable plus mnm-<?php echo $mnm_product->variation_id ? $mnm_product->variation_id : $mnm_product->id; ?>" >
	<?php echo $mnm_product->get_image( apply_filters( 'woocommerce_mnm_product_thumbnail_size', 'shop_thumbnail' ) ); ?>
</div>
