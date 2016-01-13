<?php
/**
 * Customer NOD Offer email (plain text)
 *
 * @author 		Mike Howard
 * @package 	WC_NOD/email_templates
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo $email_heading . "\n\n";

echo sprintf(
		__( 'Hi there. To say thanks for your recent purchase at {site_title}, we are offering you a discount of {offer_amount} off your next purchase.',
		'woo-nod' ),
		get_option( 'blogname' )
	) . "\n\n";
	
echo sprintf(
		__( 'To claim this offer, simply enter the Discount Code {offer_code} during checkout on our website before the discount expires on %s{offer_expiry}%s.',
		'woo-nod' ),
		'<strong>',
		'</strong>'
	) . "\n\n";
	
echo __( 'Thank you', 'woo-nod' );

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
