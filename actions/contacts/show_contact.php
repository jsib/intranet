<?php
function show_contact(){
	$user=$GLOBALS['user'];
	$user_id=(int)$_GET['contact'];
	

	//НАЧАЛО: Обновление статуса
	if(isset($_POST['status'])){
		$post_status=$_POST['status'];
		db_query("UPDATE `phpbb_profile_fields_data` SET `pf_status`='$post_status' WHERE `user_id`=$user_id");
		$status_update_message=template_get('message', array('message'=>"Статус успешно обновлен"));
	}else{
		$status_update_message="";
	}
	//КОНЕЦ: Обновление статуса
	
	$posts_number=db_easy_count("SELECT * FROM `phpbb_posts` WHERE `poster_id`=$user_id");
	$topics_number=db_easy_count("SELECT * FROM `phpbb_topics` WHERE `topic_poster`=$user_id");
	$contact=db_easy("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id");	
	$status=db_easy("SELECT * FROM `phpbb_profile_fields_data` WHERE `user_id`=$user_id");
	$point=db_easy("SELECT * FROM `phpbb_points` WHERE `id`={$contact['point_id']}");
	$mobilephones_html="";
	if($contact['user_workmobilephone']!=""){
		$mobilephones_html.="<tr><td>Мобильный телефон (рабочий)</td><td>{$contact['user_workmobilephone']}</td>";
	}
	if($contact['user_privatemobilephone']!=""){
		$mobilephones_html.="<tr><td>Мобильный телефон (личный)</td><td>{$contact['user_privatemobilephone']}</td>";
	}
	
	//
	if(check_rights('show_hidden_contacts')){
		$contact['user_type']==9 ? $sql_hidden_contacts="OR `user_type`=9" : $sql_hidden_contacts="";
	}
	
	//Мой руководитель
	if($contact['mychief_id']!=0){
		$mychief=db_easy("SELECT * FROM `phpbb_users` WHERE `user_id`={$contact['mychief_id']}");
		$mychief_html="<tr><td>Руководитель:</td><td><a href='/manager.php?action=show_contact&contact={$contact['mychief_id']}'>{$mychief['username']}</a></td></tr>";
	}else{
		$mychief_html="";
	}
	
	//Мои подчиненные
	$employeesRES=db_query("SELECT * FROM `phpbb_users` WHERE `mychief_id`=$user_id AND `user_type` IN (0,3) ORDER BY `username` ASC");
	
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
		$edit_contact_html="<a href='/manager.php?action=edit_contact&contact=$user_id' style='font-size:8pt;'>Редактировать</a>";
	}
	
	if($user->data['user_id']==$user_id && !check_rights('edit_contact')){
		$status_html="<form action='/manager.php?action=show_contact&contact=$user_id' method='post'>
								<input type='text' name='status' value='{$status['pf_status']}' style='width:350px;' /><br/>
								$status_update_message
								<input type='submit' value='Обновить'  style='margin:6px 0 0 0; width:80px;' />
						</form>";
	}else{
		$status_html=$status['pf_status'];
	}
	
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
		if($a_contact['user_id']==$user_id) $current=$i;
		$i++;
	}
	$previous=$current;$next=$current;$previous_id=$user_id;$next_id=$user_id;
	if($current>0){$previous=$current-1;$previous_id=$all_contacts[$previous];}
	if($current<$count_contacts-1){$next=$current+1;$next_id=$all_contacts[$next];}
	//КОНЕЦ: Переключатели "Следующий" и "Предыдущий"
	
	/*НАЧАЛО: Учет рабочего времени*/
	if(($user->data['user_id']==$user_id ||
			($user->data['timetable_editor']==1 && $contact['my_timetable_editor_id']==$user->data['user_id']) ||
				check_rights('hr_manager')) && $contact['notimetable']!=1){
		/*Отпуск*/
		$vocations=get_days_str($user_id, date("Y"), 2);
		
		/*Больничный*/
		$bolnichny=get_days_str($user_id, date("Y"), 3);
		
		/*За свой счет*/
		$zasvoischet=get_days_str($user_id, date("Y"), 4);
		
		/*Командировка*/
		$travel=get_days_str($user_id, date("Y"), 5);
		
		$uchet_rabochego_vremeni=template_get("contacts/uchet_rabochego_vremeni", array(
																	'vocations_num'=>$vocations['used'],
																	'vocations_rest'=>get_rest($vocations['used_hours'], 20*8),
																	'vocations_str'=>$vocations['when'],
																	'bolnichny_num'=>$bolnichny['used'],
																	'bolnichny_rest'=>get_rest($bolnichny['used_hours'], 5*8),
																	'bolnichny_str'=>$bolnichny['when'],
																	'zasvoischet_num'=>$zasvoischet['used'],
																	'zasvoischet_str'=>$zasvoischet['when'],
																	'travel_num'=>$travel['used'],
																	'travel_str'=>$travel['when']
									));
	}else{
		$uchet_rabochego_vremeni='';
	}
	
	/*КОНЕЦ: Учет рабочего времени*/
	
	$html.=template_get("contacts/show_contact", array(
																'name'=>$contact['username'],
																'occupation'=>$contact['user_occ'],
																'email'=>$contact['user_email'],
																'skype'=>$contact['user_skype'],
																'officephone'=>$point['phone'],
																'extphone'=>$contact['user_extphone'],
																'mobilephones'=>$mobilephones_html,
																'status'=>$status_html,
																'point'=>$point_html,
																'editcontact'=>$edit_contact_html,
																'mychief'=>$mychief_html,
																'employees'=>$employees_html,
																'photo'=>get_user_avatar($contact['user_avatar'], $contact['user_avatar_type'], $contact['user_avatar_width'], $contact['user_avatar_height']),
																'posts_number'=>$posts_number,
																'previous'=>"/manager.php?action=show_contact&contact=$previous_id",
																'next'=>"/manager.php?action=show_contact&contact=$next_id",
																'current'=>($current+1)." из ".$count_contacts,
																'uchet_rabochego_vremeni'=>$uchet_rabochego_vremeni
												));
	return $html;
}

/*Строит список дней из timetable в виде строки*/
function get_days_str($user_id, $year, $status){
	$when="";
	$used="";
	
	$res=db_query("SELECT * FROM `phpbb_timetable`
									WHERE `user_id`=$user_id
											AND `year`=$year
											AND `status`=$status
									ORDER BY `month`, `day` ASC");
	if(db_count($res)>0){
		$when.="Когда: ";
		$iWHILE=0;
		$hours=0;
		
		//WHILE
		while($fetch=db_fetch($res)){
			$iWHILE++;
			//IF
			if($fetch['hours']>=1 && $fetch['hours']<=7){
				$addtext='('.$fetch['hours'].'ч)';
			//ELSE
			}else{
				$addtext='(полный)';
			}
			
			//
			$hours+=$fetch['hours'];
			
			if(strlen($fetch['day'])==1) $fetch['day']="0".$fetch['day'];
			if(strlen($fetch['month'])==1) $fetch['month']="0".$fetch['month'];
			$when.=$fetch['day'].".".$fetch['month'].$addtext."&nbsp;&nbsp;";
			if($iWHILE%10==0) $when.="<br/>";
			
		}
		$when.="<br/>";
		
	}

	$used.=round(($hours-($hours%8))/8, 0).'д ';
	
	if($hours%8!=0){
		$used.=($hours%8).'ч';
	}
	
	return array('when'=>$when, 'used'=>$used, 'used_hours'=>$hours);
}

/*Возвращает количество оставшихся дней/часов в формате строки*/
function get_rest($used_hours, $avail_hours){
	//Определяем переменную
	$str="";
	
	//Определяем переменную
	$hours=$avail_hours-$used_hours;
	
	//Определяем переменную
	$str.=round(($hours-($hours%8))/8, 0).'д ';
	
	//IF
	if($hours%8!=0){
		$str.=($hours%8).'ч';
	}
	
	//Возвращаем значение функции
	return $str;
}
?>