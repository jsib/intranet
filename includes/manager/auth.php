<?php
function check_rights($right_name=""){
	//Define global variables
	global $user;
	
	//Check rights
	if(isset($GLOBALS['super_rights_users'][$user->data['username']])){
		//For users with super rights 
		return true;
	}else{
		//If
		if($right_name!==""){
			$rightRES=db_query("SELECT * FROM `phpbb_rights` WHERE `name`='$right_name'");
			
			if(db_count($rightRES)==1){
				$right_id=db_fetch($rightRES)['id'];
			}else{
				show("Ошибка в функции check_right_name(). Права с именем '$right_name' не существует или имеется несколько прав с таким именем.<br/>");
				show('Debug backtrace:');
				show(debug_backtrace());
				exit;
			}
			
			if(db_easy_count("SELECT * FROM `phpbb_rights_users` WHERE `user_id`={$user->data['user_id']} AND `right_id`=$right_id")>0){
				return true;
			}else{
				return false;
			}
		}
	}
}
?>