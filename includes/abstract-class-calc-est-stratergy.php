<?php 
abstract class XA_Calc_Est_Strategy {
	public $record_log 	= '';
	public $wf_holiday 	= '';
	public $wf_workdays = '';
	public $dafault_days = '';
	public $calculation_mode = '';
	public $delivery_date_display_format = '';
	public $calculate_option = '';
	public $cutOff = '';

	
	function __construct(){
		$this->xa_set_options();
	}

	private function xa_set_options(){
		$default_working_days 	= array('mon','tue','wed','thu','fri');

		$record_log				= get_option( 'wf_estimated_delivery_record_log' );
		$this->record_log 		= !empty( $record_log ) && $record_log ==='yes' ? true : false;

		$wf_holiday				= get_option( 'wf_estimated_delivery_holiday' );
		$this->wf_holiday 		= !empty( $wf_holiday ) ? $wf_holiday : array();

		$wf_workdays			= get_option( 'wf_estimated_delivery_operation_days' );
		$this->wf_workdays		= !empty( $wf_workdays  ) ? $wf_workdays : $default_working_days ;

		$this->calculation_mode		= 'holiday_for_shop';

		$this->delivery_date_display_format 	= 'd/m/Y';

		$this->cutOff 	= get_option('wf_estimated_delivery_day_limits'); 

	}

	public static function get_calculation_mode( $calculation_mode ){
		switch ( $calculation_mode ) {
		    case 'holiday_for_shop':
		        include_once('class-xa-calc-est-holiday-for-shop.php');
		        $delivery_date_calculator_obj = new XA_Calc_Est_Strategy_Holiday_for_Shop();
		        break;
		}
		return $delivery_date_calculator_obj;
	}

	public function xa_write_log($msg, $title='input'){
		if($this->record_log == 'yes'){
			Estimated_Delivery_Log::log_update($msg, $title);
		}
	}


	protected function xa_get_current_day(){
		$cur_day 	= current_time('D');
		return strtolower($cur_day);
	}

	protected function xa_get_cutoff_time( $cur_day ){
		$day_order = array( 'mon'=>0, 'tue'=>1, 'wed'=>2, 'thu'=>3, 'fri'=>4, 'sat'=>5, 'sun'=>6 );
		
		$cutOff = !empty($this->cutOff[ $day_order[$cur_day] ]) ? $this->cutOff[ $day_order[$cur_day] ] : '';
		$this->cutOff 	= !empty( $cutOff ) ? $cutOff : '20:00';

		$this->cutOff = str_replace('.', ':', $this->cutOff);
		return ( strpos($this->cutOff , ':') ) ? explode( ':', $this->cutOff ) : $this->cutOff;		
	}

	protected function xa_get_staring_date( $cutoff_time ){
		list( $cut_hrs, $cut_min ) = $cutoff_time;

		$cut_hrs = intval($cut_hrs);
		$cut_min = intval($cut_min);

		$wf_date = Ph_Estimated_Delivery_Common::get_wordpress_time();;

		$wf_time = clone $wf_date;
		$wf_time->setTime($cut_hrs,$cut_min);

		$today_date = clone $wf_date;
		if ($wf_date >= $wf_time){
			$today_date->modify('+1 day');				
		}

		return $today_date;
	}
}
