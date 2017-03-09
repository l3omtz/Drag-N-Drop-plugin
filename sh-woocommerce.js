$j(document).ready(function() {
	"use strict";

    $j('.price_slider_wrapper').parents('.widget').addClass('widget_price_filter');
    initSelect2();
    initAddToCartPlusMinus();
});

function initSelect2(){
    $j('.woocommerce-ordering .orderby, #calc_shipping_country, #dropdown_product_cat, select#calc_shipping_state').select2({
        minimumResultsForSearch: -1
    });
    $j('.woocommerce-account .country_select').select2();
}


/* ----
  START ON CLICK PRODUCT IMAGE FUNCTIONS
  ----- */

function initAddToCartPlusMinus(){

	//  Add class to where the click should add product to cart
	$j(document).on( 'click', '.quantity .plus, .quantity .minus, .product-thumbnail .img-plus, .box-container .slot', function() {

		// Get values
									// Find the form input value from outside it self container
		var $qty		= $j(this).parent().parent('tr').find('.product-quantity .quantity .qty'),
			$boxQty 		= $j(this).parent().parent().parent().parent().find('.summary form .mnm_item td.mnm-376 .quantity .qty'),
			currentVal	= parseFloat( $qty.val() ),
			max			= parseFloat( $qty.attr( 'max' ) ),
			min			= parseFloat( $qty.attr( 'min' ) ),
			step		= $qty.attr( 'step' ),

			boxCurrentVal	= parseFloat( $boxQty.val() ),
			boxMax			= parseFloat( $boxQty.attr( 'max' ) ),
			boxMin			= parseFloat( $boxQty.attr( 'min' ) ),
			boxStep		= $boxQty.attr( 'step' ),

			$379Qty 	= $j(this).parent().parent().parent().parent().find('.summary form .mnm_item td.mnm-379 .quantity .qty'),
			$379CurrentVal	= parseFloat( $379Qty.val() ),
			$379Max			= parseFloat( $379Qty.attr( 'max' ) ),
			$379Min			= parseFloat( $379Qty.attr( 'min' ) ),
			$379Step		= $379Qty.attr( 'step' );

		// Format values
		if ( ! currentVal || currentVal === '' || currentVal === 'NaN' ) currentVal = 0;
		if ( max === '' || max === 'NaN' ) max = '';
		if ( min === '' || min === 'NaN' ) min = 0;
		if ( step === 'any' || step === '' || step === undefined || parseFloat( step ) === 'NaN' ) step = 1;

		// Change the value on the plus button -- not in use
		if ( $j( this ).is( '.plus' ) ) {

			if ( max && ( max == currentVal || currentVal > max ) ) {
				$qty.val( max );
			} else {
				$qty.val( currentVal + parseFloat( step ) );
			}

		} else {

			if ( min && ( min == currentVal || currentVal < min ) ) {
				$qty.val( min );
			} else if ( currentVal > 0 ) {
				$qty.val( currentVal - parseFloat( step ) );
			}
		}

		// *** Change the value on product image click *** //
		// With tag img
		if ( $j( this ).is( 'img' ) ) {

			console.log('plus cicked image');

			if ( max && ( max == currentVal || currentVal > max ) ) {
				$qty.val( max );

			} else {
				//  Add the quanity to the form value so it can be updated
				// to the cart
				$qty.val( currentVal + parseFloat( step ) );
			}

		} else if( $j(this).is('.mnm-376') ){
			console.log('minus cicked image');
			if ( max && ( max == currentVal || currentVal > max ) ) {
				$qty.val( max );
			} else if ( boxCurrentVal > 0 ) {
				$boxQty.val( boxCurrentVal - parseFloat( step ) );
			}

			// REMOVE IMAGE
			// 1 .Fetch Class marked Draggable
			var name = $j(this).parent('.slot')      //.find('.slot');

			// 2 .Check if class exists on click
			if(name.length != 0) {
				console.log('removed');
        		// 3 .Remove draggable image and replace with default
        			$j(name).find("div").remove();
        			$j(name).removeClass('containsMac');
        			$j(name).addClass('empty');
				$j('<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron" class="ui-droppable">').appendTo(jQuery(name));

			}
		}else if( $j(this).is('.mnm-379')){
			console.log('379 minus cicked image');
			if ( max && ( max == currentVal || currentVal > max ) ) {
				$qty.val( max );
			} else if ( $379CurrentVal > 0 ) {
				$379Qty.val( $379CurrentVal - parseFloat( step ) );
				console.log('379 minus yes image');
			}

			// REMOVE IMAGE
			// 1 .Fetch Class marked Draggable
			var name = $j(this).parent('.slot')      //.find('.slot');

			// 2 .Check if class exists on click
			if(name.length != 0) {
				console.log('removed');
        		// 3 .Remove draggable image and replace with default
        			$j(name).find("div").remove();
        			$j(name).removeClass('containsMac');
        			$j(name).addClass('empty');
				$j('<img src="http://tiktalkinteractive.com/savor/wp-content/uploads/2017/03/MacLogo_Picker.png" alt="Macaron" class="ui-droppable">').appendTo(jQuery(name));

			}

		}// End if

		// Trigger change event
		$qty.trigger( 'change' );

	}); // END CLICK TO CART FUNCTION


	// *** ADD MACARON TO BASKET ON CLICK *** //
	$j(document).on('click', "#left-pane .ui-draggable", function(){

				var macaronCount = $j(".showBox .containsMac").length;
				var totalBoxMacs = $j(".slot").length



				if (macaronCount < totalBoxMacs) {

					// 1 .Search through for empty basket item
					for (var n = 1; n < 19; ++ n){

					var macaron = '.showBox #slot-' + n;


						// 2. If empty replace with clicked macaron
						if ($j(macaron).hasClass("empty")) {

							$j(macaron).children("img").remove();
									$j(this).clone().appendTo(macaron).css({"transform":"rotate(-90deg)", "width":"100px"} );
									$j(macaron).removeClass("empty");
									$j(macaron).addClass("containsMac");

							//Count Macarons and show add button when full
								var totalBasketMacs = $j(".showBox .slot").length
								var macaronsAdded = $(".showBox .containsMac").length;

								if (macaronsAdded == totalBasketMacs) {
						$j(this).MacaronPickerResults();
								}

									return false;
								}
					}
				 }
		});

}
