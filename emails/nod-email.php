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
			$this->title = __( 'Next Order Discount', 'wc-next-order-discount' );
		 
			// Set the description in WooCommerce email settings.
			$this->description = sprintf(
				__( 'Next Order Discount emails are sent following a customer order in accordance with your %sNOD Settings%s.',
				'wc-next-order-discount' ),
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
		 * Retrieve the email header content.
		 *
		 * @since	0.0.1
		 * @param	str		$email_heading		Required: The heading text string.
		 * @param	str		$type				Required: The email type.
		 * @return	str
		 */
		public function get_email_header( $email_heading, $type )	{
			$html = '<!DOCTYPE html>' . "\r\n";
			$html .= '<html dir="' . ( is_rtl() ? 'rtl' : 'ltr' ) . '">' . "\r\n";
			$html .= '<head>' . "\r\n";
			$html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . "\r\n";
			$html .= '<title>' . get_bloginfo( 'name', 'display' ) . '</title>' . "\r\n";
			$html .= '</head>' . "\r\n";
			$html .= '<body ' . ( is_rtl() ? 'rightmargin' : 'leftmargin' ) . '="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">' . "\r\n";
			$html .= '<div id="wrapper" dir="' . ( is_rtl() ? 'rtl' : 'ltr' ) . '">' . "\r\n";
			$html .= '<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td align="center" valign="top">' . "\r\n";
			$html .= '<div id="template_header_image">' . "\r\n";

			if ( $img = get_option( 'woocommerce_email_header_image' ) )
				$html .= '<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>' . "\r\n";

			$html .= '</div>' . "\r\n";
			$html .= '<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td align="center" valign="top">' . "\r\n";
			$html .= '<!-- Header -->' . "\r\n";
			$html .= '<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header">' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td id="header_wrapper">' . "\r\n";
			$html .= '<h1>' . $email_heading . '</h1>' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '</table>' . "\r\n";
			$html .= '<!-- End Header -->' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td align="center" valign="top">' . "\r\n";
			$html .= '<!-- Body -->' . "\r\n";
			$html .= '<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td valign="top" id="body_content">' . "\r\n";
			$html .= '<!-- Content -->' . "\r\n";
			$html .= '<table border="0" cellpadding="20" cellspacing="0" width="100%">' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td valign="top">' . "\r\n";
			$html .= '<div id="body_content_inner">' . "\r\n";
						
			return $type == 'html' ? $html : $email_heading;
		}
		
		/**
		 * Retrieve the email footer content.
		 *
		 * @since	0.0.1
		 * @param	str		$type		Required: The email type.
		 * @return	str
		 */
		public function get_email_footer( $type )	{
			$html = '</div>' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '</table>' . "\r\n";
			$html .= '<!-- End Content -->' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '</table>' . "\r\n";
			$html .= '<!-- End Body -->' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td align="center" valign="top">' . "\r\n";
			$html .= '<!-- Footer -->' . "\r\n";
			$html .= '<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td valign="top">' . "\r\n";
			$html .= '<table border="0" cellpadding="10" cellspacing="0" width="100%">' . "\r\n";
			$html .= '<tr>' . "\r\n";
			$html .= '<td colspan="2" valign="middle" id="credit">' . "\r\n";
			$html .= wpautop( wp_kses_post( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) );
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '</table>' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '</table>' . "\r\n";
			$html .= '<!-- End Footer -->' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '</table>' . "\r\n";
			$html .= '</td>' . "\r\n";
			$html .= '</tr>' . "\r\n";
			$html .= '</table>' . "\r\n";
			$html .= '</div>' . "\r\n";
			$html .= '</body>' . "\r\n";
			$html .= '</html>' . "\r\n";
			
			return $type == 'html' ? $html : '';
		} // get_email_footer
				
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
					'title'       => __( 'Offer Email Subject', 'wc-next-order-discount' ),
					'description' => __( 'Enter the subject line for the NOD email.', 'wc-next-order-discount' ),
					'type'		  => 'text',
					'placeholder' => '',
					'default'     => WC_NOD()->settings->email_subject
				),
				'heading'           => array(
					'id'          => 'heading',
					'title'       => __( 'Offer Email Heading', 'wc-next-order-discount' ),
					'description' => __( 'Enter the heading for the NOD email.', 'wc-next-order-discount' ),
					'type'        => 'text',
					'placeholder' => '',
					'default'     => WC_NOD()->settings->email_heading
				),
				'email_type'        => array(
					'id'          => 'email_type',
					'title'       => __( 'Email type', 'wc-next-order-discount' ),
					'type'		=> 'select',
					'description' => __( 'Choose which format of email to send.', 'wc-next-order-discount' ),
					'default'	 => WC_NOD()->settings->email_type,
					'class'		=> 'wc-enhanced-select',
					'options'	  => array(
						'plain'     => __( 'Plain text', 'wc-next-order-discount' ),
						'html'      => __( 'HTML', 'wc-next-order-discount' ),
						'multipart' => __( 'Multipart', 'wc-next-order-discount' )
					)
				),
				'admin_emails' => array(
					'id'          => 'admin_emails',
					'title'       => __( 'NOD Notification Emails', 'wc-next-order-discount' ),
					'description' => __( 'Enter the email address(es) that should receive a notification anytime a NOD offer is made, one per line.', 'wc-next-order-discount' ),
					'type'		=> 'textarea',
					'css'		=> 'width:200px; height: 75px;',
					'default'	=> WC_NOD()->settings->admin_emails
				),
				'no_admin_emails' => array(
					'id'          => 'no_admin_emails',
					'title'       => __( 'Disable Admin Notifications', 'wc-next-order-discount' ),
					'description' => __( 'Check this box if you do not want NOD offers to be blind copied to admins. Will overide NOD Notification Emails.', 'wc-next-order-discount' ),
					'type'		=> 'checkbox',
					'default'	=> WC_NOD()->settings->disable_admin
				),
			);
		} // init_form_fields
	} // NOD_Email class
endif;
