<?php
	if( !defined( 'ABSPATH' ) ) 
		exit;
/**
 * Plugin Name: WooCommerce - Next Order Discount
 * Plugin URI: http://mikesplugins.co.uk
 * Description: Next Order Discount (NOD) is an extension to the WooCommerce WordPress plugin that enables shop owners to attract further purchases from their customers.
 * Version: 1.0.3
 * Date: 11 February 2016
 * Author: Mike Howard <mike@mikesplugins.co.uk>
 * Author URI: https://profiles.wordpress.org/mikeyhoward1977
 * Text Domain: wc-next-order-discount
 * Domain Path: /lang
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: WooCommerce, Discount, Coupon, Discount Code, Coupon Code
 */
/**
   Next Order Discount is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License, version 2, as 
   published by the Free Software Foundation.

   Next Order Discount is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Next Order Discount; if not, see https://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Class: WC_NOD
 * Description: The main Next Order Discount (singleton) class
 *
 *
 */
 
if ( ! class_exists( 'WC_NOD' ) ) :
	class WC_NOD	{
		private static $instance;
		
		public $settings; // The NOD settings will be stored in the WC_NOD()->settings object
		/**
		 * Run during plugin activation. Check for existance of version key and execute install procedures
		 * if it does not exist. Otherwise simply return.
		 * 
		 *
		 *
		 *
		 */
		public static function activate()	{
			// If this is the first time activated, call install procesures
			if( !get_option( 'nod_version' ) )
				include( WC_NOD_PLUGIN_DIR. '/procedures/nod-install.php' );
				
			// Register our scheduled event hooks
			if( !wp_next_scheduled( 'woo_nod_schedule_next_order_discount' ) )
				wp_schedule_event( time(), 'hourly', 'woo_nod_schedule_next_order_discount' );
			
			// Make sure our email templates exist.	
			$templates = array(
				"/customer-nod-offer.php",
				"/plain/customer-nod-offer.php"
			);
			$dir = WC_NOD_PLUGIN_DIR. '/email_templates';
			$wc_dir = str_replace( 'woocommerce.php', '', WC_PLUGIN_FILE ) . 'templates/emails';
				
			foreach( $templates as $template )	{
				if( !file_exists( $wc_dir . $template ) )
					copy( $dir . $template, $wc_dir . $template );
			}
		} // activate
		
		/**
		 * Run during plugin deactivation.
		 * 
		 * 
		 * 
		 *
		 *
		 */
		public static function deactivate()	{
			// Clear the scheduled event hooks when the plugin is deactivated.
			wp_clear_scheduled_hook( 'woo_nod_schedule_next_order_discount' );
		} // deactivate
		
		/**
		 * Load the text domain.
		 *
		 *
		 *
		 */
		private function load_text_domain()	{
			load_plugin_textdomain( 
				'edd-nod',
				false, 
				dirname( plugin_basename(__FILE__) ) . '/lang/' );
		} // load_text_domain
		
		/**
		 * Let's ensure we only have one instance of NOD loaded into memory at any time
		 *
		 *
		 *
		 * @return The one true NOD
		 */
		public static function instance()	{
			if( ! isset( self::$instance ) && ! ( self::$instance instanceof WC_NOD ) ) {
				self::$instance = new WC_NOD();
				
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_text_domain();
				self::$instance->check_update();
				self::$instance->settings		= nod_settings();
				self::$instance->discounts		= new NOD_Discounts();
				self::$instance->email_offers	= new NOD_Email_Offers();			
			}
			
			return self::$instance;
		} // instance
		
		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * 
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-next-order-discount' ), '1.0.1' );
		} // __clone
		
		/**
		 * Define constants
		 *
		 *
		 *
		 */
		private function setup_constants()	{
			
		} // define_constants
				
		/**
		 * Files for inclusion
		 *
		 *
		 *
		 */
		private function includes()	{
			require_once( WC_NOD_PLUGIN_DIR. '/includes/nod-functions.php' );
			require_once( WC_NOD_PLUGIN_DIR. '/includes/nod-settings.php' );
			require_once( WC_NOD_PLUGIN_DIR. '/includes/metabox.php' );
			require_once( WC_NOD_PLUGIN_DIR. '/discounts/nod-discounts.php' );
			require_once( WC_NOD_PLUGIN_DIR. '/emails/nod-email-offers.php' );
		} // includes
		
		/**
		 * Plugin updates.
		 *
		 *
		 *
		 */
		public function check_update()	{
			$version = get_option( WC_NOD_VER_KEY, true );
			if( version_compare( WC_NOD_VER, $version, '>' ) )	{
				require_once( 'procedures/nod-updates.php' );
				nod_do_updates( $version );
			}
		} // check_update
	} //class WC_NOD
endif;

/**
 * Instantiate the NOD singleton class and return the one and only instance.
 *
 *
 *
 */
function WC_NOD()	{
	return WC_NOD::instance();
} // NOD

function WC_NOD_Load()	{
	if( class_exists( 'WooCommerce' ) )
		WC_NOD();
}

add_action( 'plugins_loaded', 'WC_NOD_Load' );

define( 'WC_NOD_VER_KEY', 'woo_nod_version');
define( 'WC_NOD_VER', '1.0.3' );
define( 'WC_NOD_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'WC_NOD_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

register_activation_hook( __FILE__, array( 'WC_NOD', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_NOD', 'deactivate' ) );