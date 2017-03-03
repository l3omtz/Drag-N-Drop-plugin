jQuery( function($){

	if ( typeof woocommerce_admin_meta_boxes === 'undefined' ) {
		woocommerce_admin_meta_boxes = woocommerce_writepanel_params;
	}

	// Mix and Match type specific options
	$( 'body' ).on( 'woocommerce-product-type-change', function( event, select_val, select ) {

		if ( select_val == 'mix-and-match' ) {

			$( 'input#_downloadable' ).prop( 'checked', false );
			$( 'input#_virtual' ).prop( 'checked', false );

			$( '.show_if_simple' ).show();
			$( '.show_if_mix-and-match' ).show();

			$( 'input#_downloadable' ).parent().hide();
			$( 'input#_virtual' ).parent().hide();

			$( 'input#_manage_stock' ).change();

			$( '.options_group.pricing' ).show();

			$( 'input#_mnm_per_product_pricing' ).change();
			$( 'input#_mnm_per_product_shipping' ).change();

			$( '#_nyp' ).change();

		}

	});

	$( 'select#product-type' ).change();

	// non-bundled shipping
	$( 'input#_mnm_per_product_shipping' ).change( function() {

		if ( $( 'select#product-type' ).val() == 'mix-and-match' ) {

			if ( $( 'input#_mnm_per_product_shipping' ).is( ':checked' ) ) {
				$( '.show_if_virtual' ).show();
				$( '.hide_if_virtual' ).hide();
				if ( $( '.shipping_tab' ).hasClass( 'active' ) ){
					$( 'ul.product_data_tabs li:visible' ).eq(0).find('a').click();
				}
			} else {
				$( '.show_if_virtual' ).hide();
				$( '.hide_if_virtual' ).show();
			}
		}

	} ).change();

	// show options if pricing is static
	$( 'input#_mnm_per_product_pricing' ).change( function() {

		if ( $( 'select#product-type' ).val() == 'mix-and-match' ) {

			if ( $(this).is( ':checked' ) ) {

		        $( '#_regular_price' ).val('');
		        $( '#_sale_price' ).val('');

				$( '._tax_class_field' ).closest( '.options_group' ).hide();
				$( '.pricing' ).hide();
				$( '.mnm_base_pricing' ).show();

			} else {

				$( '._tax_class_field' ).closest( '.options_group' ).show();

				if ( ! $( '#_nyp' ).is( ':checked' ) ){
					$( '.pricing' ).show();
				}

				$( '.mnm_base_pricing' ).hide();
			}
		}

	} ).change();

	// nyp support
	$( '#_nyp' ).change( function() {

		if ( $( 'select#product-type' ).val() == 'mix-and-match' ) {

			if ( $( '#_nyp' ).is( ':checked' ) ) {
				$( 'input#_mnm_per_product_pricing' ).prop( 'checked', false );
				$( '.mnm_pricing' ).hide();
			} else {
				$( '.mnm_pricing' ).show();
			}

			$( 'input#_mnm_per_product_pricing' ).change();
		}

	} ).change();

});
