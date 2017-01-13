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
																	'vocations_str'=>$vocations['when'],
																	'bolnichny_num'=>$bolnichny['used'],
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
	
	if(check_rights('hr_manager') && $contact['notimetable']!=1){
		$raschet_rabochego_vremeni=get_hire_credit_info($contact);
	}
	
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
																'uchet_rabochego_vremeni'=>$uchet_rabochego_vremeni,
																'raschet_rabochego_vremeni'=>$raschet_rabochego_vremeni
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

//Get user's hire info
function get_hire_info($user){
	//In case of uknown hire date we don't give vacation days to employee
	if($user['hire']=='0000-00-00'){
		$user_hire_date='<span class="attention">не определено</span>';
		$months_work=0;
		$days_work=0;
		$days_work_total=0;
	//If hire date is defined
	}else{
		//Get hire property of user which stored in database
		$user_hire_month=date("m", strtotime($user['hire']));
		$user_hire_year=date("Y", strtotime($user['hire']));
		
		//Get formatted date when employee begin working
		$user_hire_date=$user_hire_month.'.'.$user_hire_year; 
		
		//Get number of days which employee works till now since hire date
		if($user_hire_year==date('Y')){
			$days_work_total=(int)((strtotime('now')-strtotime(date('01.'.$user_hire_month.'.'.$user_hire_year)))/(60*60*24));
			
		//Or 1st of Junuary
		}else{
			$days_work_total=(int)((strtotime('now')-strtotime(date('01.01.Y')))/(60*60*24));
		}
		
		//Get average days number per month in current year
		$days_aver=(date('L')?366:365)/12;
		
		//Get number of months and rest of days which employee works 
		$months_work=(int)($days_work_total/$days_aver);
		$days_work=$days_work_total%$days_aver;
		
	}

	//Get hire info
	$hire_info['user_hire_date']=$user_hire_date;
	$hire_info['months_work']=$months_work;
	$hire_info['days_work']=$days_work;
	$hire_info['days_work_total']=$days_work_total;
	
	//Return hire info
	return $hire_info;
}

//Get user's vacations, sick leave credit days number
function get_credit_info($user, $object, $days_credit_norm, $days_work_total, $year=false){
	//For not current year
	if($year==date('Y')){
		//In case of uknown hire date we don't give vacation days to employee
		if($user['hire']=='0000-00-00'){
			$credit_days=0;
			$credit_hours=0;
		//If hire date is defined
		}else{
			//Get average days number per month in current year
			$days_aver=(date('L')?366:365)/12;
			
			//Get credit in hours
			$credit_hours_total=(int)(($days_credit_norm/12)*($days_work_total/$days_aver)*8);

			//Get credit in days with hours
			$credit_days=(int)($credit_hours_total/8);
			$credit_hours=$credit_hours_total%8;
		}
	//For current year
	}else{
		$credit_days=$days_credit_norm;
		$credit_hours=0;
		$credit_hours_total=$credit_days*8;
	}
	
	//Get credit info
	$credit_info[$object.'_credit_days']=$credit_days;
	$credit_info[$object.'_credit_hours']=$credit_hours;
	$credit_info[$object.'_credit_hours_total']=$credit_hours_total;
	
	//Return credit info
	return $credit_info;
}

//Get HTML with information about rest of vacation, sick leave, etc
function get_hire_credit_info($user){
	//Define credits info array
	$credits_info=array();
	
	//Get hire info
	$hire_info=get_hire_info($user);
	
	//Get vacations and sick leave credits info
	foreach(array('vacation'=>VACATION_DAYS_CREDIT, 'sickleave'=>SICKLEAVE_DAYS_CREDIT) as $object=>$days_credit_norm){
		$credits_info=$credits_info+get_credit_info($user, $object, $days_credit_norm, $hire_info['days_work_total']);
	}
	
	//Return HTML flow
	return template_get("contacts/raschet_rabochego_vremeni", $hire_info+$credits_info);
}

?>