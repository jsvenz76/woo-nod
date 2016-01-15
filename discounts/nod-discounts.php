<?php
/**
 * NOD Discount Management
 *
 * This class manages creation and querying of the WooCommerce Coupons.
 *
 * @package     NOD
 * @subpackage  
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.0.1
 */
 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
	exit;

/**
 * NOD_Discounts Class
 *
 * 
 *
 * @since  0.0.1
 */
if( !class_exists( 'NOD_Discounts' ) ) :
	class NOD_Discounts {
		public $pending_nods; // Our array of pending offers to send	
		/**
		 * Class constructor
		 */
		public function __construct()	{
			
			$this->pending_nods = get_option( 'woo_nod_pending_offers' );
						
			// Hook into the woocommerce_payment_complete hook to process new purchases.
			add_action( 'woocommerce_payment_complete', array( &$this, 'new_purchase' ) );
		} // __construct
		
		/**
		 * Adds a new NOD so that we know to process during our scheduled task.
		 *
		 * @param	arr		$data	Required: Data to be included within the NOD
		 *
		 *
		 */
		public function add_nod( $data )	{
			if( empty( $data['order_id'] ) )
				return false;
			
			if( !empty( $this->pending_nods ) && array_key_exists( $data['order_id'], $this->pending_nods ) )
				return false;
				
			$this->pending_nods[$data['order_id']] = $data;
			
			$update = update_option( 'woo_nod_pending_offers', $this->pending_nods );
		} // add_nod
		 
		 /**
		 * Delete a NOD so that is not processed again.
		 *
		 * @param	int		$download_id	The ID of the download which is the array key of the NOD entry
		 *
		 *
		 */
		public function delete_nod( $payment_id )	{
			if( !array_key_exists( $payment_id, $this->pending_nods ) )
				return;
				
			unset( $this->pending_nods[$payment_id] );
			update_option( 'woo_nod_pending_offers', $this->pending_nods );
		} // delete_nod
		
		/**
		 * Runs once a purchase is completed.
		 *
		 * If NOD is enabled, store the purchase details into an array we can use during the offer email schedule
		 *
		 * @hook woocommerce_payment_complete 
		 * @params	int		$order_id			Required: Purchase ID
		 *
		 * @return	void
		 */
		public function new_purchase( $order_id )	{
			// Exit here if the plugin is not enabled
			if( empty( WC_NOD()->settings ) || empty( WC_NOD()->settings->enable ) )
				return;
			
			// WC Order object
			$order = new WC_Order( $order_id );
			
			if( !$order )
				return;
			
			$items = $order->get_items();
			
			// If this purchase is not eligible for NOD offers, exit
			if( !wc_nod_product_is_eligible( $items ) )
				return;
			
			// If we do not apply offers to free downloads, check here
			if( empty( WC_NOD()->settings->free ) && $order->get_total > '0' )
				return;
			
			// If there is a minimum spend, check here
			$min = WC_NOD()->settings->min_spend;
			if( !empty( $min ) && $min < $order->get_total )
				return;
							
			// Check if the customer is eligible for a NOD offer.
			$customer_id = $order->customer_user;
			
			$customer       = '';
			$customer_email = get_post_meta( $order_id, '_billing_email', true );
			$customer       = get_user_by( 'id', $customer_id );
			
			$purchase_count = 0;
			$purchase_count = wc_nod_get_customer_purchase_count( $customer_id, $customer_email );
						
			if( !wc_nod_purchase_qualifies( $purchase_count ) )
				return;
			
			// Before adding the new NOD
			do_action( 'nod_before_register', $order_id, $customer_id );
			
			// Add this download to our pending offers array
			$valid_from = date( 'Y-m-d H:i:s', strtotime( "+" . WC_NOD()->settings->send_after ) );
			$this->add_nod(
				array(
					'order_id'          => $order_id,
					'customer_id'       => $customer_id,
					'total_cost'		=> $order->get_total(),
					'send_offer'		=> date( 'Y-m-d H:i:s', strtotime( "+" . WC_NOD()->settings->send_after ) )
				)
			);
			
			// After adding the new NOD
			do_action( 'nod_after_register', $order_id, $customer_id );
		} // new_purchase
		
		/**
		 * Create the NOD discount.
		 *
		 * This function generates the WooCommerce Discount for the NOD offer using the preferences
		 * that admin has defined in settings for expiry etc.
		 * 
		 * @params	int		$purchase_id	Required: The WooCommerce purchase ID
		 *
		 * @return	int|bool		The discount ID if successful, false on failure.
		 */
		public function generate_discount( $order_id )	{
			$code = $this->generate_code();
			
			$order = new WC_Order( $order_id );
			$customer_id = $order->customer_user;
			
			// Make sure the code does not already exist, otherwise create another
			if( get_page_by_title( $code, '', 'shop_coupon' ) )
				$code = $this->generate_code();
			
			// Create the meta for the discount code
			$discount_args = apply_filters(
				'nod_discount_args',
				array(
					'type'                   => WC_NOD()->settings->type,
					'individual'             => 'yes',
					'customer_email'         => empty( $customer_id ) ? get_post_meta( $order_id, '_billing_email', true ) : '',
					'coupon_amount'          => WC_NOD()->settings->rate,
					'expiry'	             => date( 'm/d/Y', strtotime( "+" . WC_NOD()->settings->expires ) ),
					'usage_limit'            => '1',
					'usage_limit_per_user'   => !empty( $customer_id ) ? $customer_id : get_post_meta( $order_id, '_billing_email', true )
				),
				$code,
				$order_id
			);
			
			// Before adding the new NOD discount code
			do_action( 'nod_before_nod_discount_code', $discount_args );
			
			// Create the discount code. Return if unsuccessful.
			$discount_id = wp_insert_post(
				array(
					'post_title'        => $code,
					'post_content'      => '',
					'post_status'       => 'publish',
					'post_author'       => 1,
					'post_type'         => 'shop_coupon'
				)
			);
			
			if( empty( $discount_id ) )
				return false;
				
			foreach( $discount_args as $key => $value )	{
				update_post_meta( $discount_id, $key, $value );
			}
			
			// After adding the new NOD discount code
			do_action( 'nod_after_nod_discount_code', $discount_id, $discount_args );
			
			return $discount_id;
		} // generate_discount
		
		/**
		 * Create the unique NOD discount code.
		 *
		 * This function generates a new discount code to be used when creating the discount.
		 * 
		 * @params
		 *
		 * @return	str		The newly generated code
		 */
		public function generate_code()	{
			// Generate a new code using the defined prefix and random numbers/letters.
			$code = WC_NOD()->settings->prefix . substr( str_shuffle( "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" ), 0, 4 );
			
			return apply_filters( 'nod_generate_code', $code, WC_NOD()->settings->prefix );
		} // generate_code
		
		
	} // NOD_Discounts class
endif;