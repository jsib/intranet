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
		//Return HTML flow
		return dis_error("You don't have permissions for this action", 'return', 'prod');
	}
	
	//Retrieve entity_id from browser
	$entity_id=$_GET['entity_id'];
	
	if(!isset($_POST['name'])){
		$entity = db_easy("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `id`=$entity_id");
		$show_entity_link="<a href='/manager.php?action=show_".$object_singular_eng."&".$object_singular_eng."_id=".$entity_id."&' style='font-size:8pt;'>Просмотреть</a>";
		
		//Build HTML flow
		$html=template_get("points/edit_point", array(		'action'=>"/manager.php?action=edit_point&point=$entity_id",
																'name'=>$entity['name'],
																'address'=>$entity['address'],
																'phone'=>$entity['phone'],
																'branches'=>$branches_html,
																'showpoint'=>$show_entity_link,
																'message'=>$message_html
																));
	}else{
		$entity['name']=trim($_POST['name']);
		$entity['branch_id']=trim($_POST['branch']);
		$do=true;
		//Проверка на пустое название города
		$entity['name']=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $entity['name'])){
			header("location: /manager.php?action=edit_point&point=$entity_id&message=emptypointname");
			$do=false;
		}
		//Проверка на наличие города с таким же именем
		$other_pointRES=db_query("SELECT * FROM `phpbb_points` WHERE `name`='{$entity['name']}'");
		$other_point=db_fetch($other_pointRES);
		if(db_count($other_pointRES)>0){
			if($other_point['id']!=$entity_id){
				header("location: /manager.php?action=edit_point&point=$entity_id&message=samepointexists");
				$do=false;
			}
		}
		if($do){
			db_query("UPDATE `phpbb_points`
					SET `name`='{$entity['name']}',
						`branch_id`={$entity['branch_id']},
						`address`='{$entity['address']}',
						`phone`='{$entity['phone']}'
					WHERE `id`=$entity_id");
			header("location: /manager.php?action=edit_point&point=$entity_id&message=pointsaved");
		}
	}
	return $html;
}
?>