<?php
function edit_direction(){
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
		system_error('permission_denied');
	}
	
	//Retrieve entity_id from browser
	$entity_id=$_GET['entity_id'];
	
	if(!isset($_POST['name'])){
		//Form result message (if some presented)
		if(isset($_GET['result'])){
			//Retrieve result from browser
			$action_result=$_GET['result'];
			
			//Retrieve entity name from browser
			$entity_name=@$_GET['name'];
			
			//Build action result message
			if(isset($object_actions[$action_eng]['results'][$action_result]['result'])){
				$result_html=template_get("message", array('message'=>html_replace($object_actions[$action_eng]['results'][$action_result]['result'], array('name'=>$entity_name))));
			//or result message is not configured
			}else{
				system_error('result_not_defined_for_this_object_and_action', array('result'=>$$action_result, 'object'=>$object_plural_eng, 'action'=>$action_eng));
			}
		}
		
		$entity = db_easy("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `id`=$entity_id");
		
		//Form show entity link
		$show_entity_link="<a href='/manager.php?action=show_".$object_singular_eng."&entity_id=".$entity_id."' style='font-size:8pt;'>Просмотреть</a>";
		
		//Form list entities link
		$list_entities_link="<a href=\"/manager.php?action=list_".$object_plural_eng."\" style=\"font-size:8pt;color:black;text-decoration:underline;\">Все дирекции</a>";
		
		//Build HTML flow
		$html=template_get($object_plural_eng."/".$action_full_eng, array(
																'list_entities_link'=>$list_entities_link,
																'action'=>"/manager.php?action=".$action_full_eng."&entity_id=$entity_id",
																'name'=>$entity['name'],
																'address'=>$entity['address'],
																'phone'=>$entity['phone'],
																'branches'=>$branches_html,
																'showpoint'=>$show_entity_link,
																'message'=>$result_html
																));
	}else{
		//Retrieve data from browser
		$entity_name=trim($_POST['name']);
		//echo "entity_id:".$entity_id;
		//exit;

		
		//Define success checks flag
		$do=true;		
		
		//Check for empty entity name
		$entity_name=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $entity_name)){
			header("location: /manager.php?action=".$action_full_eng."&entity_id_id=".$entity_id."&result=empty_entity_name");
			$do=false;
		}
		
		//Check for entity with same name
		$other_entities_db=db_query("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `name`='".$entity_name."'");
		$other_entity=db_fetch($other_entities_db);
		if(db_count($other_entities_db)>0){
			if($other_entity['id']!=$entity_id){
				header("location: /manager.php?action=".$action_full_eng."&entity_id=".$entity_id."&result=same_entity_exists&name=".urlencode($entity_name));
				$do=false;
			}
		}
		
		if($do){
			db_query("	UPDATE `".$table_prefix.$object_plural_eng."`
						SET `name`='".$entity_name."'
						WHERE `id`=".$entity_id);
						
			//Refer to edit entity page
			header("location: /manager.php?action=".$action_full_eng."&entity_id=".$entity_id."&result=success");
		}
	}
	
	//Return HTML flow
	return $html;
}
?>