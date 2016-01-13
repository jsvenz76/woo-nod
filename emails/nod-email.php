<?php
/**
 * NOD Email
 *
 * Add the NOD email class and template to the WC loaded email classes.
 *
 * The primary purpose of this class is define the NOD email template and settings within WC.
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
 * NOD Email Class
 *
 * @package     NOD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.0.1
 */
if( !class_exists( 'NOD_Email' ) ) :
	class NOD_Email extends WC_Email	{
		public $object;
		/**
		 * Class constructor
		 */
		public function __construct()	{
			
			// Set the template ID.
			$this->id = 'wc_nod_offer';
		 
			// Set the title WooCommerce Email settings.
			$this->title = __( 'Next Order Discount', 'woo-nod' );
		 
			// Set the description in WooCommerce email settings.
			$this->description = sprintf(
				__( 'Next Order Discount emails are sent following a customer order in accordance with your %sNOD Settings%s.',
				'woo-nod' ),
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=nod_settings_tab' ) . '">',
				'</a>'
			);
		 
			// Default heading and subject lines that can be overridden using the settings.
			$this->heading = WC_NOD()->settings->email_heading;
			$this->subject = WC_NOD()->settings->email_subject;
			
			// Define the locations of the templates that this email should use.
			$this->template_html  = 'emails/customer-nod-offer.php';
			$this->template_plain = 'emails/plain/customer-nod-offer.php';
			
			parent::__construct();			
		} // __construct
		
		/**
		 * Retrieve the email content in HTML.
		 *
		 * @since 0.0.1
		 * @return	str
		 */
		public function get_content_html() {
			ob_start();
			woocommerce_get_template( $this->template_html, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading()
			) );
			return ob_get_clean();
		} // get_content_html
		
		/**
		 * Retrieve the email content in plain text.
		 *
		 * @since 0.1
		 * @return string
		 */
		public function get_content_plain() {
			ob_start();
			woocommerce_get_template( $this->template_plain, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading()
			) );
			return ob_get_clean();
		} // get_content_plain
		
		/**
		 * Initialise the NOD email settings options.
		 *
		 *
		 * @since 0.0.1
		 *
		 */
		public function init_form_fields()	{
			$this->form_fields = array(
				'subject'            => array(
					'id'          => 'subject',
					'title'       => __( 'Offer Email Subject', 'woo-nod' ),
					'description' => __( 'Enter the subject line for the NOD email.', 'woo-nod' ),
					'type'		  => 'text',
					'placeholder' => '',
					'default'     => WC_NOD()->settings->email_subject
				),
				'heading'           => array(
					'id'          => 'heading',
					'title'       => __( 'Offer Email Heading', 'woo-nod' ),
					'description' => __( 'Enter the heading for the NOD email.', 'woo-nod' ),
					'type'        => 'text',
					'placeholder' => '',
					'default'     => WC_NOD()->settings->email_heading
				),
				'email_type'        => array(
					'id'          => 'email_type',
					'title'       => __( 'Email type', 'woo-nod' ),
					'type'		=> 'select',
					'description' => __( 'Choose which format of email to send.', 'woo-nod' ),
					'default'	 => WC_NOD()->settings->email_type,
					'class'		=> 'wc-enhanced-select',
					'options'	  => array(
						'plain'     => __( 'Plain text', 'woo-nod' ),
						'html'      => __( 'HTML', 'woo-nod' ),
						'multipart' => __( 'Multipart', 'woo-nod' )
					)
				),
				'admin_emails' => array(
					'id'          => 'admin_emails',
					'title'       => __( 'NOD Notification Emails', 'woo-nod' ),
					'description' => __( 'Enter the email address(es) that should receive a notification anytime a NOD offer is made, one per line.', 'woo-nod' ),
					'type'		=> 'textarea',
					'css'		=> 'width:200px; height: 75px;',
					'default'	=> WC_NOD()->settings->admin_emails
				),
				'no_admin_emails' => array(
					'id'          => 'no_admin_emails',
					'title'       => __( 'Disable Admin Notifications', 'woo-nod' ),
					'description' => __( 'Check this box if you do not want NOD offers to be blind copied to admins. Will overide NOD Notification Emails.', 'woo-nod' ),
					'type'		=> 'checkbox',
					'default'	=> WC_NOD()->settings->disable_admin
				),
			);
		} // init_form_fields
	} // NOD_Email class
endif;
