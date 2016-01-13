<?php
/**
 * Updates
 *
 * @package     WC_NOD
 * @subpackage  Updates
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.0.1
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
	exit;
	
/**
 * Determine if an update needs to be applied.
 *
 * @since	    0.0.1
 * @global		
 * @param       arr		$version		Current version per the DB.
 * @return
 */
function nod_do_updates( $version )	{
	update_option( WC_NOD_VER_KEY, WC_NOD_VER );
} // nod_do_updates	
?>