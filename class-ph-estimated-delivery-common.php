<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Common Functions class.
 */
if( ! class_exists("Ph_Estimated_Delivery_Common") ) {
	/**
	 * Holds Common Methods.
	 */
	class Ph_Estimated_Delivery_Common {

		/**
		 * Array of active plugins.
		 */
		private static $active_plugins;

		/**
		 * Initialize the active plugins.
		 */
		public static function init() {

			self::$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() )
				self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		/**
		 * Check whether woocommerce is active or not.
		 * @return boolean True if woocommerce is active else false.
		 */
		public static function woocommerce_active_check() {

			if ( ! self::$active_plugins ) self::init();

			return in_array( 'woocommerce/woocommerce.php', self::$active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
		}

		/**
		 * Get Wordpress local time.
		 * @return object DateTime Local Wordpress Time.
		 */
		public static function get_wordpress_time() {
			$time		= current_time('Y-m-d H:i:s');
			$timeobject = date_create( $time );
			return $timeobject;
		}

	}
}