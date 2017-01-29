<?php
function show_contact(){
	//Bind global variable
	$auth_user=$GLOBALS['user'];
	
	//Get user id from browser
	$user_id=(int)$_GET['contact'];
	

	//Update user status. Should be before fetching $status from database!!!
	if(isset($_POST['status'])){
		$post_status=$_POST['status'];
		db_query('UPDATE `phpbb_profile_fields_data` SET `pf_status`="'.$post_status.'" WHERE `user_id`='.$user_id);
		$status_update_message=template_get('message', array('message'=>"Статус успешно обновлен"));
	}else{
		$status_update_message="";
	}
	
	//Get some forum information about user
	$posts_number=db_easy_count('SELECT * FROM `phpbb_posts` WHERE `poster_id`='.$user_id);
	$topics_number=db_easy_count('SELECT * FROM `phpbb_topics` WHERE `topic_poster`='.$user_id);
	
	//Get main user info
	$user=db_easy('SELECT * FROM `phpbb_users` WHERE `user_id`='.$user_id);	
	
	//Get user status
	$status=db_easy('SELECT * FROM `phpbb_profile_fields_data` WHERE `user_id`='.$user_id);
	
	//Get point related information
	$point=db_easy('SELECT * FROM `phpbb_points` WHERE `id`='.$user['point_id']);
	
	//Get user mobile phone
	$mobilephones_html="";
	if($user['user_workmobilephone']!=""){
		$mobilephones_html.='<tr><td>Мобильный телефон (рабочий)</td><td>'.$user['user_workmobilephone'].'</td>';
	}
	if($user['user_privatemobilephone']!=""){
		$mobilephones_html.='<tr><td>Мобильный телефон (личный)</td><td>'.$user['user_privatemobilephone'].'</td>';
	}
		
	//Get user's chief
	if($user['mychief_id']!=0){
		$mychief=db_easy('SELECT * FROM `phpbb_users` WHERE `user_id`='.$user['mychief_id']);
		$mychief_html='<tr><td>Руководитель:</td><td><a href="/manager.php?action=show_contact&contact='.$user['mychief_id'].'">'.$mychief['username'].'</a></td></tr>';
	}else{
		$mychief_html="";
	}
	
	//Get user's employees
	$replacements['employees']=get_user_employees($user);
	
	if($point['name']=="" || $point['name']=="--не определено--"){
		$point_html="не определено";
	}else{
		$point_html='<a href="/manager.php?action=show_point&point='.$point['id'].'">'.$point['name'].'</a>';
	}
	
	//Build edit user link
	if(check_rights('edit_contact')){
		$replacements['edit_user_link']='<a href="/manager.php?action=edit_contact&contact='.$user_id.'" style="font-size:8pt;">Редактировать</a>';
	}else{
		$replacements['edit_user_link']='';
	}
	
	//Build list users link
	$replacements['list_users_link']='<a href="/manager.php?action=list_contacts" class="page">Все сотрудники</a>';
	
	//Build edit status form for usual users
	if($auth_user->data['user_id']==$user_id && !check_rights('edit_contact')){
		$status_html=template_get('contacts/status', array('action'=>'/manager.php?action=show_contact&contact='.$user_id,
														   'status'=>$status['pf_status'],
														   'update_result_message'=>$status_update_message,
		                         ));
	}else{
		$status_html=$status['pf_status'];
	}
	
	//Get users previous id and next id for user switcher 
	$switcher=get_users_switcher($user);
	
	//Get attendance info
	if(check_rights('hr_manager') || $auth_user->data['user_id']==$user['user_id']){
		$replacements['extra_attendance_info']=get_attendance_info($user);
	}else{
		$replacements['extra_attendance_info']='';
	}
	
	//Return HTML flow
	return template_get('contacts/show_contact', array(	'officephone'=>$point['phone'],
														'extphone'=>$user['user_extphone'],
														'mobilephones'=>$mobilephones_html,
														'status'=>$status_html,
														'point'=>$point_html,
														'mychief'=>$mychief_html,
														'photo'=>get_user_avatar($user['user_avatar'],
																				 $user['user_avatar_type'],
																				 $user['user_avatar_width'],
																				 $user['user_avatar_height']
																				),
												) + $replacements + $user + $switcher);
}

//Get number of attendance gaps for specified user, year and status
function get_attendance_gaps($user_id, $year, $status){
	//Here we store date when user had a attendance gap
	$when='';
	
	//Get attendance info from database
	$attendance_res=db_query('SELECT * FROM `phpbb_timetable`
					        WHERE `user_id`='.$user_id.'
								  AND `year`='.$year.'
								  AND `status`='.$status.'
							ORDER BY `month`, `day` ASC'
				 );
	
	//If there are some attendance gaps
	if(db_count($attendance_res)>0){
		//Line wrapper flag
		$lw_flag=0;
		
		//Hours which user spent
		$hours=0;
		
		//Iterate over attendance results
		while($fetch=db_fetch($attendance_res)){
			
			//Increase line wrapper flag value
			$lw_flag++;
			
			//If attendance gap is not full day
			if($fetch['hours']>=1 && $fetch['hours']<=7){
				$addtext='('.$fetch['hours'].'ч)';
			//For full day attendance gap
			}else{
				$addtext='(полный)';
			}
			
			//Calculate total number of hours for this year
			$hours+=$fetch['hours'];
			
			//Add '0' before single-digit day numbers
			if(strlen($fetch['day'])==1) $fetch['day']="0".$fetch['day'];
			
			//Add '0' before single-digit month numbers
			if(strlen($fetch['month'])==1) $fetch['month']="0".$fetch['month'];
			
			//Get when gap was the case
			$when.=$fetch['day'].".".$fetch['month'].$addtext."&nbsp;&nbsp;";
			
			//Add line wrapping if there are more then 10 records
			if($lw_flag%10==0) $when.="<br/>";
		}
	}
	
	//Get how many gays were spent
	$used_days=($hours-($hours%8))/8;
	
	//Get how many hours were spent
	$used_hours=$hours%8;
	
	//Return
	return array('when'=>$when, 'used_days'=>$used_days, 'used_hours'=>$used_hours, 'hours'=>$hours);
}

//Get HTML with information about rest of vacation, sick leave, etc
function get_attendance_info($user){
	//Bind global variables
	$auth_user=$GLOBALS['user'];
	$attendance_config=$GLOBALS['configuration']['attendance'];
	
	//Activate smarty
	$smarty=new Smarty();
	
	//Define credits info array
	$credits_info=array();
	
	//Put user hire date to smarty
	$smarty->assign('user_hire',$user['hire']);
	
	//Get hire and credit attendance info
	if($user['hire']!='0000-00-00'){
		//Get hire info
		$user_hire=new UserHire($user);
		$hire_info=$user_hire->get_info();
		
		//Put hire info to smarty
		$smarty->assign('hire_info',$hire_info);
		
		//Put attandance benefits information to smarty
		foreach($attendance_config as $status_for=>$attendance_for){
			//Get attendance benefits object
			$attendance_benefit = new AttendanceBenefits($user, date('Y'), $status_for);
			
			//Put number of survive attendance benefits
			$attendance_benefit->get_survive_benefits();
			$smarty->assign( $attendance_for['name'] . '_survive_benefits',  $attendance_benefit->survive_benefits );
			 
			//Put number of granted attendance benefits
			$attendance_benefit->get_granted_benefits();
			$smarty->assign( $attendance_for['name'] . '_granted_benefits', $attendance_benefit->granted_benefits ); 
		}
		
		//Build "since" phrase
		if($hire_info['count_from_begin_of_this_year']===true){
			$replacements['since_phrase']='с начала этого года';
		}else{
			$replacements['since_phrase']='с момента устройства на работу';
		}
		
		//Put 'show_hr_information' rights to smarty
		if(check_rights('hr_manager') && $user['notimetable']!=1){
			$smarty->assign('show_hr_information', true);
		}else{
			$smarty->assign('show_hr_information', false);
		}
		
		foreach(array(2=>'Отпуск', 3=>'Больничный', 4=>'За свой счёт', 5=>'Командировка', 10=>'Переработка', 11=>'Оплачиваемая командировка') as $status_id_for=>$status_name_for){
			//Collect attendance info for this status
			$attendance_info[$status_id_for]=get_attendance_gaps($user['user_id'], date("Y"), $status_id_for);
		}
		
		//Put attendance info to smarty
		$smarty->assign('attendance_info', $attendance_info);
	}
	
	//Return HTML flow
	return $smarty->fetch('contacts/attendance_info.tpl');
}
	
//Get HTML with info about user's subordinates
function get_user_employees($user){
	//Get user's employees information from database
	$employees_res=db_query('SELECT * FROM `phpbb_users` WHERE `mychief_id`='.$user['user_id'].' AND `user_type` IN (0,3) ORDER BY `username` ASC');
	
	//There are some subordinates 
	if(db_count($employees_res)>0 && $user['chief']==1){
		//Build header
		$html="<tr><td valign='top'>Подчиненные:</td><td>";
		
		//Build subordinate's name and link HTML
		while($employee=db_fetch($employees_res)){
			$html.='<a href="/manager.php?action=show_contact&contact='.$employee['user_id'].'">'.$employee['username']."</a><br/>";
		}
		
		$html.="</td></tr>";
	//This user is not chief or user doesn't have subordinates now
	}else{
		$html="";
	}
	
	//Return HTML piece
	return $html;
}

//Get users switcher
function get_users_switcher($user){
	//User id helper
	$user_id=$user['user_id'];
	
	//Build "Next"/"Previous" switcher
	if(check_rights('show_hidden_contacts')){
		$user['user_type']==9 ? $sql_hidden_contacts="OR `user_type`=9" : $sql_hidden_contacts="";
	}
	
	//Query database
	$users_res=db_query('SELECT * FROM `phpbb_users`
								      WHERE (`user_type`=0 OR `user_type`=3 '.$sql_hidden_contacts.')
									        AND `username`!=\'root\'
									  ORDER BY `username`'
						   );
	
	
	//Get number of users
	$number_users=db_count($users_res);
	
	//Ordinal user number
	$user_number=0;
	
	//Define array for users got from database
	$users=array();
	
	//Iterate over users we got from database
	while($user_while = db_fetch($users_res)){
		//Build switching list array
		$users[$user_number]=$user_while['user_id'];
		
		//Identify current_id user from switching list
		if($user_while['user_id']==$user_id){
			$current_id=$user_number;
		}
		
		//Increse ordinal number of user
		$user_number++;
	}
	
	$previous=$current_id;
	$next=$current_id;
	$previous_id=$user_id;
	$next_id=$user_id;
	
	
	if($current_id>0){
		$previous=$current_id-1;
		$previous_id=$users[$previous];
	}
	
	if($current_id<$number_users-1){
		$next=$current_id+1;
		$next_id=$users[$next];
	}
	
	//Return switcher links
	return array('previous'=>'/manager.php?action=show_contact&contact='.$previous_id,
	             'next'=>'/manager.php?action=show_contact&contact='.$next_id,
	             'current'=>($current_id+1).' из '.$number_users
				);
}


?>