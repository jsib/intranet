<?php
//Get days from specified date to end of year
function get_days_since_date_to_year_end($day, $month, $year){
	return (int) ( (strtotime( date( '01.01.'.($year+1) ) ) - strtotime( date( $day.'.'.$month.'.'.$year ) ) ) / (60*60*24) );
}

//Get number of months from specified month to end of year
function get_months_from_date_to_year_end($month){
	return 12 - (int)$month + 1;
}

?>