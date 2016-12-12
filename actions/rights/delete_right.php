<?php
function delete_right(){
	//Check rights to perform this action
	if(!check_rights('delete_right')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}
	
	//Retrieve user id from browser
	$user_id=(int)$_GET['user'];

	//Retrieve right id from browser
	$right_id=(int)$_GET['right']; 
	
	//Check if user exist in database
	if(db_easy_count('SELECT * FROM `phpbb_users` WHERE `user_id`='.$user_id)==0){
		system_error('User with id '.$user_id.' doesn\'t exist in database');
	}
	
	//Check if right exist in database
	$right_res=db_query('SELECT * FROM `phpbb_rights` WHERE `id`='.$right_id);
	if(db_count($right_res)==0){
		system_error('Right with id '.$right_id.' doesn\'t exist in database');
	}else{
		$right=db_fetch($right_res);
	}
	
	//Request to database
	$del_res=db_query('DELETE FROM `phpbb_rights_users` WHERE `user_id`='.$user_id.' AND `right_id`='.$right_id);
	
	//Error when try to delete
	if(!db_result($del_res)){
		system_error('Error when trying delete right with user id='.$user_id.' and right id='.$right_id);
	}else{
		header('location: /manager.php?action=list_rights&group='.$right['group_id']);	
	}
}
?>