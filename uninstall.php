<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/**
 * Next Order Discount Uninstall Procedures
 * 
 * 
 * 
 */
// Remove the plugin option keys
$options = array( 'woo_nod_version', 'woo_nod_pending_offers' );

foreach( $options as $option )	{
	delete_option( $option );	
}
?>