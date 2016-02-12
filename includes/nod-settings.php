<?php
/**
 * Admin Settings
 *
 * @package     NOD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.0.1
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
	exit;

/**
 * Add NOD settings tab to WooCommerce settings
 *
 * @since	    0.0.1
 * @global		
 * @param       arr		$tabs		Existing WooCommerce tabs
 * @return      arr		$tabs		Filtered WooCommerce tabs
 */
function nod_settings_tabs( $tabs )	{
	$tabs['nod_settings_tab'] = __( 'Next Order Discount', 'wc-next-order-discount' );

	return $tabs;
} // nod_settings_tabs
add_filter( 'woocommerce_settings_tabs_array', 'nod_settings_tabs', 50 );

/**
 * Output the settings options to the tab.
 *
 * @since	    0.0.1
 * @global		
 * @param
 * @return
 */
function nod_do_settings_tab()	{
	woocommerce_admin_fields( nod_registered_settings() );
} // do_settings_tab
add_action( 'woocommerce_settings_tabs_nod_settings_tab', 'nod_do_settings_tab' );

/**
 * Save the NOD settings.
 *
 * @since	    0.0.1
 * @global		
 * @param
 * @return
 */
function nod_update_settings() {
    woocommerce_update_options( nod_registered_settings() );
} // nod_update_settings
add_action( 'woocommerce_update_options_nod_settings_tab', 'nod_update_settings' );

/**
 * Add NOD settings sections to the existing WooCommerce sections
 *
 * @since	    0.0.1
 * @global		
 * @param       arr		$sections		Existing WooCommerce sections
 * @return      arr		$sections		Filtered WooCommerce sections
 */
function nod_settings_sections( $sections )	{
	$sections['nod_general'] = __( 'NOD General Settings', 'wc-next-order-discount' );
	$sections['nod_email'] = __( 'NOD Email Settings', 'wc-next-order-discount' );

	return $sections;
} // nod_settings_sections
add_filter( 'woocommerce_get_sections_nod_settings_tab', 'nod_settings_sections' );

/**
 * Add NOD registered settings to the WooCommerce extensions tab
 *
 * @since	    0.0.1
 * @global		
 * @param
 * @return
 */
function nod_registered_settings()	{
	return apply_filters(
		'woo_nod_settings',
		array(
			'nod_settings_title' => array(
				'name'     => __( 'General NOD Settings', 'wc-next-order-discount' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'nod_settings_title'
			),
			'nod_enable'		=> array(
				'name'		=> __( 'Enable Next Order Discount?', 'wc-next-order-discount' ),
				'type'		=> 'checkbox',
				'desc'		=> __( 'Check this box to enable the Next Order Discount feature.', 'wc-next-order-discount' ),
				'id'		=> 'nod_enable'
			),
			'nod_first'	=> array(
				'id'   => 'nod_first',
				'name' => __( 'First NOD after', 'edd-nod' ),
				'desc' => __( 'Enter the number of orders a customer must complete before qualifying for their first NOD.', 'edd-nod' ),
				'type' => 'number',
				'class' => 'small-text',
				'default'  => WC_NOD()->settings->first
			),
			'nod_continue'	=> array(
				'id'   => 'nod_continue',
				'name' => __( 'Continue with NOD offers?', 'edd-nod' ),
				'desc' => sprintf(
					__( 
					'Check this box to send NOD offers after customer completes every %sRepeat NOD offer after every%s orders.', 'edd-nod' ),
					'<code>',
					'</code>'
				),
				'type' => 'checkbox'
			),
			'nod_repeat'	=> array(
				'id'   => 'nod_repeat',
				'name' => __( 'Repeat NOD offer after every', 'edd-nod' ),
				'desc' => __( 'Enter the number of orders the customer needs to complete to qualify for their second NOD. Enter 0 to deactivate.', 'edd-nod' ),
				'type' => 'number',
				'class' => 'small-text',
				'default'  => WC_NOD()->settings->repeat
			),
			'nod_free'			=> array(
				'id'		=> 'nod_free',
				'name'		=> __( 'Apply to Free Purchases?', 'wc-next-order-discount' ),
				'desc'		=> __( 'Check this box to enable the Next Order Discount following free purchases.', 'wc-next-order-discount' ),
				'type'		=> 'checkbox'
			),
			'nod_min_spend' 	=> array(
				'id'		=> 'nod_min_spend',
				'name'		=> __( 'Minimum Spend', 'wc-next-order-discount' ),
				'desc'		=> __( 'Enter the minimum spend required for Next Order Discount (i.e. 10), or leave empty.', 'wc-next-order-discount' ),
				'type'		=> 'number',
				'class'		=> 'small-text',
				'default' 	=> WC_NOD()->settings->min_spend
			),
			'nod_prefix' 		=> array(
				'id'		=> 'nod_prefix',
				'name'		=> __( 'Discount Code Prefix', 'wc-next-order-discount' ),
				'desc'		=> __( 'Enter a prefix for the discount code, or leave empty.', 'wc-next-order-discount' ),
				'type'		=> 'text',
				'default'	=> WC_NOD()->settings->prefix
			),
			'nod_rate'	  => array(
				'id'		=> 'nod_rate',
				'name'		=> __( 'Discount Amount', 'wc-next-order-discount' ),
				'desc'		=> __( 'Enter the discount amount.', 'wc-next-order-discount' ),
				'type'		=> 'number',
				'class'		=> 'small-text',
				'default'	=> WC_NOD()->settings->rate
			),
			'nod_type'	  => array(
				'id'		=> 'nod_type',
				'name'		=> __( 'Discount Type', 'wc-next-order-discount' ),
				'desc'		=> __( 'The kind of discount to apply for Next Order Discounts.', 'wc-next-order-discount' ),
				'type'		=> 'select',
				'class'		=> 'wc-enhanced-select',
				'options' 	=> array(
					'fixed_cart'		=> __( 'Cart Discount', 'wc-next-order-discount' ),
					'percent'			=> __( 'Cart % Discount', 'wc-next-order-discount' ),
				),
				'default'	=> WC_NOD()->settings->type
			),
			'nod_send_after'	  => array(
				'id'   		=> 'nod_send_after',
				'name' 		=> __( 'Send Offer After', 'wc-next-order-discount' ),
				'desc' 		=> __( 'Select length of time after purchase to wait before sending the offer.', 'wc-next-order-discount' ),
				'type' 		=> 'select',
				'class'		=> 'wc-enhanced-select',
				'options'	=> array(
					'12 hours'	=> __( '12 Hours', 'wc-next-order-discount' ),
					'1 day'		=> __( '1 Day', 'wc-next-order-discount' ),
					'36 hours'	=> __( '36 Hours', 'wc-next-order-discount' ),
					'2 days'	=> __( '2 Days', 'wc-next-order-discount' ),
					'3 days'	=> __( '3 Days', 'wc-next-order-discount' ),
					'4 days'	=> __( '4 Days', 'wc-next-order-discount' ),
					'5 days'	=> __( '5 Days', 'wc-next-order-discount' ),
					'6 days'	=> __( '6 Days', 'wc-next-order-discount' ),
					'1 week'	=> __( '1 Week', 'wc-next-order-discount' )
				),
				'default'  	=> WC_NOD()->settings->send_after,
			),
			'nod_expires'	  => array(
				'id'   		=> 'nod_expires',
				'name' 		=> __( 'Offer Expires after', 'wc-next-order-discount' ),
				'desc' 		=> __( 'Select time period offer is valid for.', 'wc-next-order-discount' ),
				'type' 		=> 'select',
				'class'		=> 'wc-enhanced-select',
				'options'	=> array(
					'1 day'		=> __( '1 Day', 'wc-next-order-discount' ),
					'2 days'	=> __( '2 Days', 'wc-next-order-discount' ),
					'3 days'	=> __( '3 Days', 'wc-next-order-discount' ),
					'4 days'	=> __( '4 Days', 'wc-next-order-discount' ),
					'5 days'	=> __( '5 Days', 'wc-next-order-discount' ),
					'6 days'	=> __( '6 Days', 'wc-next-order-discount' ),
					'1 week'	=> __( '1 Week', 'wc-next-order-discount' ),
					'10 days'	=> __( '10 Days', 'wc-next-order-discount' ),
					'2 weeks'	=> __( '2 Weeks', 'wc-next-order-discount' ),
					'1 month'	=> __( '1 Month', 'wc-next-order-discount' )
				),
				'default'  	=> WC_NOD()->settings->expires,
			),
			array(
				'type'		=> 'sectionend',
				'id'		=> 'nod-main-end'
			),
		)
	);
} // nod_registered_settings

/**
 * Retrieve NOD settings and store them in our public WC_NOD()->settings var as an object
 *
 *
 *
 */
function nod_settings()	{	
	$settings						= new stdClass();
	$settings->enable				= get_option( 'nod_enable', false );
	$settings->first    			= get_option( 'nod_first', '1' );
	$settings->repeat   			= get_option( 'nod_repeat', false );
	$settings->free					= get_option( 'nod_free', false );
	$settings->free					= get_option( 'nod_free', false );
	$settings->min_spend            = get_option( 'nod_min_spend', '0' );
	$settings->prefix				= get_option( 'nod_prefix', '' );
	$settings->rate					= get_option( 'nod_rate', '20' );
	$settings->type					= get_option( 'nod_type', 'percent' );
	$settings->send_after			= get_option( 'nod_send_after', '12 hours' );
	$settings->expires				= get_option( 'nod_expires', '1 week' );
	$settings->email_subject		= get_option( 'woocommerce_wc_nod_offer_subject', 
		sprintf( __( 'Discount Offer from %s', 'wc-next-order-discount' ), '{site_title}' ) );
		
	$settings->email_heading		= get_option( 'woocommerce_wc_nod_offer_heading', 
		sprintf( __( '%s Discount on Your Next Purchase - Act Now!', 'wc-next-order-discount' ), '{offer_amount}' ) );
		
	$settings->email_type			= get_option( 'woocommerce_wc_nod_offer_email_type', 'html' );
	$settings->admin_emails			= get_option( 'woocommerce_wc_nod_offer_admin_emails', get_bloginfo( 'admin_email' ) );
	$settings->disable_admin		= get_option( 'woocommerce_wc_nod_offer_no_admin_emails', false );
	
	return $settings;
} // nod_settings
?>