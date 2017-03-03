<?php
/**
 * Functions related to extension cross-compatibility.
 *
 * @class    WC_MNM_Compatibility
 * @version  1.0.5
 * @since    1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

class WC_Mix_and_Match_Compatibility {

	function __construct() {

		
		// Multiple Shipping Addresses support
		if ( class_exists( 'WC_Ship_Multiple' ) ) {
			require_once( 'compatibility/class-wc-ship-multiple-compatibility.php' );
		}

		// Points and Rewards support
		if ( class_exists( 'WC_Points_Rewards_Product' ) ) {
			require_once( 'compatibility/class-wc-pnr-compatibility.php' );
		}

		// Pre-orders support
		if ( class_exists( 'WC_Pre_Orders' ) ) {
			require_once( 'compatibility/class-wc-po-compatibility.php' );
		}

		// One Page Checkout support
		if ( function_exists( 'is_wcopc_checkout' ) ) {
			require_once( 'compatibility/class-wc-opc-compatibility.php' );
		}

		// Wishlists support
		if ( class_exists( 'WC_Wishlists_Plugin' ) ) {
			require_once( 'compatibility/class-wc-wl-compatibility.php' );
		}

		// Shipstation integration
		require_once( 'compatibility/class-wc-shipstation-compatibility.php' );
	}
}
