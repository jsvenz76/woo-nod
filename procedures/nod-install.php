<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/**
 * Class: NOD_Install
 * 
 * 
 * The installation procedures for NOD
 */
if( !class_exists( 'NOD_Install' ) ) :
	class NOD_Install	{
		/**
		 * Initialise and execute the install procedures
		 */
		public static function init()	{
			// Add the version key for NOD
			add_option( WC_NOD_VER_KEY, WC_NOD_VER );			
		} // init
		
	} // class NOD_Install
endif;
	NOD_Install::init();