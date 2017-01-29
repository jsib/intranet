<?php
class UserHire{
	protected $user;
	
	function __construct($user){
		//User array with data from database
		$this->user=$user;
	}
	
	//Get user's hire info
	function get_info(){
		//Get hire property of user which stored in database
		$user_hire_month=date("m", strtotime($this->user['hire']));
		$user_hire_year=date("Y", strtotime($this->user['hire']));
		
		//Get formatted date when employee begin working
		$user_hire_date=$user_hire_month.'.'.$user_hire_year;
		
		//Get number of days which employee has worked already in this year
		if($user_hire_year==date('Y')){
			//If user start working this year
			$count_from_begin_of_this_year=false;
		}else{
			//If user start working one of previous years
			$count_from_begin_of_this_year=true;
		}

		//Get hire info
		$hire_info['user_hire_date']=$user_hire_date;
		$hire_info['count_from_begin_of_this_year']=$count_from_begin_of_this_year;
		
		//Return hire info
		return $hire_info;
	}
}
?>