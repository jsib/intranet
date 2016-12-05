<?php
function edit_user(){
	//Retrieve information from this function name
	$function_name_pieces=explode("_", __FUNCTION__);

	//Refer to global variables
	global $Dbh;
	global $table_prefix;
	global $system_objects;

	//Retrieve object properties
	$object_singular_eng=$function_name_pieces[1];
	$object_plural_eng=$system_objects[$object_singular_eng]['plural_name_eng'];
	$object_actions=$system_objects[$object_plural_eng]['actions'];
	
	//Retrieve action properties
	$action_eng=$function_name_pieces[0];
	$action_full_eng=__FUNCTION__;
	
	//Retrieve parent properties in object-action context
	$parent_object_singular_eng=$system_objects[$object_singular_eng]['parent'];
	$parent_object_plural_eng=$system_objects[$parent_object_singular_eng]['plural_name_eng'];

	
	if(!check_rights($action_full_eng)){
		//Return HTML flow
		system_error('permission_denied');
	}
	
	//Retrieve entity_id from browser
	$entity_id=(int)$_GET['entity_id'];
	
	//Retrieve the entity from database
	$entity_db=db_query("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `".$object_singular_eng."_id`=".$entity_id." AND `user_type` IN (0,3,9)");
	
	if(db_count($entity_db)>0){
		$entity=db_fetch($entity_db);
	}else{
		system_error('try_edit_non_existent_entity', array('object'=>$object_singular_eng, 'entity'=>$entity_id));
	}
	
	//No HTML form data
	if(!isset($_POST['name'])){
		//Upload profile photo
		if(isset($_FILES['file'])){
			$photomessage=upload_user_photo($entity_id);
		}
		
		//Build HTML form
		$html=show_form_edit_entity($action_eng, $system_objects, $object_singular_eng, $object_plural_eng, $action_full_eng, $table_prefix, $parent_object_plural_eng, $entity, $messages, $photomessage);
	}else{
		//Retrive entity name from browser
		$entity_name_eng=trim($_POST['name']);
		
		//Check entity name
		if(preg_match(REGEXP_USERNAME, $entity_name_eng)){
			//Entity with same name already exists
			if(db_easy_count("SELECT `".$object_singular_eng."_id` FROM `".$table_prefix.$object_plural_eng."` WHERE `username`='".$entity_name_eng."' AND `".$object_singular_eng."_id`!=$entity_id")>0){
				$errors[]=html_replace($object_actions[$action_eng]['results']['same_entity_exists']['result'], array('name'=>$entity_name_eng));
			}
		//Error in entity name
		}else{
			$errors[]=html_replace($object_actions[$action_eng]['results']['entity_name_error']['result'], array('name'=>$entity_name_eng));
		}
		
		//Build SQL for text fields
		$strings_sql="";
		$strings_params=$object_actions[$action_eng]['form']['text_fields'];
		foreach($strings_params as $nameFOR){
				$strings_sql.="`".$nameFOR."`= :".$nameFOR." , ";
		}
		
		//Build SQL for numeric fields
		$numeric_sql="";
		$numeric_params=$object_actions[$action_eng]['form']['numeric_fields'];
		foreach($numeric_params as $nameFOR){
				$numeric_sql.="`".$nameFOR."`= :".$nameFOR." , ";
		}

		$direction_id=(int)$_POST['direction_id'];
		$boss_id=(int)$_POST['boss_id'];
		
		//Build SQL for checkboxes
		$checkboxes_sql="";
		foreach(array('boss', 'deleted') as $nameFOR){
			if($_POST[$nameFOR]=="on"){
				$checkboxes_sql.="`$nameFOR`=1, ";
			}else{
				$checkboxes_sql.="`$nameFOR`=0, ";
			}
		}
		
		//show($_POST);
		
		//Set user type
		$user_type=0;

		//Check data retrieved from browser
		if(count($errors)==0){
			//Build final SQL
			$sql="	UPDATE
						`".$table_prefix.$object_plural_eng."` 
					SET 
						".$strings_sql."
						".$checkboxes_sql."
						`user_type`=$user_type,
						`direction_id`=$direction_id,
						`boss_id`=$boss_id
					WHERE
						`".$object_singular_eng."_id`=".$entity_id;
					
			show($sql);
			//Готовим выражение
			$sth=$Dbh->prepare($sql);
			
			//Привязываем параметры
			foreach($strings_params as $nameFOR){
				$sth->bindParam(":".$nameFOR, $_POST[$nameFOR], PDO::PARAM_STR);
			}
			
			//Выполняем запрос
			if(!$sth->execute()) show($sth->errorInfo());

				
			/*Обновляем пароль*/
			if(trim($_POST['password'])!=""){
				$sth=$Dbh->prepare("UPDATE `phpbb_users` SET `user_password`= ? WHERE `user_id`=".$entity_id);
				if(!$sth->execute(array(phpbb_hash($_POST['password'])))) show($sth->errorInfo());
			}		
			
			//Retrieve data from database which we just wrote
			$entity=db_easy("SELECT * FROM `".$table_prefix.$object_plural_eng."` WHERE `".$object_singular_eng."_id`=".$entity_id." AND `user_type` IN (0,3,9)");
			//show($entity);
			
			//Возвращаем значение функции
			return show_form_edit_entity($action_eng, $system_objects, $object_singular_eng, $object_plural_eng, $action_full_eng, $table_prefix, $parent_object_plural_eng, $entity, $errors);
		}else{
			//Возвращаем значение функции
			return show_form_edit_entity($action_eng, $system_objects, $object_singular_eng, $object_plural_eng, $action_full_eng, $table_prefix, $parent_object_plural_eng, $entity, $errors);
		}
	}
	
	//Return HTML flow
	return $html;
}

/*Возвращает HTML код формы*/
function show_form_edit_entity($action_eng, $system_objects, $object_singular_eng, $object_plural_eng, $action_full_eng, $table_prefix, $parent_object_plural_eng, $entity=array(), $messages=array(), $photomessage=''){
	//Bind global variables
	global $MonthsShort;
	
	//Retrieve message HTML
	$message_html=show_messages($messages);

	//Определяем переменную
	$show_entity_link_html="<a href='/manager.php?action=show_".$object_singular_eng."&entity_id=".$entity['user_id']."' style='font-size:8pt;'>Просмотреть</a>";

	//Переключатель "Руководитель"
	if($entity['boss']==1){$boss="checked";}else{$boss="";}

	//Переключатель "Показывать удаленные сущности"
	if($entity['deleted']==1){$deleted="checked";}else{$deleted="";}
	
	//Get HTML of lists with parents entities
	$directions_html=get_parents_options($table_prefix, 'directions', $entity['direction_id']);
	//$bosses_html=get_parents_options($table_prefix, 'bosses');

	/*Переключатели "Следующий" и "Предыдущий"*/
	$switch=switch_next_previous($entity['user_id']);

	/*Подключаем шаблон*/
	return template_get($object_plural_eng."/".$action_full_eng, array(
															'page_header'=>$system_objects[$object_singular_eng]['actions'][$action_eng]['full_name_rus'],
															'action_link'=>"/manager.php?action=".$action_full_eng."&entity_id=".$entity['user_id'],
															'show_entity_link'=>$show_entity_link_html,
															'all_entities_link'=>"<a href='/manager.php?action=list_".$object_plural_eng."' class='action_link'>".
																				  $system_objects[$object_singular_eng]['phrases']['all_entities_text']."</a><br/>",
															'name'=>$entity['username'],
															'position'=>$entity['position'],
															'email'=>$entity['email'],
															'phone_mobile'=>$entity['user_officephone'],
															'phone_ext'=>$entity['user_extphone'],
															'message'=>$message_html,
															'directions'=>$directions_html,
															'bosses'=>$bosses_html,
															'boss'=>$boss,															
															'previous'=>"/manager.php?action=".$action_full_eng."&entity_id=".$switch['previous_id'],
															'next'=>"/manager.php?action=".$action_full_eng."&entity_id=".$switch['next_id'],
															'current'=>($switch['current']+1)." из ".$switch['contacts_num'],
															'nocontact'=>$nocontact,
															'photo'=>get_user_avatar($entity['user_avatar'], $entity['user_avatar_type'], $entity['user_avatar_width'], $entity['user_avatar_height']),
															'photomessage'=>$photomessage,
															));
}

/*Загружаем фото пользователя*/
function upload_user_photo($entity_id){
	$file_extension=get_file_extension($_FILES['file']['name']);
	if(db_easy_count("SELECT * FROM `phpbb_avatars` WHERE `user_id`=$entity_id")>0){
		db_query("DELETE FROM `phpbb_avatars` WHERE `user_id`=$entity_id");
	}
	db_query("INSERT INTO `phpbb_avatars` SET `user_id`=$entity_id, `extension`='$file_extension'");
	$file_id=db_insert_id();
	
	$uploadfile=$_SERVER['DOCUMENT_ROOT']."images/avatars/upload/5748d7ff6b4d48da44e8a6525604c781_".$file_id.".".$file_extension;
	if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)){
		$image_size=getimagesize($uploadfile);
		db_query("UPDATE `phpbb_users` SET `user_avatar`='$file_id.$file_extension', `user_avatar_type`=1, `user_avatar_width`={$image_size[0]}, `user_avatar_height`={$image_size[1]} WHERE `user_id`=$entity_id");
		return template_get("message", array('message'=>"Фотография обновлена"));
	}else{
		return template_get("errormessage", array('message'=>"Ошибка"));
	}

}

/*Переключатели "Следующий" и "Предыдущий"*/
function switch_next_previous($entity_id){
	//Определяем переменные
	$previous_html="";
	$next_html="";
	
	//Запрос к БД
	$contactsRES=db_query("SELECT * FROM `phpbb_users`
								WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root'
								ORDER BY `username`
								");
								
	/*Получаем количество контактов*/
	$contacts_num=db_count($contactsRES);
	
	//Определяем переменные
	$i=0;
	$contacts=array();
	
	//WHILE
	while($contactWHILE = db_fetch($contactsRES)){
		//Определяем переменную
		$contacts[$i]=$contactWHILE['user_id'];
		
		//Сокращенный IF-ELSE
		$contactWHILE['user_id']==$entity_id ? $current=$i : '';
		
		//Определяем переменную
		$i++;
	}
	
	//Определяем переменные
	$previous=$current;
	$next=$current;
	$previous_id=$entity_id;
	$next_id=$entity_id;
	
	//IF
	if($current>0){$previous=$current-1;$previous_id=$contacts[$previous];}
	
	//IF
	if($current<$contacts_num-1){$next=$current+1;$next_id=$contacts[$next];}
	
	//Возвращаем значение функции
	return array('next_id'=>$next_id, 'previous_id'=>$previous_id, 'current'=>$current, 'contacts_num'=>$contacts_num);
}

//Get HTML of parents list
function get_parents_options($table_prefix, $parent_object_plural_eng, $parent_entity_id){
	//Определяем переменную
	$options_html="";
	
	//Запрос к БД
	$entities_db=db_query("SELECT * FROM `".$table_prefix.$parent_object_plural_eng."` ORDER BY `name` ASC");
	
	//Build HTML for list of parents
	if(db_count($entities_db)>0){
		//Look over entities
		while($entity_while=db_fetch($entities_db)){
			if($parent_entity_id==$entity_while['id']){
				$selected="selected";
			}else{
				$selected="";
			}
			$options_html.="<option value='{$entity_while['id']}' $selected>{$entity_while['name']}</option>";
		}
	}else{
		
	}
	//Return HTML flow
	return $options_html;
}
?>