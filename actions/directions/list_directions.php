<?php
//Show list of directions of company 
function list_directions(){ 
	//Retrieve information from this function name
	$function_name_pieces=explode("_", __FUNCTION__);

	//Refer to global variables
	global $table_prefix;
	global $system_objects;
	global $system_actions;
	
	//Retrieve object properties
	$object_plural_eng=$function_name_pieces[1];
	$object_singular_eng=$system_objects[$object_plural_eng]['singular_name_eng'];

	//Retrieve action properties
	$action_eng=$function_name_pieces[0];

	//Generate message for adding entity action
	if(isset($_GET['action_previous']) && isset($_GET['action_previous_result']) && isset($_GET['action_previous_entity_name'])){
		//Get previous action properties from browser
		$action_previous=$_GET['action_previous'];
		$action_previous_result=$_GET['action_previous_result'];
		$action_previous_entity_name=$_GET['action_previous_entity_name'];
		
		//Check does this action and result exist in system
		if(isset($system_actions[$action_previous]['results'][$action_previous_result])){
			//Retrieve previous action result message
			$action_previous_result_message=template_get("message", array('message'=>html_replace($system_actions[$action_previous]['results'][$action_previous_result]['message'], array('name'=>$action_previous_entity_name))));
		}else{
			//Handle error
			dis_error("This action or result doesn't exist in system", 'echo'); 
		}
	}
	
	//Retrieve entities from database
	$db_entities=db_query("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `system`!=1 ORDER BY `name` ASC");
	
	//Count number of retrieved from database entities
	$number_entities=db_count($db_entities);
	
	//Define entities counter
	$entities_counter=0;
	
	//Define HTML flow for table
	$table_html="";
	
	if(check_rights('delete_'.$object_singular_eng)){
		$th_html="	<th class='right'></th>";
	}else{
		$th_html="";
	}
	while ($entity = db_fetch($db_entities)){
		$entities_counter++;
		if($entities_counter==$number_entities){
			$bottom_class="bottom";
		}else{
			$bottom_class="";
		}
		
		if(check_rights('delete_'.$object_singular_eng)){$right_class='';}else{$right_class='right';}

		//
		$table_html.="	<tr class='$bottom_class'>
							<td><a href='/manager.php?action=show_point&point={$entity['id']}' style='font-size:9pt;'>".$entity['name']."</a></td>
							<td>".$entity['phone']."</td>
							<td class='$right_class'>".$entity['address']."</td>";
		if(check_rights('delete_'.$object_singular_eng)){
			$table_html.="	<td class='right'><a href='/manager.php?action=delete_".$object_singular_eng."&point={$entity['id']}' onclick=\"if(!confirm('Удалить?')) return false;\">Удалить</a><br/></td>
						</tr>";
		}
	}
	if(check_rights('add_'.$object_singular_eng)){
		$add_entity_link="<a href='/manager.php?action=add_".$object_singular_eng."' class='listcontacts'>Добавить дирекцию</a><br/><br/>";
	}
	
	$html=template_get("directions/list_directions", array(	'add_entity_link'=>$add_entity_link,
																'number_entities'=>$number_entities,
																'table_html'=>$table_html,
																'action_previous_result_message'=>$action_previous_result_message,
																'th_html'=>$th_html,
																'right_class'=>$right_class
															));
															
	//echo "ht|".$html."|ml";
	return $html;
}
?>