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
		return dis_error("You don't have needed permissions");
	}

	//Get data from browser
	$entity_id=(int)$_GET['entity'];
	
	//Retrieve the entity properties from database
	$entity=db_easy("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `id`=$entity_id");
	
	//Delete entity from database
	db_query("DELETE FROM `".$table_prefix.$object_plural_eng."` WHERE `id`=$entity_id AND `system`!=1");
	
	//Forward to other page through HTTP request
	header("location: /manager.php?action=list_".$object_plural_eng."&action_previous=".$action_eng."&action_previous_result=success&action_previous_entity_name={$entity['name']}");
	
	//Return HTML stream (if presented)
	isset($html) ? return $html : return true;
}
?>