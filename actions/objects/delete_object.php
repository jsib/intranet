<?php
function delete_object(){
	//Check rights to perform this action
	if(!check_rights('delete_object')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}

	//Get data from browser
	$object_id=(int)$_GET['object'];
	
	//Perform query to database
	$object_res=db_query("SELECT * FROM `phpbb_objects` WHERE `id`=".$object_id);
	
	//Check for object existence
	if(db_count($object_res)==0){
		system_error();
	}else{
		//Get object from database
		$object=db_fetch($object_res);
		
		//Delete object from database
		db_query("DELETE FROM `phpbb_objects` WHERE `id`=".$object_id);
		
		//Refer to other page
		header("location: /manager.php?action=list_objects&result=object_deleted&name=".urlencode($object['name']));
	}
}
?>