<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wf_Estimated_Delivery_Settings extends WC_Settings_Page {

	public function __construct() {	
		$this->id		= 'wf_estimated_delivery';
		$this->label	= __( 'Estimated Delivery', 'estimated-delivery-woocommerce' );

		$shipping_class_dates		= get_option( 'wf_estimated_delivery_shipping_class' );
		$this->shipping_class_dates = !empty( $shipping_class_dates ) ? $shipping_class_dates : array();
		
		add_filter( 'woocommerce_settings_tabs_array',		array( $this, 'add_settings_page' ), 21 );
		add_action( 'woocommerce_sections_' . $this->id,	  array( $this, 'output_sections' ) );

		add_action( 'woocommerce_settings_' . $this->id,	  array( $this, 'wf_estimated_delivery_output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'wf_estimated_delivery_save' ) );

		add_action( 'woocommerce_update_options_wf_estimated_delivery', array( $this, 'wf_estimated_delivery_update_settings') );

		add_action('woocommerce_admin_field_shipping_class',array( $this, 'generate_shipping_class_html'));
		add_action('woocommerce_admin_field_day_limits',array( $this, 'generate_day_limits_html'));
		add_action( 'current_screen', array( $this,'wf_estimated_delivery_this_screen' ));
		add_action( 'wp_footer', array( $this, 'wf_estimated_delivery_scripts' ) );			
	}
	
	public function get_sections() {  
		include_once("market.php");
		$sections = array(
			''						=> __( 'General Settings', 'estimated-delivery-woocommerce' ),
			'wf_shipping_class'		=> __( 'Shipping Class', 'estimated-delivery-woocommerce' ),
		);			   
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	public function wf_estimated_delivery_update_settings( $current_section ) {
		global $current_section;
		switch($current_section) {
			case '':
				$options = $this->wf_estimated_delivery_get_settings();
			
			case 'wf_shipping_class':
				$options = $this->wf_estimated_delivery_get_settings();
				break;
		}
	}

	public function wf_estimated_delivery_get_settings( $current_section = '' ) {
		global $current_section;
		switch($current_section){
			case '':
				$settings = apply_filters( 'wf_estimated_delivery_section1_settings', array(
					
					'date_options_title'	=>	array(
						'name' => __( 'General Settings', 'estimated-delivery-woocommerce' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'wf_estimated_delivery_date_options_title',
					),
					'min_delivery_days'	=>	array(
						'type'	 		=> 'text',
						'id'	   		=> 'wf_estimated_delivery_min_delivery_days',
						'name'	 		=> __( 'Minimum Delivery Days', 'estimated-delivery-woocommerce' ),
						'desc'	 		=> __( 'Enter the minimum number of days for the delivery of all the products.','estimated-delivery-woocommerce'),
						'desc_tip'		=> true,
						'default'  		=> '0',
					),				
					'day_limits'		=>	array(
						'type'	 		=> 'day_limits',
						'id'	   		=> 'wf_estimated_delivery_day_limits',
						'desc_tip'		=> true,
						'value' 		=> 'abc',
						'default'  		=> 'Monday',
					),
					'operation_days' => array(
						'type'	  => 'multiselect',
						'css' 	  => 'padding: 0px;',
						'id'	  => 'wf_estimated_delivery_operation_days',
						'name'	  => __( 'Working Days', 'estimated-delivery-woocommerce' ),
						'desc'	  => __( 'Select the working days of the week and according to these days the date of delivery can be estimated.','estimated-delivery-woocommerce' ),
						'class'   => 'chosen_select',
						'desc_tip'		=> true,
						'default' => array('mon','tue','wed','thu','fri'), //If changed here chage abstract-class-calc-est-stratergy.php also.
						'options' => array(
							'mon'  => __('Monday','estimated-delivery-woocommerce' ),
							'tue'  => __('Tuesday','estimated-delivery-woocommerce' ),
							'wed'  => __('Wednesday','estimated-delivery-woocommerce' ),
							'thu'  => __('Thursday','estimated-delivery-woocommerce' ),
							'fri'  => __('Friday','estimated-delivery-woocommerce' ),
							'sat'  => __('Saturday','estimated-delivery-woocommerce' ),
							'sun'  => __('Sunday','estimated-delivery-woocommerce' ),
						)
					),				
					'record_log'		=> array(
						'title'		   	=> __( 'Record Log', 'wf_address_autocomplete_validation' ),
						'type'			=> 'checkbox',
						'default'		=> 'no',
						'name'		 	=> __( 'Enable', 'estimated-delivery-woocommerce' ),
						'desc'	 		=> 'Enable <p style="width:60%"><small>To debug the problem, select the checkbox to record log which gets generated in folder wordpress\wp-content\uploads\wc-logs. Here, you can check the estimation input and result pair.</small></p>',
						'custom_attributes'=> array(
							'autocomplete'=> 'off'),
						'id'   			=> 'wf_estimated_delivery_record_log',
					),
					
					'date_options_sectionend'	=>	array(
						'type' => 'sectionend',
						'id'   => 'wf_estimated_delivery_date_options_sectionend'
					),			
				) );
				break;		
			case 'wf_shipping_class':
				$settings = apply_filters( 'wf_estimated_delivery_section3_settings', array(
					'shipping_class_options_title'	=>	array(
						'name' => __( 'Shipping Class', 'estimated-delivery-woocommerce' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'wf_estimated_delivery_shipping_class_options_title',
					),	
					'shipping_class'	=>	array(
						'type'	=> 'shipping_class',
						'id'	=> 'wf_estimated_delivery_shipping_class',
					),			
					'shipping_class_options_sectionend'	=>	array(
						'type' => 'sectionend',
						'id'   => 'wf_estimated_delivery_shipping_class_options_sectionend'
					),			
				) );
				
				break;
		}
		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );	
	}

	public function wf_estimated_delivery_output() {
		global $current_section;
		$settings = $this->wf_estimated_delivery_get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}

	public function wf_estimated_delivery_save() {   
		global $current_section;  
		$settings = $this->wf_estimated_delivery_get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}

	//to add the necessary js scripts and css styles
	public function wf_estimated_delivery_admin_scripts() {
		
		wp_enqueue_script( 'wf-settingsAlign-script', plugins_url( '../assests/js/settings.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'wf-timepicker-script', plugins_url( '../assests/js/jquery.timepicker.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_style( 'wf-timepicker-style', plugins_url( '../assests/css/jquery.timepicker.css', __FILE__ ) );
	}	
	public function wf_estimated_delivery_scripts() {
		if(is_checkout()&&!is_order_received_page()){
			wp_enqueue_script( 'wf-checkout-script', plugins_url( '../assests/js/checkout.js', __FILE__ ), array( 'jquery' ) );
		}	
	}

	public function generate_day_limits_html() {
		$this->day_limits			= get_option( 'wf_estimated_delivery_day_limits' );

		?>
		<tr valign="top">

		<th scope="row" class="titledesc"><?php _e( 'Shipping Times', 'estimated-delivery-woocommerce' ); ?> <span class="woocommerce-help-tip" data-tip="Set the time limit for the days of the week."></span></th>
		<td class="forminp" id="bacs_accounts">
		<table class="widefat wc_input_table sortable" style="width:60%;" cellspacing="0">
			<thead>
				<tr>
					<th><?php _e( 'Monday', 'estimated-delivery-woocommerce' ); ?></th>
					<th><?php _e( 'Tuesday', 'estimated-delivery-woocommerce' ); ?></th>
					<th><?php _e( 'Wednesday', 'estimated-delivery-woocommerce' ); ?></th>
					<th><?php _e( 'Thursday', 'estimated-delivery-woocommerce' ); ?></th>
					<th><?php _e( 'Friday', 'estimated-delivery-woocommerce' ); ?></th>
					<th><?php _e( 'Saturday', 'estimated-delivery-woocommerce' ); ?></th>
					<th><?php _e( 'Sunday', 'estimated-delivery-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input  name="wf_estimated_delivery_day_limits[]" type="text" style="width:80px;" placeholder="20:00" value="<?php echo !empty($this->day_limits[0]) ? $this->day_limits[0] : '20:00' ?>"/></td>
					<td><input  name="wf_estimated_delivery_day_limits[]" type="text" style="width:80px;" placeholder="20:00" value="<?php echo !empty($this->day_limits[1]) ? $this->day_limits[1] : '20:00' ?>"/></td>
					<td><input  name="wf_estimated_delivery_day_limits[]" type="text" style="width:80px;" placeholder="20:00" value="<?php echo !empty($this->day_limits[2]) ? $this->day_limits[2] : '20:00' ?>"/></td>
					<td><input  name="wf_estimated_delivery_day_limits[]" type="text" style="width:80px;" placeholder="20:00" value="<?php echo !empty($this->day_limits[3]) ? $this->day_limits[3] : '20:00' ?>"/></td>
					<td><input  name="wf_estimated_delivery_day_limits[]" type="text" style="width:80px;" placeholder="20:00" value="<?php echo !empty($this->day_limits[4]) ? $this->day_limits[4] : '20:00' ?>"/></td>
					<td><input  name="wf_estimated_delivery_day_limits[]" type="text" style="width:80px;" placeholder="20:00" value="<?php echo !empty($this->day_limits[5]) ? $this->day_limits[5] : '20:00' ?>"/></td>
					<td><input  name="wf_estimated_delivery_day_limits[]" type="text" style="width:80px;" placeholder="20:00" value="<?php echo !empty($this->day_limits[6]) ? $this->day_limits[6] : '20:00' ?>"/></td>
				</tr>
			</tbody>
		</table>
		</td>
		</tr>
		<?php
	}

	public function generate_shipping_class_html() {
		include( 'html-shipping-class.php' );
	}
	public function generate_advanced_html() {
		include( 'html-advanced-settings.php' );
	}
	public function wf_estimated_delivery_this_screen() {
		$currentScreen = get_current_screen();
		if($currentScreen->id=='woocommerce_page_wc-settings'){
		
		add_action( 'admin_enqueue_scripts', array( $this, 'wf_estimated_delivery_admin_scripts' ) );
		}
	 }
	
}
return new Wf_Estimated_Delivery_Settings();