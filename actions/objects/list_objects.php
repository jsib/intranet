<?php
function list_objects(){
	if(isset($_GET['message'])){
		$object_id=trim($_GET['object']);
		$object_name=trim($_GET['name']);
		switch(@$_GET['message']){
			case "object_added_success":
				$message_html=template_get("message", array('message'=>"Добавлен объект \"{$object_name}\""));
			break;
			case "object_deleted":
				$message_html=template_get("message", array('message'=>"Удален объект \"{$object_name}\""));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
	}
	$result_objects = db_query("SELECT * FROM `phpbb_objects` WHERE `id`!=1 ORDER BY `name` ASC");
	$entities_number=db_count($result_objects);
	$object_counter=0;
	$table_html="";
	if(check_rights('delete_object')){
		$th_html="<th class='right'></th>";
	}else{
		$th_html="";
	}
	
	//Look over objects
	while ($object = db_fetch($result_objects)){
		$object_counter++;
		if($object_counter==$entities_number){
			$bottom_class="bottom";
		}else{
			$bottom_class="";
		}
		
		//Check rights for deleting object
		if(check_rights('delete_object')){
			$right_class='';
		}else{
			$right_class='right';
		}
		
		
		$table_html.="	<tr class='$bottom_class'>
							<td><a href='/manager.php?action=show_object&object=".$object['id']."' style='font-size:9pt;'>".$object['name']."</a></td>";

		if(check_rights('delete_object')){
			$table_html.="	<td class='right'><a href='/manager.php?action=delete_object&object={$object['id']}' onclick=\"if(!confirm('Удалить ".$object['name']."?')) return false;\">Удалить</a><br/></td>
						</tr>";
		}
	}
	if(check_rights('add_object')){
		$add_entity_link="<a href='/manager.php?action=add_object' class='listcontacts'>Добавить объект</a><br/><br/>";
	}
	
	//Return HTML flow
	return template_get("objects/list_objects", array(		'add_entity_link'=>$add_entity_link,
															'entities_number'=>$entities_number,
															'table'=>$table_html,
															'message'=>$message_html,
															'th_html'=>$th_html,
															'right_class'=>$right_class
																));
}
?>