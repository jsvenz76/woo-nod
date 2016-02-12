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
 * Add NOD custom email to the WooCommerce emails
 *
 * @since	    0.0.1
 * @global		
 * @param		arr		$email_classes		Array of email classes WC loads
 * @return		arr		$email_classes		Filtered array of email classes WC loads
 */
function nod_add_email_class_to_wc( $email_classes )	{
	// Include the NOD Email custom email class.
    require( WC_NOD_PLUGIN_DIR. '/emails/nod-email.php' );
	
	// Add the NOD email class to the list of email classes that WooCommerce loads
    $email_classes['NOD_Email'] = new NOD_Email();
 
    return $email_classes;
} // nod_add_email_to_wc
add_filter( 'woocommerce_email_classes', 'nod_add_email_class_to_wc' );

/**
 * Return the count of all customer orders through WC.
 *
 * If no $user_id is provided, or no orders exist for the given user and an
 * email address is provided, we can query the PayPal address to query previous orders
 * to cover guest checkout.
 *
 * @param int $user_id		Optional: The user ID to query.
 * @param str $email		Optional: The user email address.
 * 
 * @since	0.0.1
 * @return	int		Total number of orders from this customer.
 */
function wc_nod_get_customer_purchase_count( $user_id='', $email='' )	{
	if( empty( $user_id ) && empty( $email ) )
		return false;

	$orders = 0;

	// Retrieve the WP user
	if( !empty( $user_id ) )
		$field = 'id';
		
	elseif( is_email( trim( $email ) ) )
		$field = 'email';
	
	if( empty( $field ) )
		return $orders;

	$user = get_user_by( $field, $user_id );
	
	// If we did not retrieve a user by ID and we have an email, try that.
	if( !$user && $field == 'id' && !empty( $email ) )
		$user = get_user_by( 'email', trim( $email ) );
		
	if( $user )
		$orders += nod_get_order_count_by( 'id', $user->ID );
		
	// If an email is provided query orders by email	
	if( !empty( $email ) )
		$orders += nod_get_order_count_by( 'email', trim( $email ) );

	return $orders;
} // wc_nod_get_customer_purchase_count

/**
 * Count all customer orders for the customer by the given field.
 *
 * Returns a count of customer orders.
 *
 * @param	int		$field		Optional: The field by which to query.
 * @param	str		$value		Required: The value to search $field for.
 * 
 * @since 0.0.1
 * @return int	The total number of orders for the customer.
 */
function nod_get_order_count_by( $field='id', $value )	{
	switch( $field )	{
		case 'id':
			$count = nod_get_order_count_by_id( $value );
		break;
		
		case 'email':
			$count = nod_get_order_count_by_email( $value );
		break;
	}
	return $count;
} // nod_get_order_count_by

/**
 * Count all customer orders for the registered customer by their ID.
 *
 * Returns a count of customer orders.
 *
 * @param	int		$user_id	Required: The user ID we are querying.
 * 
 * @since 0.0.1
 * @return int	The total number of orders for the customer.
 */
function nod_get_order_count_by_id( $user_id )	{
	$customer_orders = get_posts(
		array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $user_id,
			'post_type'   => wc_get_order_types(),
			'post_status' => array( 'wc-completed', 'wc-pending' )
    	)
	);
	
	return $customer_orders ? count( $customer_orders ) : 0;
} // nod_get_order_count_by_id

/**
 * Count all guest orders for the registered customer by their email address.
 *
 * Returns a count of customer orders.
 *
 * We need to confirm that the _customer_user meta key is set to 0.
 * Otherwise we may be counting duplicates for registered users
 *
 * @param	int		$email		Required: The email address we are querying.
 * 
 * @since 0.0.1
 * @return int	The total number of orders for the customer.
 */
function nod_get_order_count_by_email( $email )	{
	$customer_orders = get_posts(
		array(
			'numberposts'	=> -1,
			'post_type'		=> wc_get_order_types(),
			'post_status'	=> array( 'wc-completed', 'wc-pending' ),
			'meta_query'	=> array(
				'relation'	=> 'AND',
				array(
					'key'		=> '_billing_email',
					'value'		=> trim( $email ),
					'compare'	=> '='
				),
				array(
					'key'       => '_customer_user',
					'value'     => '0',
					'compare'   => '=',
					'type'      => 'NUMERIC'
				)
			)
		)
	);
	error_log( print_r( wc_get_order_types() ), 0 );
	return $customer_orders ? count( $customer_orders ) : 0;
} // nod_get_order_count_by_email

/**
 * Get NOD offer email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since 0.0.1
 * @return string $message
 */
function nod_get_default_offer_email()	{
	$default_email_body = sprintf( __( 'Hey %s,', 'wc-next-order-discount' ), '{name}' ) . "\n\n";
	
	$default_email_body .= sprintf( 
		__( 'To say thanks for your recent purchase at {site_title}, we are offering you a discount of %s off your next purchase.',
			'nod' ),
		'{offer_amount}' )
		 . "\n\n";
	
	$default_email_body .= sprintf(
		__( 'To claim this offer, simply enter the Discount Code %s%s%s during checkout on our website before the discount expires on %s.', 'wc-next-order-discount' ),
		'<strong>',
		'{offer_code}',
		'</strong>',
		'{offer_expiry}'
	) . "\n\n";
	
	$default_email_body .= __( 'Thank you', 'wc-next-order-discount' );

	$message = get_option( 'nod_offer_content', false );
	$message = ! empty( $message ) ? $message : $default_email_body;

	return $message;
} // nod_get_default_offer_email

/**
 * NOD Email Offer Template Body
 *
 * @since 0.0.1
 * @param	int		$discount_id	Required: Discount ID
 * @param	int		$payment_id		Optional: Payment ID
 * @param	array	$payment_data	Optional: Payment Data
 *
 * @return	string	$email_body		Body of the email
 */
function nod_get_email_body_content( $discount_id, $payment_id = 0, $payment_data = array() ) {
	$default_email_body = sprintf( __( 'Hey %s,', 'wc-next-order-discount' ), '{name}' ) . "\n\n";
	
	$default_email_body .= sprintf( 
		__( 'To say thanks for your recent purchase at {site_title}, we are offering you a discount of %s off your next purchase.',
			'nod' ),
		'{offer_amount}' )
		 . "\n\n";
	
	$default_email_body .= sprintf(
		__( 'To claim this offer, simply enter the Discount Code %s%s%s during checkout on our website before the discount expires on %s.', 'wc-next-order-discount' ),
		'<strong>',
		'{offer_code}',
		'</strong>',
		'{offer_expiry}'
	) . "\n\n";
	
	$default_email_body .= __( 'Thank you', 'wc-next-order-discount' );
	
	return apply_filters( 'nod_offer_email', $email_body, $discount_id, $payment_id, $payment_data );
} // nod_get_email_body_content

/**
 * Retrieves the emails for which admin NOD Offer notifications are sent to (these can be
 * changed in the WooCommerce Emails Settings NOD heading)
 *
 * @param	str		$headers	Existing email headers.
 *
 * @since 0.0.1
 * @return str		$headers	Filtered email headers.
 */
function nod_get_admin_notice_emails( $headers ) {
	if( !empty( WC_NOD()->settings->disable_admin ) )
		return $headers;
	
	$emails = WC_NOD()->settings->admin_emails;
	
	if( empty( $emails ) )
		return $headers;
	
	$emails = strlen( trim( $emails ) ) > 0 ? $emails : '';
	
	$emails = 'Bcc: ' . str_replace( "\n", ',', $emails ) . "\r\n";

	return apply_filters( 'nod_admin_notice_emails', $emails );
} // nod_get_admin_notice_emails

/**
 * Create array of NOD email tags which will be appended to the WooCommerce email tags.
 *
 * @params
 *
 * @since 0.0.1
 * @return 	arr		$email_tags		Array of NOD email tags.
 */
function nod_get_email_tags()	{
	$email_tags = array(
		array(
			'tag'			=> 'offer_code',
			'description'	=> __( 'The discount offer code that the customer should use for the offer', 'wc-next-order-discount' ),
			'function'		=> 'nod_email_tag_offer_code'
		),
		array(
			'tag'			=> 'offer_amount',
			'description'	=> __( 'The discount amount that the code provides', 'wc-next-order-discount' ),
			'function'		=> 'nod_email_tag_offer_amount'
		),
		array(
			'tag'			=> 'offer_expiry',
			'description'	=> __( 'The date the discount code expires', 'wc-next-order-discount' ),
			'function'		=> 'nod_email_tag_offer_expiry'
		)
	);
	return apply_filters( 'nod_email_tags', $email_tags );
} // nod_get_email_tags

/**
 * Create list of all available email tags
 *
 * @params
 *
 * @since 0.0.1
 * @return 	arr		$list		HTML list of all available email tags
 */
function nod_get_email_tags_list()	{
	$list = '';
	
	$email_tags = nod_get_email_tags();
	
	// Check
	if ( count( $email_tags ) > 0 ) {
		// Loop
		foreach ( $email_tags as $email_tag ) {
			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';
		}
	}
	
	return $list;
} // nod_get_email_tags_list

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param	str		$content		Content to search for email tags
 * @param	int		$discount_id	The discount code id
 *
 * @since 1.9
 *
 * @return	str		Content with email tags filtered out.
 */
function nod_do_email_tags( $content, $discount_id ) {
	$tags = nod_get_email_tags();
	
	// Check if there is at least one tag added and that we have a discount ID
	if ( empty( $tags ) || !is_array( $tags ) || empty( $discount_id ) )
		return $content;
		
	foreach( $tags as $tag )	{
		$search[] = '{' . $tag['tag'] . '}';
		$func = 'nod_email_tag_' . $tag['tag'];
		$replace[] = function_exists( $func ) ? $func( $discount_id ) : '';
	}
	
	$new_content = str_replace( $search, $replace, $content );

	return $new_content;
} // nod_do_email_tags

/**
 * Output the content for the {offer_code} email tag
 *
 * @param	int		$discount_id	The discount code ID
 *
 * @since 0.0.1
 * @return
 */
function nod_email_tag_offer_code( $discount_id )	{
	$code = get_post( $discount_id );
	
	return get_the_title( $code->ID );
} // nod_email_tag_offer_code

/**
 * Output the content for the {offer_amount} email tag as either a currency or percentage.
 *
 * @param	int		$discount_id	The discount code ID
 *
 * @since 0.0.1
 * @return	str		The correctly formatted discount offer amount.
 */
function nod_email_tag_offer_amount( $discount_id )	{
	$type = get_post_meta( $discount_id, 'type', true );
	$rate = get_post_meta( $discount_id, 'coupon_amount', true );
	
	switch( $type )	{
		case 'percent':
			$amount = $rate . '%';
		break;
		case 'fixed_cart':
			$amount = wc_price( $rate, array() );
		break;	
	}
	
	return $amount;
} // nod_email_tag_offer_amount

/**
 * Output the content for the {offer_expiry} email tag
 *
 * @param	int		$discount_id	The discount code ID
 *
 * @since 0.0.1
 * @return
 */
function nod_email_tag_offer_expiry( $discount_id )	{
	$expires = get_post_meta( $discount_id, 'expiry', true );
		
	return !empty( $expires ) ? date_i18n( get_option( 'date_format' ), strtotime( $expires ) ) : '';
} // nod_email_tag_offer_expiry

/**
 * Check if the current purchase is eligible for NOD offers.
 *
 * @param	int|arr		$items	Array of products included in the purchase.
 * @return	bool		true if this purchase is NOD eligible, false if not.
 *
 * @since 0.0.1
 * @return
 */
function wc_nod_product_is_eligible( $items )	{
	if( !is_array( $items ) )
		$items = array( $items );
		
	foreach( $items as $item )	{
		$exclude = get_post_meta( $item['product_id'], '_wc_exclude_from_nod', true );
		
		if( $exclude == 'no' )
			return true;
	}
	
	return false;
} // wc_nod_product_is_eligible

/**
 * Check if the users purchase qualifies for a NOD offer.
 *
 * @param	int			$purchase_count		Purchase count of customer.
 * @return	bool		true if this the customer is eligible, false if not.
 *
 * @since 0.0.4
 * @return
 */
function wc_nod_purchase_qualifies( $purchase_count )	{
	// First offer
	if( $purchase_count < WC_NOD()->settings->first )
		return false;
	
	if( $purchase_count == WC_NOD()->settings->first )
		return true;
	
	// Second offer	
	if( empty( WC_NOD()->settings->repeat ) )
		return false;
		
	if( $purchase_count == WC_NOD()->settings->repeat )
		return true;
		
	// Repeat offers
	if( $purchase_count % WC_NOD()->settings->repeat == 0 && get_option( 'nod_continue', false ) )
		return true;

	return false;
} // wc_nod_purchase_qualifies
?>