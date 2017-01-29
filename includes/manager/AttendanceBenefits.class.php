<?php
class AttendanceBenefits{
	public $granted_benefits;
	public $survive_benefits;
	public $utilize_benefits;
	public $available_benefits;
	
	protected $user;
	protected $config;
	protected $status;
	
	function __construct($user, $report_year=false, $status){
		//User array with data from database
		$this->user=$user;
		
		//Status of certain benefit
		$this->status=$status;
		
		//Get settings from config
		$this->config=$GLOBALS['configuration']['attendance'];
		
		//Define in which year we make calculations
		if($report_year==false){
			$this->report_year=date('Y');
		}else{
			$this->report_year=$report_year;
		}
	}
	
	//Get amount of granted benefits for employee
	public function get_granted_benefits(){
		//Get user's hire year and month
		$user_hire_month=date("m", strtotime($this->user['hire']));
		$user_hire_year=date("Y", strtotime($this->user['hire']));
		
		//Get days from hire date to end of year
		if($user_hire_year == $this->report_year){
			$months_from_date_to_year_end=get_months_from_date_to_year_end($user_hire_month);
			$granted_days=$this->config[$this->status]['days_credit_norm'] / 12 * $months_from_date_to_year_end;
		}else{
			$granted_days=$this->config[$this->status]['days_credit_norm'];
		}
		
		//Get result in hours
		$granted_hours=(int) ($granted_days * 8);
		
		/*//Round result
		$granted_hours=$granted_hours - ($granted_hours % 8);*/
		
		//Set helpers
		$this->granted_benefits['hours_total']=$granted_hours;
		$this->granted_benefits['hours']=$granted_hours % 8;
		$this->granted_benefits['days']=($granted_hours - $granted_hours % 8) / 8;
		
		//Return granted benefit days for staff
		return $granted_hours;
	}
	
	//Get amount of survive benefits for employee
	public function get_survive_benefits(){
		//Get amount of survive benefits from previous year
		$survive_days_res=db_query('SELECT `days_number`
									FROM `phpbb_attendance_granted_benefits`
									WHERE `user_id`='.$this->user['user_id'].'
									      AND `year`='.$this->report_year.'
										  AND `status`='.$this->status
								  );
								   
		//Take days number from database
		if(db_count($survive_days_res)>0){
			$survive_hours=db_fetch($survive_days_res)['days_number'] * 8;
		//Put default value
		}else{
			$survive_hours=0;
		}
		
		//Set helpers
		$this->survive_benefits['hours_total']=$survive_hours;
		$this->survive_benefits['hours']=$survive_hours % 8;
		$this->survive_benefits['days']=($survive_hours - $survive_hours % 8) / 8;
		
		//Return got hours number
		return $survive_hours;
	}
	
	//Get amount of utilize benefits for employee
	public function get_utilize_benefits(){
		//Here we store result
		$utilize_hours=0;

		//Get amount of utilize benefits from database
		$utilize_hours_res=db_query('SELECT `hours`
									FROM `phpbb_timetable`
									WHERE `user_id`='.$this->user['user_id'].'
									      AND `year`='.$this->report_year.'
										  AND `status`='.$this->status
								   );
								  
		//Take hours number from database
		if(db_count($utilize_hours_res)>0){
			while( $utilize_hours_while = db_fetch($utilize_hours_res) ){
				$utilize_hours+=$utilize_hours_while['hours'];
			}
		}
		
		//Set helpers
		$this->utilize_benefits['hours_total']=$utilize_hours;
		$this->utilize_benefits['hours']=$utilize_hours % 8;
		$this->utilize_benefits['days']=($utilize_hours - $utilize_hours % 8) / 8;
		
		//Return got hours number
		return $utilize_hours;
	}
	
	//Get amount of available benefits for employee
	public function get_available_benefits(){
		//Calculate result
		$available_hours=$this->get_granted_benefits() + $this->get_survive_benefits() - $this->get_utilize_benefits();
		
		//Set helpers
		$this->available_benefits['hours_total']=$available_hours;
		$this->available_benefits['hours']=$available_hours % 8;
		$this->available_benefits['days']=($available_hours - $available_hours % 8) / 8;
		
		//Return
		return $available_hours;
	}
	

}
?>