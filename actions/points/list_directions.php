<?php
//Output list of directions of company 
function list_directions(){ 
	//Define global system variables
	var $table_prefix;
	var $system_actions;
	
	//Define object properties
	$object_singular="direction";
	$object_plural="directions";

	//Generate message for adding entity action
	if(isset($_GET['action_previous']) && isset($_GET['action_previous_result']) && isset($_GET['action_previous_entity_name'])){
		//Get previous action properties from browser
		$action_previous=$_GET['action_previous'];
		$action_previous_result=$_GET['action_previous_result'];
		$action_previous_entity_name=$_GET['action_previous_entity_name'];
		
		//Check does this action and result exist in system
		if(isset($system_actions[$action_previous]['results'][$action_previous_result])){
			//Retrieve previous action result message
			$action_previous_result_message=template_get("message", array('message'=>text_replace($system_actions[$action_previous]['results'][$action_previous_result]['message'], $action_previous_entity_name));
		}else{
			//Handle error
			dis_error(1001);
		}
	}
	
	$db_entities=db_query("SELECT * FROM '`'.$table_prefix.$object_plural.'`' WHERE `system`!=1 ORDER BY `name` ASC");
	$number_entities=db_count($db_entities);
	$entities_counter=0;
	$table_html="";
	if(check_rights('delete_point')){
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
		if(check_rights('delete_point')){$right_class='';}else{$right_class='right';}
		$table_html.="	<tr class='$bottom_class'>
							<td><a href='/manager.php?action=show_point&point={$entity['id']}' style='font-size:9pt;'>".$entity['name']."</a></td>
							<td>".$entity['phone']."</td>
							<td class='$right_class'>".$entity['address']."</td>";
		if(check_rights('delete_point')){
			$table_html.="	<td class='right'><a href='/manager.php?action=delete_point&point={$entity['id']}' onclick=\"if(!confirm('Удалить?')) return false;\">Удалить</a><br/></td>
						</tr>";
		}
	}
	if(check_rights('add_point')){
		$add_entity_link="<a href='/manager.php?action=add_point' class='listcontacts'>Добавить дирекцию</a><br/><br/>";
	}
	$html.=template_get("points/list_points", array(		'add_entity_link'=>$add_entity_link,
															'number_entities'=>$number_entities,
															'table'=>$table_html,
															'add_entity_result_message'=>$add_entity_result_message,
															'th_html'=>$th_html,
															'right_class'=>$right_class
																));
	return $html;
}
?>