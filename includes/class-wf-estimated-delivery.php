<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wf_Estimated_Delivery {
    public function __construct() {
        $shipping_class_dates           = get_option('wf_estimated_delivery_shipping_class');
        $this->shipping_class_dates     = !empty( $shipping_class_dates ) ? $shipping_class_dates : array();
        
        $estimated_delivery             = get_option('wf_estimated_delivery_min_delivery_days');
        $this->estimated_delivery       = !empty( $estimated_delivery ) ? $estimated_delivery : 0;
        
        $this->page_text_format         = 'simple';
        
        $this->cart_page_text           = __('Estimated delivery ', 'estimated-delivery-woocommerce');
        $this->product_page_sample_text = __('Estimated delivery by: ', 'estimated-delivery-woocommerce');
       
        $this->calculation_mode     = 'holiday_for_shop';

        if ( ! class_exists( 'XA_Calc_Est_Strategy' ) )
            include_once 'abstract-class-calc-est-stratergy.php';

        $this->delivery_date_calculator_obj =  XA_Calc_Est_Strategy::get_calculation_mode( $this->calculation_mode );

        add_action( 'wp_ajax_wf_estimated_delivery',array($this ,'wf_estimated_delivery_checkout_page'));
        add_action( 'woocommerce_review_order_before_shipping', array($this,'wf_estimated_delivery_checkout_page'), 10, 0 );  


        add_action( 'woocommerce_admin_order_data_after_order_details', array($this,'wf_estimated_delivery_admin_order_meta') );
        add_action( 'woocommerce_checkout_update_order_meta' , array($this, 'wf_add_order_meta' ) , 2 );         
        add_filter( 'woocommerce_get_order_item_totals', array($this, 'wf_estimated_delivery_thankyou_page'),10,2);
        add_action( 'woocommerce_get_availability', array($this, 'wf_estimated_delivery_product_page'),1,2);
        add_action( 'woocommerce_cart_totals_before_shipping', array($this, 'wf_estimated_delivery_cart_page'));

    }

    /**
    * This function return the estimated delivery of given product.
    * There might be a shipping class associated and shipping class should configured with an estimated delivery
    *
    * @since 1.4.0
    * @param product id or variation id
    * @return Estimated delivery as sting.
    */
    private function xa_get_estimated_delivery_of_item( $product ){
        if(!$product){
            return false;
        }

        try{
            $product = wc_get_product($product);
        }catch(Exception $e){
            // echo "<br> Invalid product";
            return false;
        }
        

        $test = new WC_Shipping;
        $shipping_classes = $test->get_shipping_classes();

        $class_id = $product->get_shipping_class_id();
        for ( $i=0; $i < count($shipping_classes); $i++ ) { 
            if($shipping_classes[$i]->term_id === $class_id){
                $product_shipping_class = $shipping_classes[$i]->slug;
                break;
            }
        }

        if( empty($product_shipping_class) ){
            return false;
        }

        $product_shipping_class_min_date = 0;

        if(is_array($this->shipping_class_dates) && !empty($this->shipping_class_dates)){
            foreach ( $this->shipping_class_dates as $id => $value ) {
                if($this->shipping_class_dates[$id]['id'] == $product_shipping_class){
                    $product_shipping_class_min_date = $this->shipping_class_dates[$id]['min_date'];
                }
            }
        }


        $estimated_delivery = $this->estimated_delivery;
        $estimated_delivery += (int)$product_shipping_class_min_date;

        if( $this->page_text_format === 'simple'){
            $text = $this->product_page_sample_text .' '. $this->delivery_date_calculator_obj->wf_get_delivery_date($estimated_delivery);
        }
        return $text;
    }

    public function wf_estimated_delivery_product_page($stock_arr, $item=''){ 
        global $product;
	
	if( ! is_object($product) )
	{
		$record		= get_option( 'wf_estimated_delivery_record_log' );
		$record 	= !empty( $record ) ? $record : '';
		if($record == 'yes') {
				Estimated_Delivery_Log::log_update( "Global variable Product is not an object. Product type - ". gettype($product) , 'Product Type');
		}
		return false;
	}
        //if variable product
        if( !empty($item) ){
            $delivery_text = $this->xa_get_estimated_delivery_of_item( $item->get_id() );
        }else{
            $delivery_text = $this->xa_get_estimated_delivery_of_item( $product );
        }
        if( !empty($delivery_text) ){
            if( $stock_arr['class']=='in-stock' ){
                //if variable product
                if( !empty($item) ){
                    $stock_arr['availability'] = $stock_arr['availability'].'<br><small>'.$delivery_text.' </small>';
                }else{
                    $stock_arr['availability'] = $stock_arr['availability'].$delivery_text.' ';
                }
            }
        }

        return $stock_arr;
    }

    private function get_estimate_days($destination){

        global $woocommerce;
        
        $cart_items = $woocommerce->cart->get_cart();
        foreach ($cart_items as $cart_item) {
            $product_shipping_class [] = $cart_item['data']->get_shipping_class();
        }
        
        for ($i=0; $i < count($product_shipping_class) ; $i++) { 

            $product_shipping_class_min_date = 0;
            
            if(is_array($this->shipping_class_dates) && !empty($this->shipping_class_dates)){
                foreach ( $this->shipping_class_dates as $id => $value ) {
                    if($this->shipping_class_dates[$id]['id'] == $product_shipping_class[$i])
                    {
                        $product_shipping_class_min_date = $this->shipping_class_dates[$id]['min_date'];
                    }
                }
            }
            $estimated_delivery = $this->estimated_delivery + (int)$product_shipping_class_min_date;
            $est[] = $estimated_delivery;
        }

        $test = empty($est) ? null : max($est) ;
        
        return $test;
    }

    public function wf_estimated_delivery_cart_page(){
	global $woocommerce;
        $destination = array(
            'country'  => $woocommerce->customer->get_shipping_country(),
            'state'   => $woocommerce->customer->get_shipping_state(),
            'postcode' => $woocommerce->customer->get_shipping_postcode(),
        );
        $test = $this->get_estimate_days( $destination );

        if($this->page_text_format === 'simple'){
            echo '<tr><th> ' . $this->cart_page_text . '</th><td data-title="'.$this->cart_page_text.'"> ' . $this->delivery_date_calculator_obj->wf_get_delivery_date($test)  . '</td></tr>';  
        }
    }

    private function get_estimated_delivery_text($test){
        $text = '';
        if( $this->page_text_format === 'simple'){
              $text .= '<tr><th> ' . $this->cart_page_text . '</th><td> ' . $this->delivery_date_calculator_obj->wf_get_delivery_date($test)  . '</td></tr>';  
        }
        return $text;
    }

    public function wf_estimated_delivery_checkout_page(){
        if(isset($_POST["country"])){
            echo $this->get_estimated_delivery_text( $this->get_estimate_days( array(
                        'country'  => $_POST["country"],
                        'state'    => $_POST["state"],
                        'postcode' => $_POST["postcode"],
                    )
                )
            );
        }
    }

    public function wf_estimated_delivery_thankyou_page($ids,$order){
        $order_id = ( WC()->version < '3.0' ) ? $order->ID : $order->get_id();
        $est_delivery = get_post_meta($order_id, '_est_date', true );
		if( ! empty($est_delivery) ) {
			$ids['ph_est_delivery'] = array(
				'label'		=>	esc_attr( $this->cart_page_text ),
				'value'		=>	$est_delivery
			);
		}
        return $ids;
    }
    public function wf_add_order_meta($order_id){
        $order = wc_get_order($order_id);
        $order_items = $order->get_items();
        $shippable_product_exist = false;
        foreach( $order_items as $order_item ) {
            $order_item_product = $order_item->get_product();
            if( $order_item_product instanceof WC_Product && $order_item_product->needs_shipping() ) {
                $shippable_product_exist = true;
                break;
            }
        }
        // Skip estimated delivery calculation if none of the product is shippable
        if( $shippable_product_exist === false ) {
            return;
        }

        $destination = array(
            'country'  => WC()->customer->get_shipping_country(),
            'state'    => WC()->customer->get_shipping_state(),
            'postcode' => WC()->customer->get_shipping_postcode(),
        );

        $test = $this->get_estimate_days($destination);

        if( $this->page_text_format === 'simple'){
              update_post_meta( $order_id, '_est_date', $this->delivery_date_calculator_obj->wf_get_delivery_date($test)); 
        }
    }    

    public function wf_estimated_delivery_admin_order_meta($order){
        echo '<p class="form-field form-field-wide">';
        
        $id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
        $est_date = get_post_meta($id,'_est_date',true);

        echo '<label for="wf_order_date"> '.$this->cart_page_text.' </label>';
        echo ' <input disabled type="text" value="'.$est_date.'" id="wf_order_date" name="_wf_order_date" >';
        echo '</p>';
    }    
}
new Wf_Estimated_Delivery;
