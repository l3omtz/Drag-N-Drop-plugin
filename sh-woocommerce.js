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
	$j(document).on( 'click', '.quantity .plus, .quantity .minus, .product-thumbnail .img-plus', function() {

		// Get values
									// Find the form input value from outside it self container
		var $qty		= $j(this).parent().parent('tr').find('.product-quantity .quantity .qty'),
			currentVal	= parseFloat( $qty.val() ),
			max			= parseFloat( $qty.attr( 'max' ) ),
			min			= parseFloat( $qty.attr( 'min' ) ),
			step		= $qty.attr( 'step' );

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

		// With class .img-plus
		if ( $j( this ).is( '.img-plus' ) ) {
			console.log('plus cicked image');

			if ( max && ( max == currentVal || currentVal > max ) ) {
				$qty.val( max );
			} else {
				//  Add the quanity to the form value so it can be updated
				// to the cart
				$qty.val( currentVal + parseFloat( step ) );
				console.log($qty);
			}

		}

		// Trigger change event
		$qty.trigger( 'change' );

	}); // END CLICK TO CART FUNCTION

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
