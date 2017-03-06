<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;
?>
<div class="images box-container">
	<div id="box-of-5" class="ui-draggable showBox">
		<div id="slot-1" class="slot empty ui-dropable">
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
</div>
