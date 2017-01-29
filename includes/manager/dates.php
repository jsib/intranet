<?php
//Get days from specified date to end of year
function get_days_since_date_to_year_end($day, $month, $year){
	return (int) ( (strtotime( date( '01.01.'.($year+1) ) ) - strtotime( date( $day.'.'.$month.'.'.$year ) ) ) / (60*60*24) );
}

//Get number of months from specified month to end of year
function get_months_from_date_to_year_end($month){
	return 12 - (int)$month + 1;
}

//Convert hours to human format
function to_days_and_hours($hours_input, $cut_regime=false){
	//Get absolute value of hours number 
	$hours_total=abs( $hours_input );
	
	//Prevent division by zero
	if($hours_input == 0){
		if($cut_regime == true){
			return '';
		}else{
			return '0д';
		}
	}
	
	//Get sign
	$sign=$hours_input / $hours_total;
	
	//Get hours number
	$hours=$hours_total % 8;
	
	//Get days number
	$days=($hours_total - $hours) / 8;
	
	//Build string
	if($cut_regime == true && $hours == 0){
		$result_str=$days;
	}else{
		//Attach days to string
		$result_str=$days . 'д ';
		
		//Attach hours to string
		if( $hours > 0 ){
			$result_str.=$hours . 'ч'; 
		}		
		
	}
	
	//Put hours to string

	
	//Get sign str
	if($sign == -1){
		$sign_str='-';
	}else{
		$sign_str='';
	}
	
	//Return formatted string
	return $sign_str . $result_str;
}

?>