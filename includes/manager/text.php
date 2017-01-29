<?php
function to_days_and_hours($hours_input){
	//Get absolute value of hours number
	$hours_total=abs( $hours_input );
	
	//Prevent division by zero
	if($hours_input == 0){
		return '0д';
	}
	
	//Get sign
	$sign=$hours_input / $hours_total;
	
	//Get hours number
	$hours=$hours_total % 8;
	
	//Put days to string
	$hours_str=( ( $hours_total - $hours) / 8 ) . 'д ';
	
	//Put hours to string
	if( $hours > 0 ){
		$hours_str.=$hours . 'ч';
	}
	
	//Get sign str
	if($sign == -1){
		$sign_str='-';
	}else{
		$sign_str='';
	}
	
	//Return formatted string
	return $sign_str . $hours_str;
}
?>