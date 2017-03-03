/* global wc_mnm_params */

/**
 * MnM scripts, accessible to the outside world
 */

var wc_mnm_scripts = {};

jQuery( document ).ready( function($) {

	$.fn.wc_mnm_form = function() {

		if ( ! $(this).hasClass( 'mnm_form' ) ){
			return true;
		}

		var $form        = $(this);
		var $form_cart   = $form.find( '.mnm_cart' );
		var container_id = $form_cart.data( 'container_id' );

		// if already attached, unbind all listeners before reattaching
		if ( typeof( wc_mnm_scripts[ container_id ] ) !== 'undefined' ) {
			$form.find( '*' ).off();
		}

		wc_mnm_scripts[ container_id ] = {

			$mnm_form:            $form,
			$mnm_items:           $form.find( '.mnm_item' ),
			$mnm_cart:            $form.find( '.mnm_cart' ),
			$mnm_price:           $form.find( '.mnm_price' ),
			$mnm_button:          $form_cart.find( '.mnm_add_to_cart_button' ),

			$mnm_message:         $form_cart.find( '.mnm_message' ),
			$mnm_message_content: $form_cart.find( '.mnm_message ul.mnm_message_content' ),

			container_size:       $form_cart.data( 'container_size' ),

			validation_messages:  [],
			status_messages:      [],

			init: function() {

				/**
				 * Bind event handlers
				 */

				this.bind_event_handlers();

				/**
				 * Initial states and loading
				 */

				// Add-ons support: move totals
				var $addons_totals = this.$mnm_cart.find( '#product-addons-total' );
				this.$mnm_price.after( $addons_totals );

				// Ensure notice container exists
				if ( this.$mnm_message_content.length === 0 ) {
					if ( this.$mnm_message.length > 0 ) {
						this.$mnm_message.remove();
					}
					this.$mnm_price.after( '<div class="mnm_message"><ul class="mnm_message_content woocommerce-info"></ul></div>' );
					this.$mnm_message         = this.$mnm_cart.find( '.mnm_message' );
					this.$mnm_message_content = this.$mnm_message.find( '.mnm_message_content' );
				}

				// Let 3rd party know that we are ready to rock
				this.$mnm_form.trigger( 'wc-mnm-initialized', [ this ] );

				// Init
				this.update();
			},

			/**
			 * Event Handlers
			 */

			bind_event_handlers: function() {

				var container = this;

				container.$mnm_items

					// Upon changing quantities
					.on( 'change', 'input.qty', function( event ) {
						container.update();
					} );

			},

			/**
			 * Get MnM container size
			 */

			get_container_size: function() {

				if ( typeof( this.container_size ) !== 'undefined' ) {
					return parseInt( this.container_size );
				}

				return false;
			},

			/**
			 * Failed qty validation message builder
			 */

			selected_quantity_message: function( qty ) {

				var message = qty == 1 ? wc_mnm_params.i18n_qty_message_single : wc_mnm_params.i18n_qty_message;
				return message.replace( '%s', qty );
			},

			/**
			 * Add validation/status message
			 */

			add_message: function( message, type ) {

				if ( type === 'error' ) {
					this.validation_messages.push( message.toString() );
				} else {
					this.status_messages.push( message.toString() );
				}

			},

			/**
			 * Get validation/status messages
			 */

			get_messages: function( type ) {

				var messages = [];

				if ( type === 'all' ) {
					messages = $.merge( this.status_messages, this.validation_messages );
				} else if ( type === 'error' ) {
					messages = this.validation_messages;
				} else {
					messages = this.status_messages;
				}

				return messages;

			},

			/**
			 * False if there are validation messages to display
			 */

			passes_validation: function() {

				if ( this.validation_messages.length > 0 ) {
					return false;
				}

				return true;
			},

			/**
			 * True if there are status messages to display
			 */

			has_status_messages: function() {

				if ( this.status_messages.length > 0 ) {
					return true;
				}

				return false;
			},

			/**
			 * Reset messages on update start
			 */

			reset_messages: function() {
				this.validation_messages = [];
				this.status_messages     = [];
			},

			/**
			 * Update form state
			 */

			update: function() {

				var container           = this;
				var container_size      = this.get_container_size();
				var per_item_pricing    = this.$mnm_cart.data( 'per_product_pricing' );
				var total_qty           = 0;
				var total_price         = parseFloat( this.$mnm_cart.data( 'base_price' ) );
				var total_regular_price = parseFloat( this.$mnm_cart.data( 'base_regular_price' ) );
				var formatted_price     = '';
				var total_qty_valid     = false;

				// Reset status/error messages state
				this.reset_messages();

				// Add up qties + prices
				this.$mnm_items.each( function() {

					var quantity = 0;

					$input = $(this).find( 'input.qty' );

					if ( $input.length > 0 ) {

						// restrict to min/max limits
						var min = parseFloat( $input.attr( 'min' ) ),
							max = parseFloat( $input.attr( 'max' ) );

						// max can't be higher than the container size
						if ( container_size > 0 ) {
							max = Math.min( max, parseFloat( container_size ) );
						}

						if ( min >= 0 && parseFloat( $input.val() ) < min ) {
							$input.val( min );
						}

						if ( max > 0 && parseFloat( $input.val() ) > max ) {
							$input.val( max );
						}

						// calculate total container quantity
						quantity   = parseInt( $input.val() );
						total_qty += quantity;

						if ( per_item_pricing == true ) {
							total_price         += parseFloat( $(this).data( 'price' ) ) * quantity;
							total_regular_price += parseFloat( $(this).data( 'regular_price' ) ) * quantity;
						}
					}

				} );

				// Price calculation
				if ( per_item_pricing == true ) {

					// Trigger addons update to refresh addons totals
					var mnm_addon = this.$mnm_form.find( '.mnm_cart #product-addons-total' );

					if ( mnm_addon.length > 0 ) {
						mnm_addon.data( 'price', total_price );
						this.$mnm_cart.trigger( 'woocommerce-product-addons-update' );
					}

					if ( total_qty > 0 ) {
						if ( total_price == 0 ) {

							formatted_price = '<p class="price"><span class="total"></span>'+ wc_mnm_params.i18n_free +'</p>';

						} else {

							var price_format         = wc_mnm_woocommerce_number_format( wc_mnm_number_format( total_price ) );
							var regular_price_format = wc_mnm_woocommerce_number_format( wc_mnm_number_format( total_regular_price ) );

							if ( total_regular_price > total_price ) {
								formatted_price = '<p class="price"><del>' + regular_price_format + '</del> <ins>' + price_format + '</ins></p>';
							} else {
								formatted_price = '<p class="price">' + price_format + '</p>';
							}
						}
					}
				}

				// Validation
				if ( ( container_size === 0 && total_qty > 0 ) || ( container_size > 0 && total_qty == container_size ) ) {
					total_qty_valid = true;
				}

				// Add error message
				if ( ! total_qty_valid ) {
					// "Selected X total"
					var selected_qty_message = this.selected_quantity_message( total_qty );

					// finite vs infinite container size error (select X vs select "some")
					var error_message = '';

					if( container_size == 0 ){
						error_message = wc_mnm_params.i18n_empty_error;
					} else if ( container_size == 1 ){
						error_message = wc_mnm_params.i18n_qty_error_single;
					} else {
						error_message = wc_mnm_params.i18n_qty_error;
					}

					// add error message, replacing placeholders with current values
					this.add_message( error_message.replace( '%s', container_size ).replace( '%v', selected_qty_message ), 'error' );
				}

				// Let mini extensions add their own error/status messages
				this.$mnm_form.trigger( 'wc-mnm-validation', [ container, total_qty ] );

				if ( this.passes_validation() ) {

					// Add selected qty status message if there are no error messages and infinite container is used
					if ( container_size === 0 ) {
						this.add_message( this.selected_quantity_message( total_qty ) );
					}

					// enable add to cart button
					this.$mnm_button.removeAttr( 'disabled' ).removeClass( 'disabled' );
					this.$mnm_form.trigger( 'wc-mnm-display-add-to-cart-button', [ container ] );

				} else {

					// disable add to cart button
					this.$mnm_button.attr( 'disabled', true ).addClass( 'disabled' );
					this.$mnm_form.trigger( 'wc-mnm-hide-add-to-cart-button', [ container ] );
				}

				// Display the status/error messages
				if ( this.has_status_messages() || false === this.passes_validation() ) {

					var $messages = $( '<ul/>' );
					var messages  = this.get_messages( 'all' );

					if ( messages.length > 0 ) {
						$.each( messages, function( i, message ) {
							$messages.append( $( '<li/>' ).html( message ) );
						} );
					}

					this.$mnm_message_content.html( $messages.html() );
					this.$mnm_message.slideDown( 200 );

				} else {
					this.$mnm_message.slideUp( 200 );
				}

				// update price
				if ( formatted_price !== '' ) {
					this.$mnm_price.html( formatted_price );
					// show price
					this.$mnm_price.slideDown( 200 );
				} else {
					// hide price
					this.$mnm_price.slideUp( 200 );
				}

				this.$mnm_form.trigger( 'wc-mnm-price-updated', [ container, total_qty, this.$mnm_price ] );

			}

		};

		wc_mnm_scripts[ container_id ].init();

	};

	$( 'body' ).on( 'quick-view-displayed', function() {

		$( '.mnm_form' ).each( function() {
			$(this).wc_mnm_form();
		} );

	} );

	$( '.mnm_form' ).each( function() {
		$(this).wc_mnm_form();
	} );

	/**
	 * Helper functions
	 */

	function wc_mnm_woocommerce_number_format( price ) {

		var remove 		= wc_mnm_params.currency_format_decimal_sep;
		var position 	= wc_mnm_params.currency_position;
		var symbol 		= wc_mnm_params.currency_symbol;
		var trim_zeros 	= wc_mnm_params.currency_format_trim_zeros;
		var decimals 	= wc_mnm_params.currency_format_num_decimals;

		if ( trim_zeros === 'yes' && decimals > 0 ) {
			for (var i = 0; i < decimals; i++) { remove = remove + '0'; }
			price = price.replace( remove, '' );
		}

		var price_format = '';

		if ( position === 'left' ) {
			price_format = '<span class="amount">' + symbol + price + '</span>';
		} else if ( position === 'right' ) {
			price_format = '<span class="amount">' + price + symbol +  '</span>';
		} else if ( position === 'left_space' ) {
			price_format = '<span class="amount">' + symbol + '&nbsp;' + price + '</span>';
		} else if ( position === 'right_space' ) {
			price_format = '<span class="amount">' + price + '&nbsp;' + symbol +  '</span>';
		}

		return price_format;
	}

	function wc_mnm_number_format( number ) {

		var decimals 		= wc_mnm_params.currency_format_num_decimals;
		var decimal_sep 	= wc_mnm_params.currency_format_decimal_sep;
		var thousands_sep 	= wc_mnm_params.currency_format_thousand_sep;

	    var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	    var d = decimal_sep == undefined ? ',' : decimal_sep;
	    var t = thousands_sep == undefined ? '.' : thousands_sep, s = n < 0 ? '-' : '';
	    var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '', j = (j = i.length) > 3 ? j % 3 : 0;

	    return s + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '');
	}

} );
