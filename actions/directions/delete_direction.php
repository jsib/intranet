<?php
//Delete a direction of company
function delete_direction(){
	//Retrieve information from this function name
	$function_name_pieces=explode("_", __FUNCTION__);
	
	//Refer to global variables
	global $table_prefix;
	global $system_objects;
	
	//Retrieve object properties
	$object_singular_eng=$function_name_pieces[1];
	$object_plural_eng=$system_objects[$object_singular_eng]['plural_name_eng'];
	
	//Retrieve action properties
	$action_eng=$function_name_pieces[0];
	
	//Check rights for this action
	if(!check_rights($action_eng.'_'.$object_singular_eng)){
		//Retrieve error message
		return dis_error("You don't have needed permissions", 'return');
	}

	//Get data from browser
	$entity_id=(int)$_GET['entity_id'];
	
	//Retrieve the entity properties from database
	$entity_db=db_query("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `id`=$entity_id");
	
	//Get number of entities retrieved from database 
	$entities_number=db_count($entity_db);

	if($entities_number>0){
		//Get entity name retrieved from database
		$entity_name=db_fetch($entity_db)['name'];

		//Delete entity from database
		db_query("DELETE FROM `".$table_prefix.$object_plural_eng."` WHERE `id`=".$entity_id." AND `system`!=1 LIMIT 1");
		
		//Refet to list of entities
		header("location: /manager.php?action=list_".$object_plural_eng."&action_previous=".$action_eng."&action_previous_result=success&action_previous_entity_name=".$entity_name);
	}else{
		dis_error("There is no entities of object '".$object_singular_eng."' with id=".$entity_id, 'echo', 'debug');
	}
}
?>