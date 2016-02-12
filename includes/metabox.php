<?php
/**
 * Metabox output
 *
 * @package     WC_NOD
 * @subpackage  Metabox
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.0.1
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
	exit;

/**
 * Add the Exclude from NOD option row to the Advanced options tab within Product view.
 *
 * The Exclude from NOD field enables admins to select individual downloads
 * that are not eligible for the Next Order Discount offer.
 *
 * @since	0.0.1
 * @global	obj		$post		The WP_Post object.
 * @global	int		$thepostid	The product ID.
 * @param
 * @return	void
 */
function woo_nod_render_exclude_nod_row() {
    global $post, $thepostid;
	
	woocommerce_wp_checkbox(
		array(
			'id'          => '_wc_exclude_from_nod',
			'label'       => __( 'Exclude NOD offers?', 'wc-next-order-discount' ),
			'description' => __( 'Exclude this Product from NOD offers?', 'woocommerce' )
		)
	);
} // woo_nod_render_exclude_nod_row
add_action( 'woocommerce_product_options_advanced', 'woo_nod_render_exclude_nod_row' );

/**
 * Save the setting for NOD Exclusion.
 *
 *
 * @since	0.0.1
 * @param	int		$thepostid	The product ID.
 * @return	void
 */
function woo_nod_save_nod_exclude_option( $post_id )	{
	$exclude_from_nod = isset( $_POST['_wc_exclude_from_nod'] ) ? 'yes' : 'no';
	
	update_post_meta( $post_id, '_wc_exclude_from_nod', $exclude_from_nod );
} // woo_nod_save_nod_exclude_option
add_action( 'woocommerce_process_product_meta', 'woo_nod_save_nod_exclude_option' );
?>