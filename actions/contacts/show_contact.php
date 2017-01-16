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
	
	//Get attendance info if user has proper rights
	if( ($auth_user->data['user_id']==$user_id ||
	    ($auth_user->data['timetable_editor']==1 && $user['my_timetable_editor_id']==$auth_user->data['user_id']) ||
	    check_rights('hr_manager')) && $user['notimetable']!=1){	
		
		$replacements['attendance_info']=get_attendance_info($user);
	}else{
		$replacements['attendance_info']='';
	}
	
	//Get extra attendance info
	if(check_rights('hr_manager') && $user['notimetable']!=1){
		$replacements['extra_attendance_info']=get_extra_attendance_info($user);
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
	$when="";
	
	//Here we store information how many days and hours were spent
	$used="";
	
	//Get attendance info from database
	$attendance_res=db_query('SELECT * FROM `phpbb_timetable`
					        WHERE `user_id`='.$user_id.'
								  AND `year`='.$year.'
								  AND `status`='.$status.'
							ORDER BY `month`, `day` ASC'
				 );
	
	//If there are some attendance gaps
	if(db_count($attendance_res)>0){
		//Write 'when' information only if there are some gaps
		$when.='Когда: ';
		
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
		
		//Put line wrapping only if there are some gaps
		$when.='<br/>';
	}
	
	//Get how many gays were spent
	$used.=round(($hours-($hours%8))/8, 0).'д ';
	
	//Get how many hours were spent
	if($hours%8!=0){
		$used.=($hours%8).'ч';
	}
	
	//Return
	return array('when'=>$when, 'used'=>$used);
}

//Get user's hire info
function get_hire_info($user){
	//In case of uknown hire date we don't give vacation days to employee
	if($user['hire']=='0000-00-00'){
		$user_hire_date='<span class="attention">не определено</span>';
		$months_work=0;
		$days_work=0;
		$days_work_in_this_year=0;
	//If hire date is defined
	}else{
		//Get hire property of user which stored in database
		$user_hire_month=date("m", strtotime($user['hire']));
		$user_hire_year=date("Y", strtotime($user['hire']));
		
		//Get formatted date when employee begin working
		$user_hire_date=$user_hire_month.'.'.$user_hire_year;
		
		//Get number of days which employee has worked already in this year
		if($user_hire_year==date('Y')){
			//If user start working this year
			$count_from_begin_of_this_year=false;			
			
			//Count days from hire date
			$days_work_in_this_year=(int)((strtotime('now')-strtotime(date('01.'.$user_hire_month.'.'.$user_hire_year)))/(60*60*24));
		}else{
			//If user start working one of previous years
			$count_from_begin_of_this_year=true;	
			
			//Count days from 1st of Junuary of current year
			$days_work_in_this_year=(int)((strtotime('now')-strtotime(date('01.01.Y')))/(60*60*24));
		}
		
		//Get average days number per month in current year
		$days_aver=(date('L')?366:365)/12;
		
		//Get number of months and rest of days which employee works
		$months_work=(int)($days_work_in_this_year/$days_aver);
		$days_work=$days_work_in_this_year%$days_aver;
		
	}

	//Get hire info
	$hire_info['user_hire_date']=$user_hire_date;
	$hire_info['count_from_begin_of_this_year']=$count_from_begin_of_this_year;
	$hire_info['months_work']=$months_work;
	$hire_info['days_work']=$days_work;
	$hire_info['days_work_in_this_year']=$days_work_in_this_year;
	
	//Return hire info
	return $hire_info;
}

//Get user's vacations, sick leave credit days number
function get_credit_info($user, $days_credit_norm, $days_work_in_this_year, $year){
	//For not current year, this option we use for reports, when we need report for previous years
	if($year==date('Y')){
		//Get average days number per month in current year
		$days_aver=(date('L')?366:365)/12;
		
		//Get credit in hours. Here we rely on $days_work_in_this_year variable which show how many days employee work in this year
		$credit_hours_total=(int)(($days_credit_norm/12)*($days_work_in_this_year/$days_aver)*8);

		//Get credit in days with hours
		$credit_days=(int)($credit_hours_total/8);
		$credit_hours=$credit_hours_total%8;

	//For current year, this option we use for extra attendance information in user card
	}else{
		$credit_days=$days_credit_norm;
		$credit_hours=0;
		$credit_hours_total=$credit_days*8;
	}
	
	//Get credit info
	$credit_info['credit_days']=$credit_days;
	$credit_info['credit_hours']=$credit_hours;
	$credit_info['credit_hours_total']=$credit_hours_total;
	
	//Return credit info
	return $credit_info;
}

//Get HTML with information about rest of vacation, sick leave, etc
function get_extra_attendance_info($user){
	//Bind global variables
	$auth_user=$GLOBALS['user'];
	
	//Activate smarty
	$smarty=new Smarty();
	
	//Define credits info array
	$credits_info=array();
	
	//Put user hire date to smarty
	$smarty->assign('user_hire',$user['hire']);
	
	//Get hire and credit attendance info
	if($user['hire']!='0000-00-00'){
		//Get hire info
		$hire_info=get_hire_info($user);
		
		//Put hire info to smarty
		$smarty->assign('hire_info',$hire_info);
		
		//Get vacations and sick leave credits info
		foreach(array('vacation'=>VACATION_DAYS_CREDIT, 'sickleave'=>SICKLEAVE_DAYS_CREDIT) as $attendance_type=>$days_credit_norm){
			$credits_info[$attendance_type]=get_credit_info($user, $days_credit_norm, $hire_info['days_work_in_this_year'], date('Y'));
		}
		
		//Put credit info to smarty
		$smarty->assign('credits_info', $credits_info);
		
		//Get number of transferred vacation credit from previous year
		$transfer_days_res=db_query('SELECT `days_number`
		                             FROM `phpbb_transferred_attendance_credit`
		                             WHERE `user_id`='.$user['user_id'].
									       ' AND `year`='.date('Y')
								   );
								   
		if(db_count($transfer_days_res)>0){
			//Take days number from database
			$transfer_days_number=db_fetch($transfer_days_res)['days_number'];
		}else{
			//Put default value
			$transfer_days_number=0;
		}
		
		//Put number of transferred vacation credit to smarty
		$smarty->assign('transfer_days_number', $transfer_days_number);
		
		//Build "since" phrase
		if($hire_info['count_from_begin_of_this_year']===true){
			$replacements['since_phrase']='с начала этого года';
		}else{
			$replacements['since_phrase']='с момента устройства на работу';
		}
	}
	
	//Return HTML flow
	return $smarty->fetch('contacts/extra_attendance_info.tpl');
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

//Get attendance of employees info
function get_attendance_info($user){
	//User id helper
	$user_id=$user['user_id'];
	
	//Define HTML flow
	$info_html='';

	//Iterate over attendance statuses	
	foreach(array(2=>'Отпуск', 3=>'Больничный', 4=>'За свой счёт', 5=>'Командировка') as $status_id_for=>$status_name_for){
		//Collect attendance info for this status
		$info=get_attendance_gaps($user_id, date("Y"), $status_id_for);
		
		//Build HTML piece
		$info_html.=template_get('contacts/attendance_info_item',
								 array('name'=>$status_name_for,
									   'used'=>$info['used'],
									   'when'=>$info['when']
								 )
					);
	}
	
	//Return summary HTML
	return template_get('contacts/attendance_info', array('info'=>$info_html));
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