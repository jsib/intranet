<?php
function add_direction(){
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
		system_error('permission_denied');
	}

	if(!isset($_POST['name'])){
		if(!isset($_GET['name'])){
			//Form HTML flow
			$html=template_get($object_plural_eng."/".$action_full_eng, array(
																'action'=>"/manager.php?action=".$action_full_eng,
																'message'=>template_get("no_message")
													));
		}elseif(isset($_GET['result'])){
			//Retrieve entity name frow browser
			$entity_name=$_GET['name'];
			
			//Get previous action properties from browser
			$action_result=$_GET['result'];
			
			//Check does this action and result exist in system
			if(isset($object_actions[$action_eng]['results'][$action_result]['result'])){
				//Retrieve action result message
				$result_html=template_get("message", array('message'=>html_replace($object_actions[$action_eng]['results'][$action_result]['result'], array('name'=>$entity_name))));
			}else{
				system_error('result_not_defined_for_this_object_and_action', array('result'=>$$action_result, 'object'=>$object_plural_eng, 'action'=>$action_eng));
			}
			
			//Form HTML flow
			$html=template_get($object_plural_eng."/".$action_full_eng, array(
																	'action'=>"/manager.php?action=".$action_full_eng,
																	'message'=>$result_html
														));
		}
	}else{
		//Retrieve entity name frow browser
		$entity_name=$_POST['name'];
		
		//Define success checks flag
		$do=true;

		//Some checks
		if(!preg_match("/^.{1,70}$/", $entity_name)){
			header("location: /manager.php?action=".$action_full_eng."&result=empty_entity_name&name=".urlencode($entity_name));
			$do=false;
		}
		//Check for entity with same name
		if(db_easy_count("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `name`='".$entity_name."'")>0){
			header("location: /manager.php?action=".$action_full_eng."&result=same_entity_exists&name=".urlencode($entity_name));
			$do=false;
		}
		
		if($do){
			db_query("INSERT INTO `".$table_prefix.$object_plural_eng."` SET
										`name`='{$entity_name}'
										");
			$entity_id=db_insert_id();
			header("location: /manager.php?action=list_".$object_plural_eng."&action_previous=".$action_eng."&action_previous_result=entity_added&action_previous_entity_name=".$entity_name);
		}
	}
	
	//Form HTML flow
	return $html;
}
?>