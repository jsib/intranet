<?php
function set_transfer_days_number(){
	//Get data from browser
	$user_id=(int)$_GET['user_id'];
	$year=(int)$_GET['year'];
	$days_number=(int)$_GET['days_number'];
	$status=2;
	
	//Get number of transferred vacation credit with given criteria
	$transfer_days_res=db_query('SELECT `days_number`
								 FROM `phpbb_attendance_granted_benefits`
								 WHERE `user_id`='.$user_id.'
									   AND `year`='.$year
							   );
							   
	if(db_count($transfer_days_res)>0){
		//Get previous value of days_number
		$days_number_old=db_fetch($transfer_days_res)[0];
		
		if($days_number_old==$days_number){
			//Do nothing, becouse same record already exists
			return 1;
		}else{
			//Use UPDATE syntax
			if(db_easy_result('UPDATE `phpbb_attendance_granted_benefits`
							   SET `days_number`='.$days_number.'
							   WHERE `user_id`='.$user_id.'
							         AND `year`='.$year.'
									 AND `status`='.$status
							 )){
				return 1;
			}else{
				//Error on UPDATE operation
				return 'Ошибка записи';
			}
		}
	}else{
		//Use INSERT syntax
		if(db_easy_result('INSERT INTO `phpbb_attendance_granted_benefits`
		                   SET `days_number`='.$days_number.',
							   `user_id`='.$user_id.',
						       `year`='.$year.',
							   `status`='.$status
						 )){
			return 1;
		}else{
			//Error on INSERT operation
			return 'Ошибка записи';
		}
	}
}
?>