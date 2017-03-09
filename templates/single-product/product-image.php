<?php
/**
 * Single Product Image
 *
 *  wp-content/themes/stockholm/woocommerce/single-product/product-image.php
 *
 * Template for picker and non picker box
 * Has tempalate for box of 5, 15 , and 10
 *
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		Leo Martinex
 * @package 	WooCommerce/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;
?>




<?php if(  $post->ID == 363 || $post->ID == 374 || $post->ID == 369  ){ ?>

<div class="images box-container">

	<?php if($post->ID == 363){ ?>

	<div id="box-of-5" class="ui-draggable showBox ">
		<div id="slot-1" class="slot empty ui-dropable ">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-2" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-3" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-4" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-5" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
	</div>

	<?php }else if ($post->ID == 369){ ?>

	<div id="box-of-10" class="ui-draggable showBox ">
		<div id="slot-1" class="slot empty ui-dropable ">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-2" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-3" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-4" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-5" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-6" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-7" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-8" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-9" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-10" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
	</div>

	<?php }else{ ?>

	<div id="box-of-15" class="ui-draggable showBox ">
		<div id="slot-1" class="slot empty ui-dropable ">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-2" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-3" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-4" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-5" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-6" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-7" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-8" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-9" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-10" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-11" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-12" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-13" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-14" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
		<div id="slot-15" class="slot empty ui-dropable">
			<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron">
		</div>
	</div>

	<?php } ?>


</div>


<?php }else{ ?>
	<div class="images">
<?php
	if ( has_post_thumbnail() ) {
		$attachment_count = count( $product->get_gallery_attachment_ids() );
		$gallery          = $attachment_count > 0 ? '[product-gallery]' : '';
		$props            = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
		$image            = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array(
			'title'	 => $props['title'],
			'alt'    => $props['alt'],
		) );
		echo apply_filters(
			'woocommerce_single_product_image_html',
			sprintf(
				'<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto%s">%s</a>',
				esc_url( $props['url'] ),
				esc_attr( $props['caption'] ),
				$gallery,
				$image
			),
			$post->ID
		);
	} else {
		echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $post->ID );
	}

	do_action( 'woocommerce_product_thumbnails' );
?>
</div>

<?php } ?>
