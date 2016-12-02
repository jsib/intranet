<?php
function show_direction(){
	//Retrieve information from this function name
	$function_name_pieces=explode("_", __FUNCTION__);

	//Refer to global variables
	global $table_prefix;
	global $system_objects;

	//Retrieve object properties
	$object_singular_eng=$function_name_pieces[1];
	$object_plural_eng=$system_objects[$object_singular_eng]['plural_name_eng'];
	$object_actions=$system_objects[$object_plural_eng]['actions'];
	
	//Retrieve action properties
	$action_eng=$function_name_pieces[0];
	$action_full_eng=__FUNCTION__;
	
	if(!check_rights($action_full_eng)){
		//Return HTML flow
		return dis_error("You don't have permissions for this action", 'return', 'prod');
	}

	if(isset($_GET['result'])){
		//Get previous action properties from browser
		$action_result=$_GET['result'];

		if(isset($object_actions[$action_eng]['results'][$action_result]['result'])){
			//Retrieve action result message
			$result_html=template_get("message", array('message'=>html_replace($object_actions[$action_eng]['results'][$action_result]['result'], array('name'=>$entity_name))));
		}else{
			dis_error("Result '".$action_result."' not defined with object '".$object_plural_eng."' and action '".$action_eng."'", 'echo', 'debug', 'prod');
		}
	}
	
	//Retrieve entity_id from browser
	$entity_id=(int)$_GET['entity_id'];
	
	//Perform request to database
	$entity_db=db_query("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `id`=$entity_id");
	
	//Get number of entities retrieved from database
	if(db_count($entity_db)>0){
		$entity_name=db_fetch($entity_db)['name'];
	}else{
		return dis_error("There is no entities of object '".$object_singular_eng."' in database", 'prod');
	}
		
	if(check_rights('edit_'.$object_singular_eng)){
		$edit_entity_link="<a href='/manager.php?action=edit_".$object_singular_eng."&entity_id=$entity_id' style='font-size:8pt;'>Редактировать</a>";
	}
	
	//Define child object name
	$child_object_singular_eng="employee";
	
	$child_entities_db=db_query("SELECT * FROM `".$table_prefix."users`
									WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root' AND `user_email`!='olex3352@gmail.com'
											AND `".$object_singular_eng."_id`=$entity_id
									ORDER BY `username` ASC
									");
	
	$child_entities_html="";
	
	if(db_count($child_entities_db)>0){
		while($child_entity=db_fetch($child_entities_db)){
			$child_entities_html.="<a href='/manager.php?action=show_".$child_object_singular_eng."&".child_object_singular_eng."_id={$child_entity['user_id']}'>".$child_entity['username']."</a><br/>";
		}	
	}else{
		$child_entities_html="нет";
	}
	
	//Form link to list of entities
	$list_entities_link="<a href=\"/manager.php?action=list_".$object_plural_eng."\" style=\"font-size:8pt;color:black;text-decoration:underline;\">Все дирекции</a>";

	
	$html.=template_get($object_plural_eng."/".$action_full_eng, array(
																'list_entities_link'=>$list_entities_link,
																'entity_name'=>$entity_name,
																'edit_entity_link'=>$edit_entity_link,
																'message'=>$message_html,
																'child_entities'=>$child_entities_html
												));
	return $html;
}
?>