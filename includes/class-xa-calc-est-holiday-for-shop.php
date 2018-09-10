<?php 
include_once('abstract-class-calc-est-stratergy.php');
class XA_Calc_Est_Strategy_Holiday_for_Shop extends XA_Calc_Est_Strategy {
	
	public function wf_get_delivery_date($date) {
		$this->dafault_days = $date;	
		$this->xa_write_log($this->dafault_days);		

		$cutoff_time = $this->xa_get_cutoff_time( $this->xa_get_current_day() );

		$starting_date = $this->xa_get_staring_date( $cutoff_time );

		$result_date = $this->find_nearest_working_day( $starting_date );
		
		//Add Minimum Delivery Days.
		$result_date = $result_date->modify("+$this->dafault_days day");

		$this->xa_write_log( $result_date->format( $this->delivery_date_display_format ), 'est_date' );
		
		return $result_date->format( $this->delivery_date_display_format );
	}

	private function is_working_day( $check_date ){
		return in_array(strtolower(date_format($check_date,'D')),$this->wf_workdays);
	}

	private function find_nearest_working_day( $curr_date ){
		$loop_limit = 30; //for preventing loop going endless in case of some misconfiguration of settings page.
		while(  !$this->is_working_day( $curr_date )  && $loop_limit > 0 ){
			$curr_date =  $curr_date->modify('+1 day') ;
			$loop_limit --;
		}
		return $curr_date;
	}
}