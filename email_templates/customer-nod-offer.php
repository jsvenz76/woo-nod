<?php
/**
 * Customer NOD Offer email
 *
 * @author 		Mike Howard
 * @package 	WC_NOD/email_templates
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text ); ?>

<p><?php printf(
			__( 'Hi there. To say thanks for your recent purchase at {site_title}, we are offering you a discount of {offer_amount} off your next purchase.',
			'wc-next-order-discount' ),
			get_option( 'blogname' )
		);
	?></p>

<p><?php printf(
			__( 'To claim this offer, simply enter the Discount Code {offer_code} during checkout on our website before the discount expires on %s{offer_expiry}%s.',
			'wc-next-order-discount' ),
			'<strong>',
			'</strong>'
		);
	?></p>

<p><?php _e( 'Thank you', 'wc-next-order-discount' ); ?></p>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>
