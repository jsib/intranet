<?php
class AttendanceStatistics{
	protected $timetable;
	protected $user;
	protected $year;
	protected $month;
	
	function __construct($timetable, $user, $year, $month=false){
		$this->timetable=$timetable;
		$this->user=$user;
		$this->year=$year;
		$this->month=$month;
	}
	
	//Get attendance statistics for year report
	public function get_year_report(){
		return $this->get_dominant_statuses_year_report() + $this->get_special_statuses_year_report();
	}
	
	//Get attendance statistics for month report
	public function get_month_report(){
		return $this->get_dominant_statuses_month_report() + $this->get_special_statuses_month_report();
	}	
	
	//Get statistics for less of statuses: work days and vacation days
	//!!! Be carefull to edit code here - if we in current year we count days only till now
	protected function get_special_statuses_year_report(){
		//Define result array
		$statistics=array();
		
		//Get max month
		if( $this->year == date("Y") ){
			$month_max=date("n");
		}else{
			$month_max=12;
		}
		
		//Iterate over months
		for($month_for=1; $month_for<=$month_max; $month_for++){
			//Count number of days for certain month
			if($this->year==date("Y") && $month_for==date("n")){
				$day_max=date("j");
			}else{
				$day_max=cal_days_in_month(CAL_GREGORIAN, $month_for, $this->year);	
			}
			
			//Iterate over days
			for($day_for=1; $day_for<=$day_max; $day_for++){
				//Define day of the week
				$day_of_week_for=date( "N", strtotime( $this->year . '-' . $month_for . '-' . $day_for ) );
				
				//Situation when status is defined in database
				if( isset( $this->timetable[ $this->user['user_id'] ][ $month_for ][ $day_for ][ 'status' ] ) ){
					//Get status from timetable array
					$status=$this->timetable[ $this->user['user_id'] ][ $month_for ][ $day_for ][ 'status' ];
					
					//Skip dominant statuses
					if($status == 1 || $status == 6){
						//Add number of hours for certain status
						$statistics[$status]+=$this->timetable[$this->user['user_id']][$month_for][$day_for]['hours'];
					}
					
				//Situation when status is NOT defined in database
				}else{
					//This is holiday
					if( $day_of_week_for==6 || $day_of_week_for==7 ){ 
						$statistics[6]+=8;
					//This is work day
					}else{
						$statistics[1]+=8;
					}
				}
			}
		}
		
		//Return statistics
		return $statistics;
	}
	
	//Get statistics for most of statuses: vacation, sickdays, etc.
	//!!! Be carefull to edit code here - we count all days for all month here, NOT only till now
	protected function get_dominant_statuses_year_report(){
		//Define result array
		$statistics=array();
		
		//Iterate over months
		for($month_for=1; $month_for<=12; $month_for++){
			//Count number of days for certain month
			$day_max=cal_days_in_month(CAL_GREGORIAN, $month_for, $this->year);
			
			//Iterate over days
			for($day_for=1; $day_for<=$day_max; $day_for++){
				//Situation when status is defined in database
				if( isset( $this->timetable[ $this->user['user_id'] ][ $month_for ][ $day_for ][ 'status' ] ) ){
					//Get status from timetable array
					$status=$this->timetable[ $this->user['user_id'] ][ $month_for ][ $day_for ][ 'status' ];
					
					//Skip special statuses
					if($status != 1 && $status != 6){				
						//Add number of hours for certain status
						$statistics[$status]+=$this->timetable[$this->user['user_id']][$month_for][$day_for]['hours'];	 	
					}
				}
			}
		}
		
		//Return statistics
		return $statistics;		
	}	

	//Get statistics for less of statuses: work days and vacation days
	//!!! Be carefull to edit code here - if we in current month we count days only till now
	protected function get_special_statuses_month_report(){
		//Define result array
		$statistics=array();
		
		//Count number of days in certain month
		if( $this->year == date("Y") && $this->month == date("n") ){
			$day_max=date("j");
		}else{
			$day_max=cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
		}

		//Iterate over days in month
		for($day_for=1;$day_for<=$day_max;$day_for++){
			//Get day of week
			$day_of_week_for=date("N", strtotime( $this->year. '-' . $this->month . '-' . $day_for ));
			
			//Situation when status is defined in database
			if( isset( $this->timetable[ $this->user[ 'user_id' ] ][ $day_for ][ 'status' ] ) ){
				//Get status
				$status=$this->timetable[$this->user['user_id']][$day_for]['status'];
				
				//Skip dominant statuses
				if( $status == 1 || $status == 6){
					$statistics[$status]+=$this->timetable[$this->user['user_id']][$day_for]['hours'];
				}
				
			//Situation when status is NOT defined in database
			}else{
				//This is holiday
				if($day_of_week_for == 6 || $day_of_week_for == 7){
					$statistics[6]+=8;
				//This is work day
				}else{
					$statistics[1]+=8;
				}
			}
		}
		
		//Return statistics
		return $statistics;
	}
	
	//Get statistics for most of statuses: vacation, sickdays, etc.
	//!!! Be carefull to edit code here - we count all days for month here, NOT only till now
	protected function get_dominant_statuses_month_report(){
		//Define result array
		$statistics=array();
		
		//Count number of days for certain month
		$day_max=cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);	
		
		//Iterate over days
		for($day_for=1; $day_for<=$day_max; $day_for++){			
			//Situation when status is defined in database
			if( isset( $this->timetable[ $this->user['user_id'] ][ $day_for ][ 'status' ] ) ){
				//Get status from timetable array
				$status=$this->timetable[ $this->user['user_id'] ][ $day_for ][ 'status' ];
				
				//Skip special statuses
				if($status != 1 && $status != 6){				
					//Add number of hours for certain status
					$statistics[$status]+=$this->timetable[ $this->user['user_id'] ][ $day_for]['hours' ];	 	
				}
			}
		}
				
		//Return statistics
		return $statistics;		
	}		
}
?>