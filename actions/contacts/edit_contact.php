<?php
//Подключаем вспомогательный скрипт
require_once($_SERVER['DOCUMENT_ROOT']."/actions/contacts/add_contact.php");

//Функция
function edit_contact(){
	//Глобальная переменная
	global $Dbh;
	
	/*Проверка прав на выполнение действия*/
	if(!check_rights('edit_contact')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	/*Получаем id, предварительно проверив*/
	$user_id=(int)$_GET['contact'];
	$contactRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id AND `user_type` IN (0,3,9)");
	if(db_count($contactRES)>0){
		$contact=db_fetch($contactRES);
	}else{
		$errors[]="Критическая ошибка входных данных (user_id)";
	}
	
	//IF
	if(!isset($_POST['name'])){
		/*Загружаем фото пользователя*/
		if(isset($_FILES['file'])){
			$photomessage=upload_user_photo($user_id);
		}
		
		/*Обновляем информацию о контакте после подгрузки аватара*/
		$contact=db_easy("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id AND `user_type` IN (0,3,9)");
		
		/*Обрабатываем входящее сообщение*/
		switch(@$_GET['message']){
			case "user_added_successfully":
				$message_html=template_get("message", array('message'=>"Сотрудник успешно добавлен"));
			break;
			case "contactsaved":
				$message_html=template_get("message", array('message'=>"Изменения сохранены"));
			break;
			default:
			$message_html=template_get("nomessage");
		}
		
		/*Возвращает HTML код формы*/
		$html.=show_form_edit_contact($contact, $messages, $photomessage);
	}else{
		//Проверка 'name'
		if(preg_match(REGEXP_USERNAME, $_POST['name'])){
			if(db_easy_count("SELECT `user_id` FROM `phpbb_users` WHERE `username`='".$_POST['name']."' AND `user_id`!=$user_id")>0){
				$errors[]=ERROR_USERNAME_EXISTS;
			}else{
				$name=$_POST['name'];
			}
		}else{
			$errors[]=ERROR_USERNAME_REQUIREMENT;
		}
		
		
		//REGEXP_EASY_STRING
		$strings_sql="";
		$strings_params=array('user_occ', 'user_skype', 'user_email', 'user_extphone', 'user_privatemobilephone', 'user_workmobilephone', 'hrmanager_alias');
		foreach($strings_params as $nameFOR){
				$strings_sql.="`".$nameFOR."`= :".$nameFOR." , ";
		}	

		//Числовые поля
		$point_id=(int)$_POST['point'];
		$hire_month=(int)$_POST['hire_month'];
		$hire_year=(int)$_POST['hire_year'];
		$mychief_id=(int)$_POST['mychief'];
		$my_timetable_editor_id=(int)$_POST['my_timetable_editor'];
		
		//Checkbox-ы
		$checkboxes_sql="";
		foreach(array('chief', 'notimetable', 'timetable_editor', 'engineer', 'engineer_chief', 'spec_prod_staff'
						) as $nameFOR){
			if($_POST[$nameFOR]=="on"){
				$checkboxes_sql.="`$nameFOR`=1, ";
			}else{
				$checkboxes_sql.="`$nameFOR`=0, ";
			}
		}
		
		
		//user_type
		$_POST['nocontact']=="on" ? $user_type=9 : $user_type=0;

		//Проверяем наличие ошибок во входных данных
		if(count($errors)==0){
			//Формируем SQL запрос
			$sql="	UPDATE
						`phpbb_users` 
					SET 
						".$strings_sql."
						".$checkboxes_sql."
						`user_type`= $user_type,
						`point_id`=$point_id,
						`mychief_id`=$mychief_id,
						`my_timetable_editor_id`=$my_timetable_editor_id,
						`hire`='{$hire_year}-{$hire_month}-1'
					WHERE
						`user_id`=$user_id";
						
			//show($sql);
			
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
				$sth=$Dbh->prepare("UPDATE `phpbb_users` SET `user_password`= ? WHERE `user_id`=".$user_id);
				if(!$sth->execute(array(phpbb_hash($_POST['password'])))) show($sth->errorInfo());
			}		
			
			/*Обновляем статус*/
			$sth=$Dbh->prepare("UPDATE `phpbb_profile_fields_data` SET `pf_status`= ? WHERE `user_id`=".$user_id);
			if(!$sth->execute(array($_POST['status']))) show($sth->errorInfo());
			
			//Получаем только что записанные данные из БД
			$contact=db_easy("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id AND `user_type` IN (0,3,9)");
			
			//Возвращаем значение функции
			return show_form_edit_contact($contact, $errors);
		}else{
			//Возвращаем значение функции
			return show_form_edit_contact($contact, $errors);
		}
	}
	
	//Возвращаем HTML-код
	return $html;
}

/*Возвращает HTML код формы*/
function show_form_edit_contact($contact=array(), $messages=array(), $photomessage=''){
	//Подключаем глобальные переменные
	global $MonthsShort;
	
	//Определяем значение переменной
	$message_html=show_messages($messages);

	//Определяем переменную
	$show_contact_html="<a href='/manager.php?action=show_contact&contact={$contact['user_id']}' style='font-size:8pt;'>Просмотреть</a>";

	/*Получаем статус*/
	$statusRES=db_query("SELECT * FROM `phpbb_profile_fields_data` WHERE `user_id`={$contact['user_id']}");
	db_count($statusRES)>0 ? $status=db_fetch($statusRES)['pf_status'] : $status='';
	
	//Переключатель "Есть подчиненные"
	if($contact['chief']==1){$chief="checked";}else{$chief="";}

	//Переключатель "Я могу редактировать графики работ"
	if($contact['timetable_editor']==1){$timetable_editor="checked";}else{$timetable_editor="";}
	
	//Переключатель "Не показывать в контактах"
	if($contact['user_type']==9){$nocontact="checked";}else{$nocontact="";}
	
	//Переключатель "Есть подчиненные"
	if($contact['notimetable']==1){$notimetable="checked";}else{$notimetable="";}
	
	//Переключатель "Инженер"
	if($contact['engineer']==1){$engineer="checked";}else{$engineer="";}

	//Переключатель "Руководитель инженеров"
	if($contact['engineer_chief']==1){$engineer_chief="checked";}else{$engineer_chief="";}
	
	//Переключатель "Специальный сотрудник производства"
	if($contact['spec_prod_staff']==1){$spec_prod_staff="checked";}else{$spec_prod_staff="";}

	
	
	/*Получаем список складов/офисов*/
	$points_html=get_points_options($contact);

	/*Получаем список руководителей*/
	$mychiefs_html=get_chiefs_options($contact);

	/*Получаем список редакторов для графика работ*/
	$timetable_editors_html=get_timetable_editors_options($contact);	
	
	/*Переключатели "Следующий" и "Предыдущий"*/
	$switch=switch_next_previous($contact['user_id']);

	//НАЧАЛО: Установка алиаса для HR-manager-а
	if($contact['timetable_editor']==1){
		$hrmanager_alias_html=template_get("contacts/hrmanager_alias", array('hrmanager_alias'=>$contact['hrmanager_alias']));
	}else{
		$hrmanager_alias_html="";
	}
	//КОНЕЦ: Установка алиаса для HR-manager-а
	
	//Запрос к БД
	$contact_hire_date=db_short_easy("SELECT `hire` FROM `phpbb_users` WHERE `user_id`=".$contact['user_id']);
	
	//Получаем список месяцев
	$hire_months_options=get_hire_months_options($contact);

	//НАЧАЛО: Получаем список годов
	strtotime($contact_hire_date) ? $contact_hire_year=(int)date("Y", strtotime($contact_hire_date)) :  $contact_hire_year=(int)date("Y");
	$hire_years_options="";
	for($yearFOR=(int)date("Y");$yearFOR>=1995;$yearFOR--){
		if($contact_hire_year==$yearFOR){
			$selectedFOR="selected";
		}else{
			$selectedFOR="";
		}
		
		$hire_years_options.="<option value='".$yearFOR."' ".$selectedFOR.">".$yearFOR."</option>";
	}
	//КОНЕЦ: Получаем список годов
	
	/*Подключаем шаблон*/
	return template_get("contacts/edit_contact", array(		'action'=>"/manager.php?action=edit_contact&contact=".$contact['user_id'],
															'name'=>$contact['username'],
															'occupation'=>$contact['user_occ'],
															'email'=>$contact['user_email'],
															'skype'=>$contact['user_skype'],
															'officephone'=>$contact['user_officephone'],
															'extphone'=>$contact['user_extphone'],
															'workmobilephone'=>$contact['user_workmobilephone'],
															'privatemobilephone'=>$contact['user_privatemobilephone'],
															'location'=>$contact['user_from'],
															'status'=>$status,
															'message'=>$message_html,
															'points'=>$points_html,
															'showcontact'=>$show_contact_html,
															'previous'=>"/manager.php?action=edit_contact&contact={$switch['previous_id']}",
															'next'=>"/manager.php?action=edit_contact&contact={$switch['next_id']}",
															'current'=>($switch['current']+1)." из ".$switch['contacts_num'],
															'chief'=>$chief,
															'mychiefs'=>$mychiefs_html,
															'nocontact'=>$nocontact,
															'notimetable'=>$notimetable,
															'engineer'=>$engineer,
															'engineer_chief'=>$engineer_chief,
															'spec_prod_staff'=>$spec_prod_staff,
															'timetable_editor'=>$timetable_editor,
															'timetable_editors'=>$timetable_editors_html,
															'photo'=>get_user_avatar($contact['user_avatar'], $contact['user_avatar_type'], $contact['user_avatar_width'], $contact['user_avatar_height']),
															'photomessage'=>$photomessage,
															'hrmanager_alias'=>$hrmanager_alias_html,
															'hire_months_options'=>$hire_months_options,
															'hire_years_options'=>$hire_years_options
															));
}

/*Загружаем фото пользователя*/
function upload_user_photo($user_id){
	$file_extension=get_file_extension($_FILES['file']['name']);
	if(db_easy_count("SELECT * FROM `phpbb_avatars` WHERE `user_id`=$user_id")>0){
		db_query("DELETE FROM `phpbb_avatars` WHERE `user_id`=$user_id");
	}
	db_query("INSERT INTO `phpbb_avatars` SET `user_id`=$user_id, `extension`='$file_extension'");
	$file_id=db_insert_id();
	
	$uploadfile=$_SERVER['DOCUMENT_ROOT']."images/avatars/upload/5748d7ff6b4d48da44e8a6525604c781_".$file_id.".".$file_extension;
	if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)){
		$image_size=getimagesize($uploadfile);
		db_query("UPDATE `phpbb_users` SET `user_avatar`='$file_id.$file_extension', `user_avatar_type`=1, `user_avatar_width`={$image_size[0]}, `user_avatar_height`={$image_size[1]} WHERE `user_id`=$user_id");
		return template_get("message", array('message'=>"Фотография обновлена"));
	}else{
		return template_get("errormessage", array('message'=>"Ошибка"));
	}

}

/*Переключатели "Следующий" и "Предыдущий"*/
function switch_next_previous($user_id){
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
		$contactWHILE['user_id']==$user_id ? $current=$i : '';
		
		//Определяем переменную
		$i++;
	}
	
	//Определяем переменные
	$previous=$current;
	$next=$current;
	$previous_id=$user_id;
	$next_id=$user_id;
	
	//IF
	if($current>0){$previous=$current-1;$previous_id=$contacts[$previous];}
	
	//IF
	if($current<$contacts_num-1){$next=$current+1;$next_id=$contacts[$next];}
	
	//Возвращаем значение функции
	return array('next_id'=>$next_id, 'previous_id'=>$previous_id, 'current'=>$current, 'contacts_num'=>$contacts_num);
}

/*Получаем список складов/офисов*/
function get_points_options($contact){
	//Определяем переменную
	$points_html="";
	
	//Запрос к БД
	$pointsRES=db_query("SELECT * FROM `phpbb_points` ORDER BY `name` ASC");
	
	//IF
	if(db_count($pointsRES)>0){
		//WHILE
		while($pointWHILE=db_fetch($pointsRES)){
			//Сокращенный IF-ELSE
			$contact['point_id']==$pointWHILE['id'] ? $selected="selected" : $selected="";
		
			//Определяем переменную
			$points_html.="<option value='{$pointWHILE['id']}' $selected>{$pointWHILE['name']}</option>";
		}
	}
	
	//Возвращаем значение функции
	return $points_html;
}

//Получаем список месяцев
function get_hire_months_options($contact){
	//Подключаем глобальную переменную
	global $MonthsShort;
	
	//Получаем номер месяца
	strtotime($contact['hire']) ? $contact_hire_month=(int)date("m", strtotime($contact['hire'])) : $contact_hire_month=(int)date("m");
	//show($contact_hire_month);

	//Определяем переменную для HTML-кода
	$html="";
	
	//FOREACH
	foreach($MonthsShort as $month_number=>$month_short_name){
		if($contact_hire_month==$month_number){
			$selectedFOR="selected";
		}else{
			$selectedFOR="";
		}
		$html.="<option value='".$month_number."' ".$selectedFOR.">".$month_short_name."</option>";
	}
	
	//Возвращаем полученный HTML-код
	return $html;
}

/*Получаем список руководителей*/
function get_chiefs_options($contact){
	//Определяем переменные
	$mychiefs_html="";
	
	//Запрос к БД
	$mychiefsRES=db_query("SELECT * FROM `phpbb_users` WHERE `chief`=1 ORDER BY `username` ASC");
	
	//Добавляем первый пункт
	$mychiefs_html.="<option value='0'>--не определено--</option>";

	//IF
	if(db_count($mychiefsRES)>0){
		//WHILE
		while($mychiefWHILE=db_fetch($mychiefsRES)){
			//Сокращенный IF-ELSE
			$contact['mychief_id']==$mychiefWHILE['user_id'] ? $selected="selected" : $selected="";
			
			//Определяем переменную
			$mychiefs_html.="<option value='{$mychiefWHILE['user_id']}' $selected>{$mychiefWHILE['username']}</option>";
		}
	}
	
	//Возвращаем значение функции
	return $mychiefs_html;
}

/*Получаем список редакторов для графика работ*/
function get_timetable_editors_options($contact=false){
	//Определяем переменную
	$timetable_editors_html="";
	
	//Добавляем первый пункт
	$timetable_editors_html.="<option value='0'>--не определено--</option>";
	
	//IF
	if($contact===false){
		//Запрос к БД
		$timetable_editorsRES=db_query("SELECT * FROM `phpbb_users` WHERE `timetable_editor`=1 ORDER BY `username` ASC");
	}else{
		//Запрос к БД
		$timetable_editorsRES=db_query("SELECT * FROM `phpbb_users` WHERE `timetable_editor`=1 ORDER BY `username` ASC");
	}
	
	//IF
	if(db_count($timetable_editorsRES)>0){
		//WHILE
		while($timetable_editor=db_fetch($timetable_editorsRES)){
			//IF
			if($contact!==false){
				//Сокращенный IF-ELSE
				$contact['my_timetable_editor_id']==$timetable_editor['user_id'] ? $selected="selected" : $selected='';
			}
			
			//Определяем переменную
			$timetable_editors_html.="<option value='{$timetable_editor['user_id']}' $selected>{$timetable_editor['username']}</option>";
		}
	}
	
	//Возвращаем значение функции
	return $timetable_editors_html;
}

?>