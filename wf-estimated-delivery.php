<?php
/*
Plugin Name: Estimated Delivery Date for Woocommerce (Basic)
Plugin URI: https://www.xadapter.com/product/estimated-delivery-date-plugin-woocommerce/
Description: Intuitive order delivery date plugin using which you can set delivery dates for your orders based on shipping class and a host of other features.
Version: 1.3.0
Author: PluginHive
Author URI: https://www.pluginhive.com/
License: GPLv2
WC requires at least: 2.6.0
WC tested up to: 3.4
Text Domain: estimated-delivery-woocommerce
*/

//Plus version: 1.5.3

if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
}

if( ! class_exists('Ph_Estimated_Delivery_Common') ) {
	require_once 'class-ph-estimated-delivery-common.php';
}

function wf_basic_pre_activation_check(){
    if ( is_plugin_active('wf-estimated-delivery-for-woocommerce/wf-estimated-delivery.php') ){
        deactivate_plugins( basename( __FILE__ ) );
        wp_die(__("Is everything fine? You already have the Premium version installed in your website. For any issues, kindly raise a ticket via <a target='_blank' href='//support.xadapter.com/'>support.xadapter.com</a>", "estimated-delivery-woocommerce"), "", array('back_link' => 1 ));
    }
}

register_activation_hook( __FILE__, 'wf_basic_pre_activation_check' );

if( Ph_Estimated_Delivery_Common::woocommerce_active_check() ) {
	
	//Class - To setup the plugin
	if( ! class_exists('Wf_Estimated_Delivery_Setup') ){
		class Wf_Estimated_Delivery_Setup {
				//constructor
			public function __construct() {
				$this->wf_estimated_delivery_init();
				add_action( 'woocommerce_get_settings_pages',array($this, 'wf_estimated_delivery_initialize') );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wf_estimated_delivery_plugin_action_links' ) );
			}

			public function wf_get_settings_url(){
					return version_compare(WC()->version, '1.0', '>=') ? "wc-settings" : "woocommerce_settings";
				}
				
			//to add settings url near plugin under installed plugin
			public function wf_estimated_delivery_plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=' . $this->wf_get_settings_url() . '&tab=wf_estimated_delivery' ) . '">' . __( 'Settings', 'estimated-delivery-woocommerce' ) . '</a>',
					'<a href="https://www.xadapter.com/category/product/estimated-delivery-date-plugin-for-woocommerce/" target="_blank">' . __( 'Documentation', 'estimated-delivery-woocommerce' ) . '</a>',
					'<a href="https://www.xadapter.com/support/forum/delivery-estimate-plugin-woocommerce/" target="_blank">' . __( 'Support', 'estimated-delivery-woocommerce' ) . '</a>',

				);
				return array_merge( $plugin_links, $links );
			} 

			public function wf_estimated_delivery_initialize(){
				include_once( 'includes/class-wf-estimated-delivery-settings.php' );
			}
			//to include the necessary files for plugin
			public function wf_estimated_delivery_init() {
				load_plugin_textdomain( 'estimated-delivery-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
				include_once( 'includes/class-wf-estimated-delivery.php' );
				include_once( 'includes/log.php' );
			}		
		}
	}	

	new Wf_Estimated_Delivery_Setup();
}


require 'plugin-update-checker-4.4/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'http://example.com/path/to/details.json',
	__FILE__, //Full path to the main plugin file or functions.php.
	'estimated-delivery-woocommerce'
);
error_log(plugin_dir_url(__FILE__));