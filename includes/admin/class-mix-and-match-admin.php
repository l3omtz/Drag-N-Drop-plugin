<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mix and Match Admin Class
 *
 * Loads admin tabs and adds related hooks / filters
 * Adds and save product meta
 *
 * @class WC_Mix_and_Match_Admin
 * @since 	1.0.0
 * @version 1.0.4
 */
class WC_Mix_and_Match_Admin {

	/**
	 * Bootstraps the class and hooks required
	 * @return void
	 */
	public static function init() {

		// Admin jquery
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );

		// Allows the selection of the 'mix and match' type
		add_filter( 'product_type_selector', array( __CLASS__, 'product_selector_filter' ) );

		// Per-item pricing and shipping options
		add_filter( 'product_type_options', array( __CLASS__, 'type_options' ) );

		// Creates the admin panel tab
		add_action( 'woocommerce_product_write_panel_tabs', array( __CLASS__, 'product_write_panel_tab' ) );

		// Adds the base price options
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'base_price_options' ) );

		// Adds the mnm admin options
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'container_size_options' ), 10 );
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'allowed_contents_options' ), 20 );

		// Creates the panel for selecting product options
		add_action( 'woocommerce_product_write_panels', array( __CLASS__, 'product_write_panel' ) );

		// Processes and saves the necessary post metas from the selections made above
		add_action( 'woocommerce_process_product_meta_mix-and-match', array( __CLASS__, 'process_meta' ) );

		// Scheduled base sale price
		add_action( 'woocommerce_scheduled_sales', array( __CLASS__, 'scheduled_sales' ) );

	}


	/*-----------------------------------------------------------------------------------*/
	/* Write Panel / metabox */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Load the product metabox script
	 * @return void
	 */
	public static function admin_scripts() {

		// Get admin screen id
		$screen = get_current_screen();

		// WooCommerce product admin page
		if ( 'product' == $screen->id && 'post' == $screen->base ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'WC_Mix_and_Match_Metabox', WC_Mix_and_Match()->plugin_url() . '/assets/js/mnm-write-panel' . $suffix . '.js', array( 'jquery', 'wc-enhanced-select' ), WC_Mix_and_Match()->version );

			add_action( 'admin_head', array( __CLASS__, 'admin_header' ) );
		}

		// WooCommerce order admin page
		if ( 'shop_order' == $screen->id && 'post' == $screen->base ) {
			wp_enqueue_style( 'WC_Mix_and_Match_Metabox', WC_Mix_and_Match()->plugin_url() . '/assets/css/mnm-edit-order.css', '', WC_Mix_and_Match()->version );
		}

	}


	/**
	 * Add an icon to MNM product data tab
	 * @return void
	 */
	public static function admin_header() { ?>
		<style>
			#woocommerce-product-data ul.wc-tabs li.mnm_product_options a:before { content: "\f538"; font-family: "Dashicons"; }
	    </style>
	    <?php
	}


	/**
	 * Adds the 'mix and match product' type to the product types dropdown.
	 * @param  array 	$options
	 * @return array
	 */
	public static function product_selector_filter( $options ) {
		$options['mix-and-match'] = __( 'Mix & Match product', 'woocommerce-mix-and-match-products' );
		return $options;
	}


	/**
	 * Mix-and-match type options.
	 * @param  array    $options
	 * @return array
	 */
	public static function type_options( $options ) {

		$options[ 'mnm_per_product_shipping' ] = array(
			'id' 			=> '_mnm_per_product_shipping',
			'wrapper_class' => 'show_if_mix-and-match',
			'label' 		=> __( 'Per-Item Shipping', 'woocommerce-mix-and-match-products' ),
			'description' 	=> __( 'If your Mix-and-Match product consists of items that are assembled or packaged together, leave the box un-checked and define the shipping properties of the product below. If, however, the chosen items are shipped individually, check this option to retain their original shipping weight and dimensions.', 'woocommerce-mix-and-match-products' ),
			'default'		=> 'no',
		);

		$options[ 'mnm_per_product_pricing' ] = array(
			'id' 			=> '_mnm_per_product_pricing',
			'wrapper_class' => 'show_if_mix-and-match mnm_pricing',
			'label' 		=> __( 'Per-Item Pricing', 'woocommerce-mix-and-match-products' ),
			'description' 	=> __( 'When enabled, your Mix-and-Match product will be priced per-item, based on standalone item prices and tax rates.', 'woocommerce-mix-and-match-products' ),
			'default'		=> 'no',
		);

		return $options;
	}


	/**
	 * Adds the MnM Product write panel tabs
	 * @return string
	 */
	public static function product_write_panel_tab() {
		echo '<li class="mnm_product_tab show_if_mix-and-match mnm_product_options"><a href="#mnm_product_data">'.__( 'Mix & Match', 'woocommerce-mix-and-match-products' ).'</a></li>';
	}


	/**
	 * Write panel
	 * @return html
	 */
	public static function product_write_panel() {
		global $post;

		?>
		<div id="mnm_product_data" class="mnm_panel panel woocommerce_options_panel wc-metaboxes-wrapper">

			<div class="options_group mix_and_match">

				<?php do_action( 'woocommerce_mnm_product_options', $post->ID ); ?>

			</div> <!-- options group -->

		</div>

	<?php
	}


	/**
	 * Adds the container size option writepanel options.
	 *
	 * @param int $post_id
	 * @return void
	 * @since  1.0.7
	 */
	public static function container_size_options( $post_id ) {
		woocommerce_wp_text_input(
			array(
				'id' => 'mnm_container_size',
				'value' => intval( get_post_meta( $post_id, '_mnm_container_size', true ) ),
				'wrapper_class' => 'mnm_container_size_options',
				'label' => __( 'Container Size', 'woocommerce-mix-and-match-products' ),
				'description' => __( 'The number of products to be sold together in a Container. Use 0 to not enforce a container limit.', 'woocommerce-mix-and-match-products' ),
				'type' => 'number',
				'desc_tip' => true,
				'custom_attributes' => array( 'min' => 0 ) ) );
	}


	/**
	 * Adds allowed contents select2 writepanel options.
	 *
	 * @param int $post_id
	 * @return void
	 * @since  1.0.7
	 */
	public static function allowed_contents_options( $post_id ) { ?>

		<p id="mnm_allowed_contents_options" class="form-field">

			<label for="mnm_allowed_contents"><?php _e( 'Mix & Match Products', 'woocommerce-mix-and-match-products' ); ?></label>

			<?php

			// generate some data for the select2 input

			$mnm_data = get_post_meta( $post_id, '_mnm_data', true );

			$product_ids = array_filter( array_map( 'absint', array_keys( (array)$mnm_data ) ) );
			$json_ids    = array();

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( $product ) {
					$json_ids[ $product_id ] = rawurldecode( $product->get_formatted_name() );
				}
			}

			// Select2 posts value as comma-delimited list of IDs
			$value = implode( ',', array_keys( $json_ids ) );

			// json-encode the json IDs for Select2
			$json = json_encode( $json_ids );

			?>

			<input type="hidden" class="wc-product-search" style="width: 50%;" name="mnm_allowed_contents" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-mix-and-match-products' ); ?>" data-allow_clear="true" data-multiple="true" data-action="woocommerce_json_search_products_and_variations" data-selected="<?php echo esc_attr( $json ); ?>" value="<?php echo $value; ?>" />
		</p>
	<?php
	}


	/**
	 * Adds the base and sale price option writepanel options.
	 *
	 * @return void
	 * @since  1.0.6
	 */
	public static function base_price_options() {

		global $post;
		$post_id = $post->ID;

		echo '<div class="options_group mnm_base_pricing show_if_mix-and-match">';

		// Base Prices
		$base_regular_price = get_post_meta( $post_id, '_base_regular_price', true );
		$base_sale_price    = get_post_meta( $post_id, '_base_sale_price', true );

		woocommerce_wp_text_input( array( 'id' => '_mnm_base_regular_price', 'value' => $base_regular_price, 'class' => 'short', 'label' => __( 'Base Regular Price', 'woocommerce-mix-and-match-products' ) . ' (' . get_woocommerce_currency_symbol().')', 'data_type' => 'price' ) );
		woocommerce_wp_text_input( array( 'id' => '_mnm_base_sale_price', 'value' => $base_sale_price, 'class' => 'short', 'label' => __( 'Base Sale Price', 'woocommerce-mix-and-match-products' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce-mix-and-match-products' ) . '</a>' ) );

		// Special Price date range
		$sale_price_dates_from = ( $date = get_post_meta( $post_id, '_base_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to   = ( $date = get_post_meta( $post_id, '_base_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';

		echo '<p class="form-field sale_price_dates_fields wc_mnm_base_sale_price_dates_fields">
			<label for="_mnm_base_sale_price_dates_from">' . __( 'Base Sale Price Dates', 'woocommerce-mix-and-match-products' ) . '</label>
			<input type="text" class="short sale_price_dates_from" name="_mnm_base_sale_price_dates_from" id="_mnm_base_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'woocommerce-mix-and-match-products' ) . ' YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			<input type="text" class="short sale_price_dates_to" name="_mnm_base_sale_price_dates_to" id="_mnm_base_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'woocommerce-mix-and-match-products' ) . '  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			<a href="#" class="cancel_sale_schedule" style="display: inline-block; margin: 21px 0 0 10px;">'. __( 'Cancel', 'woocommerce-mix-and-match-products' ) .'</a>
			<img class="help_tip" data-tip="' . __( 'The sale will end at the beginning of the set date.', 'woocommerce-mix-and-match-products' ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />
		</p>';
		echo '</div>';
	}


	/**
	 * Process, verify and save product data
	 *
	 * @param  int 	$post_id
	 * @return void
	 */
	public static function process_meta( $post_id ) {

		// Per-Item Pricing
		// When active, min / max price is calculated on the fly and, if necessary, saved as _min_mnm_price and _max_mnm_price accordingly (taken care of in the MnM product class)

		if ( isset( $_POST[ '_mnm_per_product_pricing' ] ) ) {

			update_post_meta( $post_id, '_mnm_per_product_pricing', 'yes' );
			update_post_meta( $post_id, '_regular_price', '' );
			update_post_meta( $post_id, '_sale_price', '' );
			update_post_meta( $post_id, '_price', '' );

			// Update base price meta
			if ( isset( $_POST[ '_mnm_base_regular_price'] ) ) {
				update_post_meta( $post_id, '_base_regular_price', ( $_POST[ '_mnm_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_mnm_base_regular_price' ] ) );
			}
			if ( isset( $_POST[ '_mnm_base_sale_price' ] ) ) {
				update_post_meta( $post_id, '_base_sale_price', ( $_POST[ '_mnm_base_sale_price' ] === '' ? '' : wc_format_decimal( $_POST[ '_mnm_base_sale_price' ] ) ) );
			}
			$date_from = isset( $_POST[ '_mnm_base_sale_price_dates_from' ] ) ? wc_clean( $_POST[ '_mnm_base_sale_price_dates_from' ] ) : '';
			$date_to   = isset( $_POST[ '_mnm_base_sale_price_dates_to' ] ) ? wc_clean( $_POST[ '_mnm_base_sale_price_dates_to' ] ) : '';
			// Dates
			if ( $date_from ) {
				update_post_meta( $post_id, '_base_sale_price_dates_from', strtotime( $date_from ) );
			} else {
				update_post_meta( $post_id, '_base_sale_price_dates_from', '' );
			}
			if ( $date_to ) {
				update_post_meta( $post_id, '_base_sale_price_dates_to', strtotime( $date_to ) );
			} else {
				update_post_meta( $post_id, '_base_sale_price_dates_to', '' );
			}
			if ( $date_to && ! $date_from ) {
				update_post_meta( $post_id, '_base_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );
			}
			// Update price if on sale
			if ( '' !== $_POST[ '_mnm_base_sale_price' ] && '' == $date_to && '' == $date_from ) {
				update_post_meta( $post_id, '_base_price', wc_format_decimal( $_POST[ '_mnm_base_sale_price' ] ) );
			} else {
				update_post_meta( $post_id, '_base_price', ( $_POST[ '_mnm_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_mnm_base_regular_price' ] ) );
			}
			if ( '' !== $_POST[ '_mnm_base_sale_price' ] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_base_price', wc_format_decimal( $_POST[ '_mnm_base_sale_price' ] ) );
			}
			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_base_price', ( $_POST[ '_mnm_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_mnm_base_regular_price' ] ) );
				update_post_meta( $post_id, '_base_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_base_sale_price_dates_to', '' );
			}

		} else {

			update_post_meta( $post_id, '_mnm_per_product_pricing', 'no' );
		}

		// Non-Bundled (per-item) Shipping

		if ( isset( $_POST[ '_mnm_per_product_shipping' ] ) ) {

			update_post_meta( $post_id, '_mnm_per_product_shipping', 'yes' );
			update_post_meta( $post_id, '_virtual', 'yes' );
			update_post_meta( $post_id, '_weight', '' );
			update_post_meta( $post_id, '_length', '' );
			update_post_meta( $post_id, '_width', '' );
			update_post_meta( $post_id, '_height', '' );

		} else {

			update_post_meta( $post_id, '_mnm_per_product_shipping', 'no' );
			update_post_meta( $post_id, '_virtual', 'no' );
			update_post_meta( $post_id, '_weight', stripslashes( $_POST[ '_weight' ] ) );
			update_post_meta( $post_id, '_length', stripslashes( $_POST[ '_length' ] ) );
			update_post_meta( $post_id, '_width', stripslashes( $_POST[ '_width' ] ) );
			update_post_meta( $post_id, '_height', stripslashes( $_POST[ '_height' ] ) );
		}

		// Container size
		$limit = ( isset( $_POST[ 'mnm_container_size'] ) ) ? intval( wc_clean( $_POST['mnm_container_size' ] ) ) : 1;

		update_post_meta( $post_id, '_mnm_container_size', $limit );

		// Initialize mnm data
		$mnm_contents_data = array();

		// Populate with product data
		if ( isset( $_POST[ 'mnm_allowed_contents' ] ) && ! empty( $_POST[ 'mnm_allowed_contents' ] ) ) {

			$mnm_allowed_contents 	= array_filter( array_map( 'intval', explode( ',', $_POST['mnm_allowed_contents'] ) ) );

			$unsupported_error = false;

			// check product types of selected items
			foreach ( $mnm_allowed_contents as $mnm_id ) {

				// Get product type
				$product = wc_get_product( $mnm_id );

				if ( ! in_array( $product->product_type, WC_Mix_and_Match_Helpers::get_supported_product_types() ) || ( $product->is_type( 'variation' ) && ! $product->has_all_attributes_set() ) ) {

					$unsupported_error = true;

				} else {

					// Product-specific data, such as discounts, or min/max quantities in container may be included later on
					$mnm_contents_data[ $mnm_id ][ 'product_id' ] = $product->id;

				}

			}

			if ( $unsupported_error ) {
				WC_Admin_Meta_Boxes::add_error( __( 'Mix & Match supports simple products and product variations with all attributes defined. Other product types and partially-defined variations cannot be added to the Mix & Match container.', 'woocommerce-mix-and-match-products' ) );
			}

		}

		if ( ! empty( $mnm_contents_data ) ) {

			update_post_meta( $post_id, '_mnm_data', $mnm_contents_data );

		} else {

			delete_post_meta( $post_id, '_mnm_data' );
			WC_Admin_Meta_Boxes::add_error( __( 'Please select at least one product to use for this Mix & Match product.', 'woocommerce-mix-and-match-products' ) );
		}

		return $post_id;

	}


	/**
	 * Function which handles the start and end of scheduled sales via cron.
	 *
	 * @access public
	 * @return void
	 */
	public function scheduled_sales() {
		global $wpdb;
		if ( function_exists( 'WC_CP' ) ) {
			return;
		}
		// Sales which are due to start
		$product_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->postmeta} as postmeta_2 ON postmeta.post_id = postmeta_2.post_id
			LEFT JOIN {$wpdb->postmeta} as postmeta_3 ON postmeta.post_id = postmeta_3.post_id
			WHERE postmeta.meta_key = '_base_sale_price_dates_from'
			AND postmeta_2.meta_key = '_base_price'
			AND postmeta_3.meta_key = '_base_sale_price'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND postmeta_2.meta_value != postmeta_3.meta_value
		", current_time( 'timestamp' ) ) );
		if ( $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				$sale_price = get_post_meta( $product_id, '_base_sale_price', true );
				if ( $sale_price ) {
					update_post_meta( $product_id, '_base_price', $sale_price );
				} else {
					// No sale price!
					update_post_meta( $product_id, '_base_sale_price_dates_from', '' );
					update_post_meta( $product_id, '_base_sale_price_dates_to', '' );
				}
			}
			delete_transient( 'wc_products_onsale' );
		}
		// Sales which are due to end
		$product_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->postmeta} as postmeta_2 ON postmeta.post_id = postmeta_2.post_id
			LEFT JOIN {$wpdb->postmeta} as postmeta_3 ON postmeta.post_id = postmeta_3.post_id
			WHERE postmeta.meta_key = '_base_sale_price_dates_to'
			AND postmeta_2.meta_key = '_base_price'
			AND postmeta_3.meta_key = '_base_regular_price'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND postmeta_2.meta_value != postmeta_3.meta_value
		", current_time( 'timestamp' ) ) );
		if ( $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				$regular_price = get_post_meta( $product_id, '_base_regular_price', true );
				update_post_meta( $product_id, '_base_price', $regular_price );
				update_post_meta( $product_id, '_base_sale_price', '' );
				update_post_meta( $product_id, '_base_sale_price_dates_from', '' );
				update_post_meta( $product_id, '_base_sale_price_dates_to', '' );
			}
			delete_transient( 'wc_products_onsale' );
		}
	}

}
// launch the admin class
WC_Mix_and_Match_Admin::init();
