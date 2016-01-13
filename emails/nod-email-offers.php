<?php
/**
 * NOD Email Offers
 *
 * Generates the Next Order Discount email.
 *
 * The primary purpose of this class is to hook into the woo_nod_schedule_next_order_discount scheduled task
 * and determine which discount offers are due to be sent to the customers.
 * Offers that are due will generate a new discount code and include that code within the email.
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
 * NOD_Email_Offers Class
 *
 * 
 *
 * @since  0.0.1
 */
if( !class_exists( 'NOD_Email_Offers' ) ) :
	if( !class_exists( 'WC_Email' ) )
		// Instantiate the WC Email class
		require_once( str_replace( 'woocommerce.php', '', WC_PLUGIN_FILE ) . 'includes/emails/class-wc-email.php' );
	
	class NOD_Email_Offers extends WC_Email	{
		/**
		 * Class constructor
		 */
		public function __construct()	{
			// Process the scheduled task for NOD offers.			
			add_action( 'woo_nod_schedule_next_order_discount', array( &$this, 'process_nods' ) );
		} // __construct
		
		/**
		 * Runs during the woo_nod_schedule_next_order_discount hook.
		 *
		 * If the NOD is due, generate the discount code, email content and send the email
		 *
		 * @hook woo_nod_schedule_next_order_discount 
		 * @params
		 *
		 *
		 *
		 * @return	void
		 */
		public function process_nods()	{
			$nods = WC_NOD()->discounts->pending_nods;
			// No NODs to process? return
			if( empty( $nods ) )
				return;
			
			if( !class_exists( 'NOD_Email' ) )	{
				require_once( WP_PLUGIN_DIR . '/' . str_replace( 'woocommerce.php', '', WC_PLUGIN_BASENAME ) . 'includes/emails/class-wc-email.php' );
				require_once( WP_PLUGIN_DIR . '/' . str_replace( 'woocommerce.php', '', WC_PLUGIN_BASENAME ) . 'includes/libraries/class-emogrifier.php' );
				require_once( 'nod-email.php' );
			}
			$email = new NOD_Email();
			
			foreach( $nods as $order_id => $nod )	{
				// If the offer is not scheduled yet, skip it
				if( !$this->nod_is_ready( $nod ) )
					continue;
					
				$email->object = new WC_Order( $order_id );
				
				// No data, remove the record and continue
				if( empty( $email->object ) )	{
					WC_NOD()->discounts->delete_nod( $order_id );
					continue;
				}
				
				// Create the discount code
				$discount = WC_NOD()->discounts->generate_discount( $order_id );
				
				if( empty( $discount ) )
					return;
				
				$email_args = $this->prepare_email( $nod, $discount, $email );
				
				apply_filters( 'nod_email_offer_args', $email_args, $nod, $discount );
				
				add_filter( 'woocommerce_email_headers', array( &$this, 'headers' ), 10, 3 );
				
				$headers = $email->get_headers();
				$headers = apply_filters( 'nod_offer_headers', $headers, $nod['order_id'] );
				
				do_action( 'nod_before_send_offer', $email_args, $nod, $discount );
				
				$is_sent = $email->send(
					$email_args['to_email'],
					$email_args['subject'],
					$email_args['message'],
					$headers,
					$email_args['attachments']
				);
				
				do_action( 'nod_after_send_offer', $email_args, $nod, $discount );
				
				// If the email was sent, we can remove this instance from pending NODs
				if( !empty( $is_sent ) )
					WC_NOD()->discounts->delete_nod( $order_id );
			}
		} // process_nods
	
		/**
		 * Determine if the pending offer is ready to process by comparing the send date with current time.
		 * 
		 * @params
		 *
		 * @return	bool	true if the offer is ready to process, false if not.
		 */
		public function nod_is_ready( $nod )	{
			return strtotime( $nod['send_offer'] ) < current_time( 'timestamp' ) ? true : false;
		} // nod_is_ready
		
		/**
		 * Prepare the NOD email content.
		 * 
		 * @param	arr		$nod			Required: The NOD offer data
		 * @param	int		$discount_id	Required: The NOD offer discount ID
		 *
		 * @return	arr		Array of prepared email data
		 */
		public function prepare_email( $nod, $discount_id, $email )	{
			$to_email     = $email->object->billing_email;
			
			$subject      = apply_filters( 'nod_offer_subject', wp_strip_all_tags( $email->get_subject() ), $nod['order_id'] );
			$subject      = $email->format_string( $subject );
			$subject      = nod_do_email_tags( $subject, $discount_id );
			
			$heading      = $email->get_heading();
			$heading      = nod_do_email_tags( $heading, $discount_id );
			$email->heading = apply_filters( 'nod_offer_heading', $heading, $nod['order_id'] );
					
			$attachments  = apply_filters( 'nod_offer_attachments', $email->get_attachments(), $nod['order_id'] );
			
			$message      = $email->format_string( $email->get_content() );
			$message      = nod_do_email_tags( $message, $discount_id );
						
			return array(
				'to_email'      => $to_email,
				'subject'       => $subject,
				'attachments'   => $attachments,
				'message'       => $message
			);
		} // prepare_email
		
		/**
		 * Adds additional headers to the email.
		 *
		 * Used to send a blind copy of the email to the specified addresses.
		 *
		 * @param	str		$headers	The existing email headers
		 *
		 * @return	str		The filtered email headers
		 */
		public function headers( $headers, $id, $wc_email )	{
			return $id == 'wc_nod_offer' ? nod_get_admin_notice_emails( $headers ) : $headers;
		} // headers
		
	} // class NOD_Email_Offers
endif;