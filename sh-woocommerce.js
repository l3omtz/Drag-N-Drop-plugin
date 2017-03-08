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

function initAddToCartPlusMinus(){

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


		// *** Change the image value on click *** //

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
	});
}
