<?php
function show_user(){
	//Bind global variables
	global $Dbh;
	global $table_prefix;
	global $system_objects;

	//Retrieve information from this function name
	$function_name_pieces=explode("_", __FUNCTION__);

	//Retrieve object and action names
	$object_name=$function_name_pieces[1];
	$action_name=$function_name_pieces[0];
	
	//Retrieve objects helper properties
	$object=$system_objects[$object_name];
	$object_name_plural=$system_objects[$object_name]['plural_name_eng'];
	
	//Retrieve action helper properties
	$object_actions=$system_objects[$object_name]['actions'];
	$object_action=$object_actions[$action_name];
	$action_full_name=$action_name."_".$object_name;
	
	//Retrieve parent object helper properties
	$parent_object_name=$system_objects[$object_name]['parent'];
	$parent_object=$system_objects[$parent_object_name];
	$parent_object_name_plural=$system_objects[$parent_object_name]['plural_name_eng'];
	
	//Retrieve entity id from browser
	$entity_id=(int)$_GET['entity_id'];
	
	//Retrieve entity data from database
	$entity=db_easy("SELECT * FROM `".$table_prefix.$object_name_plural."` WHERE `".$object_name."_id`=".$entity_id);
	
	if(check_rights('show_deleted_'.$object_name_plural)){
		$entity['deleted']==1 ? $sql_hidden_contacts="OR `deleted`=1" : $sql_hidden_contacts="";
	}
	
	//Build parents HTML
	$parents_html=get_parent_html($object_name, $action_name, 'direction', 'Руководитель', $entity);
	
	//Build children HTML
	$children_html="";
	
	//Build all entities link
	$all_entities_link="<a href='/manager.php?action=list_".$object_name_plural."' class='action_link'>".$object['phrases']['all_entities_text']."</a><br/>";
	
	//Build edit entity link
	$edit_entity_link="<a href='/manager.php?action=edit_".$object_name."&entity_id=".$entity_id."' class='action_link'>Редактировать</a>";
	
	$photo=get_user_avatar($entity['user_avatar'], $entity['user_avatar_type'], $entity['user_avatar_width'], $entity['user_avatar_height']);
	$previous_entity_id='';
	$next_entity_id='';
	$entity_number=1;
	$entities_number=1;
	
	//Return HTML flow
	return template_get($object_name_plural."/".$action_full_name,
						array(
							'all_entities_link'=>$all_entities_link,
							'edit_entity_link'=>$edit_entity_link,
							'name'=>$entity['username'],
							'position'=>$entity['position'],
							'email'=>$entity['email'],
							'phone_ext'=>$entity['phone_ext'],
							'parents'=>$parents_html,
							'children'=>$children_html,
							'phone_mobile'=>$entity['phone_mobile'],
							'photo'=>'',
							'previous_entity_link'=>"/manager.php?action=".$action_full_name."&entity_id=".$previous_entity_id,
							'next_entity_link'=>"/manager.php?action=".$action_full_name."&entity_id=".$next_entity_id,
							'entity_number'=>($entity_number+1)." из ".$entities_number
						));
}

function get_parent_html($object_name, $action_name, $parent_object_name, $parent_object_name_rus, $entity){
	//Bind global variables
	global $table_prefix;
	global $system_objects;
	
	//Retrieve objects helper properties
	$object_name_plural=$system_objects[$object_name]['plural_name_eng'];
	
	//Retrieve action helper properties
	$object_actions=$system_objects[$object_name]['actions'];
	$object_action=$object_actions[$action_name];
	$action_full_name=$action_name."_".$object_name;
	
	//Retrieve parent object helper properties
	$parent_object=$system_objects[$parent_object_name];
	$parent_object_name_plural=$system_objects[$parent_object_name]['plural_name_eng'];
	
	//Retrieve entity helper properties
	$entity_id=$entity['user_id'];
	$parent_entity_id=$entity[$parent_object_name.'_id'];
	if($parent_entity_id!=0){
		$parent=db_easy("SELECT * 
						 FROM `".$table_prefix.$parent_object_name_plural."`
						 WHERE `id`=".$parent_entity_id
						);
		$parent_link="<a href='/manager.php?action=show_".$parent_object_name."&entity_id=".$parent['id']."'>".$parent['name']."</a>";
		
		$parent_html=template_get($object_name_plural."/".$action_full_name."_row", array(
									'object_name_rus'=>$parent_object_name_rus,
									'entity_name_rus'=>$parent['name']
								 ));
	}else{
		$parent_html="";
	}
	
	//Return HTML flow
	return $parent_html;
}

function get_children_html(){
	//Мои подчиненные
	$employeesRES=db_query("SELECT * FROM `phpbb_users` WHERE `mychief_id`=$entity_id AND `user_type` IN (0,3) ORDER BY `username` ASC");
	
	if(db_count($employeesRES)>0 && $contact['chief']==1){
		$employees_html="<tr><td valign='top'>Подчиненные:</td><td>";
		while($employee=db_fetch($employeesRES)){
			$employees_html.="<a href='/manager.php?action=show_contact&contact=".$employee['user_id']."'>".$employee['username']."</a><br/>";
		}
		$employees_html.="</td></tr>";
	}else{
		$employees_html="";
	}
	
	if($point['name']=="" || $point['name']=="--не определено--"){
		$point_html="не определено";
	}else{
		$point_html="<a href='/manager.php?action=show_point&point={$point['id']}'>{$point['name']}</a>";
	}
	if(check_rights('edit_contact')){
		$edit_contact_html="<a href='/manager.php?action=edit_contact&contact=$entity_id' style='font-size:8pt;'>Редактировать</a>";
	}
	
	if($user->data['user_id']==$entity_id && !check_rights('edit_contact')){
		$status_html="<form action='/manager.php?action=show_contact&contact=$entity_id' method='post'>
								<input type='text' name='status' value='{$status['pf_status']}' style='width:350px;' /><br/>
								$status_update_message
								<input type='submit' value='Обновить'  style='margin:6px 0 0 0; width:80px;' />
						</form>";
	}else{
		$status_html=$status['pf_status'];
	}
}

function next_previous_switch(){
	//НАЧАЛО: Переключатели "Следующий" и "Предыдущий"
	$previous_html="";$next_html="";
	$all_contactsRES=db_query("SELECT * FROM `phpbb_users`
								WHERE (`user_type`=0 OR `user_type`=3 $sql_hidden_contacts) AND `username`!='root'
									ORDER BY `username`
										");
										
	$count_contacts=db_count($all_contactsRES);
	$i=0;$all_contacts=array();
	while ($a_contact = db_fetch($all_contactsRES)){
		$all_contacts[$i]=$a_contact['user_id'];
		if($a_contact['user_id']==$entity_id) $current=$i;
		$i++;
	}
	$previous=$current;$next=$current;$previous_id=$entity_id;$next_id=$entity_id;
	if($current>0){$previous=$current-1;$previous_id=$all_contacts[$previous];}
	if($current<$count_contacts-1){$next=$current+1;$next_id=$all_contacts[$next];}
	//КОНЕЦ: Переключатели "Следующий" и "Предыдущий"	
}
?>