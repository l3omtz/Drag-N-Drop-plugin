<?php
/**
 * MNM Item Variation Attributes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

echo WC_Mix_and_Match_Helpers::get_formatted_variation_attributes( $mnm_product );
