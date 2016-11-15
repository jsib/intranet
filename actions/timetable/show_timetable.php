<?php
function show_timetable(){
	//Получаем глобальные переменные
	global $Month;
	global $Year;
	global $Redactor;
	global $user;
	global $MonthsFull;
	
	//Передаем флаг шефа инженеров в cookie
	setcookie("engineer_chief", $user->data['engineer_chief']);
	
	/*Получаем данные от пользователя*/
	if(isset($_GET['month'])){
		if(!preg_match("/^[0-9]{1,2}$/", $_GET['month'])){
			return "Ошибка в формате входных данных (1).";
		}else{
			$Month=(int)$_GET['month'];
		}
	}else{
		$Month=(int)date("m");
	}

	/*Получаем данные от пользователя*/
	if(isset($_GET['year'])){
		if(!preg_match("/^[0-9]{4}$/", $_GET['year'])){
			return "Ошибка в формате входных данных (2).";
		}else{
			$Year=(int)$_GET['year'];
		}
	}else{
		$Year=(int)date("Y");
	}

	/*Получаем данные от пользователя*/
	if(isset($_GET['redactor'])){
		$Redactor=(int)$_GET['redactor'];
	}else{
		if($user->data['timetable_editor']==1){
			if(check_rights('timetable_show_all_first')){
				$Redactor=-1;
			}else{
				$Redactor=$user->data['user_id'];
			}
		}else{
			$Redactor=-1;
		}
	}
	
	/*Формируем таблицу*/
	if(@$_GET['regime']=='report'){
		$timetable_html=create_report();
	}else{
		$timetable_html=create_timetable();
	}
	
	/*Проверяем чьими графиками работы мы можем управлять и можем ли вообще*/
	if(!$timetable_html){
		return "Вам не доступен ни один график работы для просмотра/редактирования";
	}else{
		//Определяем переменные
		$html="";
		
		/*Формируем список годов*/
		$years_html="";
		$selectedFOR="";
		$year_start=date("Y");
		$year_end=date("Y")-5;
		for($yearFOR=$year_start;$yearFOR>=$year_end;$yearFOR--){
			$yearFOR==$Year ? $selectedFOR='selected' : $selectedFOR='';
			$years_html.="<option value='$yearFOR' $selectedFOR>$yearFOR</option>";
		}

		/*Формируем список месяцев*/
		$months_html="";
		$selectedFOR="";
		foreach($MonthsFull as $keyFOR=>$monthFOR){
			$keyFOR==$Month ? $selectedFOR="selected" : $selectedFOR="";
			$months_html.="<option value='$keyFOR' $selectedFOR>$monthFOR</option>";
		}	
		
		/*Формируем список редакторов*/
		//IF
		if(check_rights('timetable_show_all')){
			//Определяем переменные
			$redactors_html="Подразделение (ответственный): ";
			
			//Запрос к базе
			$redactorsRES=db_query("SELECT * FROM `phpbb_users` WHERE `timetable_editor`=1 ORDER BY `hrmanager_alias` ASC");
			if(db_count($redactorsRES)==0){
				$redactors_html.="не заданы";
			}else{
				//Определяем переменную
				$redactors_html.="<select name='redactor'>";
				
				/*Не важно*/
				$Redactor===-1 ? $redactors_html.="<option value='-1' selected>--не важно--</option>" : $redactors_html.="<option value='-1'>--не важно--</option>";
				
				if(check_rights('timetable_bez_redactora')){
					/*Без редактора*/
					$Redactor===0 ? $redactors_html.="<option value='0' selected>--ответственный не указан--</option>" : $redactors_html.="<option value='0'>--ответственный не указан--</option>";
				}
				
				//WHILE
				while($redactorWHILE=db_fetch($redactorsRES)){
					//IF
					$redactorWHILE['hrmanager_alias']!="" ? $nameWHILE=$redactorWHILE['hrmanager_alias']." (".$redactorWHILE['username'].")" : $nameWHILE=$redactorWHILE['username'];
					$redactorWHILE['user_id']==$Redactor ? $selected='selected' :$selected='';
					$redactors_html.="<option value='{$redactorWHILE['user_id']}' $selected>{$nameWHILE}</option>";
				}
				
				//Определяем переменную
				$redactors_html.="</select>";
			}
		//ELSE
		}else{
			//Определяем переменную
			$redactors_html="";
		}
		
		
		/*НАЧАЛО: Определяем вид ссылок subgroup*/
		//Определяем переменную
		$temp_style="font-weight:bold;text-decoration:none;";
		
		//IF
		if(@$_GET['regime']=='report'){
			$subgroup_link_2=$temp_style;
			$subgroup_link_1='';
		}else{
			$subgroup_link_1=$temp_style;
			$subgroup_link_2='';
		}
		/*КОНЕЦ: Определяем вид ссылок subgroup*/

		/*НАЧАЛО: Определяем вид ссылок subgroup2*/
		//Определяем переменную
		$temp_style="font-weight:bold;text-decoration:none;";
		
		//IF
		if(@$_GET['report']=='year'){
			$subgroup2_link_2=$temp_style;
			$subgroup2_link_1='';
		}else{
			$subgroup2_link_1=$temp_style;
			$subgroup2_link_2='';
		}
		/*КОНЕЦ: Определяем вид ссылок subgroup2*/
		
		/*НАЧАЛО: Отображение subgroup2*/
		if(@$_GET['regime']=='report'){
			$subgroup2=template_get("timetable/subgroup2",
									array(		'year'=>$Year,
												'month'=>$Month,
												'redactor'=>$Redactor,
												'subgroup2_link_1'=>$subgroup2_link_1,
												'subgroup2_link_2'=>$subgroup2_link_2));
		}else{
			$subgroup2='';
		}
		/*КОНЕЦ: Отображение subgroup2*/
		
		//Проверка, даны ли вошедшему пользователю права на управления графиками кого-либо из редакторов{
			//Случай 1. Пользователь перечислен в таблице БД `phpbb_timetable_editors_rights`{
				$same_rightsSQL="SELECT `editor_id` FROM `phpbb_timetable_editors_rights` WHERE `user_id`={$user->data['user_id']}";
				if(db_easy_count($same_rightsSQL)>0){
					$editor_id=db_short_easy($same_rightsSQL);
					if(db_short_easy("SELECT `timetable_editor` FROM `phpbb_users` WHERE `user_id`={$editor_id}")==1){
						$timetable_editor_same_rights=1;
					}else{
						$timetable_editor_same_rights=0;
					}
					
				}else{
					$timetable_editor_same_rights=0;
				}
			//}
			//Случай 2. Пользователь является подчиненным шефа инженеров{
				if(is_engineer_chief_employee()){
					$timetable_editor_same_rights=1;
				}
			//}
			
		//}
		
		/*НАЧАЛО: Отображение ссылки на раздел "Таблица"*/
		if(check_rights('timetable_menu_tablica') || db_short_easy("SELECT `timetable_editor` FROM `phpbb_users` WHERE `user_id`={$user->data['user_id']}")==1 || $timetable_editor_same_rights){
			$tablica=template_get("timetable/tablica",
									array(		'year'=>$Year,
												'month'=>$Month,
												'redactor'=>$Redactor,
												'subgroup_link_1'=>$subgroup_link_1
										));
		}else{
			$tablica='';
		}
		
		/*КОНЕЦ: Отображение ссылки на раздел "Таблица"*/
		
		/*НАЧАЛО: Формируем additional_hiddens для <SELECT>*/
		//Определяем переменные
		$temp_array=array();
		$additional_hiddens="";
		
		//IF
		if(@$_GET['regime']=='report') $temp_array['regime']='report';
		if(@$_GET['report']=='year') $temp_array['report']='year';
		
		//FOREACH
		foreach($temp_array as $nameFOR=>$valueFOR){
			$additional_hiddens.="<input type='hidden' name='$nameFOR' value='$valueFOR' />";
		}
		/*КОНЕЦ: Формируем additional_hiddens для <SELECT>*/
		
		/*Неактивный <SELECT name='month'...*/
		@$_GET['report']=='year' ? $select_disabled='disabled' : $select_disabled='';
		
		/*Подключаем файл шаблона*/
		$html.=template_get("timetable/show_timetable",
									array(		'year'=>$Year,
												'month'=>$Month,
												'redactor'=>$Redactor,
												'years'=>$years_html,
												'months'=>$months_html,
												'redactors'=>$redactors_html,
												'table'=>$timetable_html,
												'subgroup_link_2'=>$subgroup_link_2,
												'subgroup2'=>$subgroup2,
												'additional_hiddens'=>$additional_hiddens,
												'select_disabled'=>$select_disabled,
												'tablica'=>$tablica
									));
													
		//Возвращаем значение функции
		return $html;
	}
}

//------Блок вспомогательных Функций------//

/*Формируем таблицу*/
function create_timetable(){
	//Получаем глобальные переменные
	global $Month;
	global $Year;
	global $Redactor;
	global $user; /*Переменная phpbb*/
	global $MonthsShort;
	
	//Определяем переменные
	$html="";
	
	//Определяем переменную
	$users=array();

	/*Вычисляем количество дней в месяце*/
	$day_number=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
	
	/*Получаем список пользователей, графиком работы которых мы можем управлять*/
	//IF
	if(check_rights('timetable_show_all')){
		//IF
		if($Redactor==0){
			$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `username`!='root' AND `my_timetable_editor_id`=0 ORDER BY `username` ASC");	
		//ELSEIF
		}elseif($Redactor==-1){
			$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `username`!='root' ORDER BY `username` ASC");	
		//ELSE
		}else{
			$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `username`!='root' AND `my_timetable_editor_id`=$Redactor ORDER BY `username` ASC");	
		}
	//ELSE
	}else{
		if(db_short_easy("SELECT `timetable_editor` FROM `phpbb_users` WHERE `user_id`={$user->data['user_id']}")==1){
			$same_editor_id=$user->data['user_id'];
		}elseif(is_engineer_chief_employee()){
			$same_editor_id=$user->data['mychief_id'];
		}else{
			$same_editorSQL="SELECT `editor_id` FROM `phpbb_timetable_editors_rights` WHERE `user_id`={$user->data['user_id']}";
			if(db_easy_count($same_editorSQL)>0){
				$same_editor_id=db_short_easy($same_editorSQL);
			}
		}
		
		//Исключение в сортировке для пользователя Нечаев Андрей по его просьбе
		if($user->data['user_id']==46){
			$special_order='timetable_order';
		}else{
			$special_order='username';
		}
		
		//Запрос к базе
		$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `my_timetable_editor_id`=$same_editor_id ORDER BY `$special_order` ASC");
	}
	
	/*Строим шапку таблицы*/
	$html.="<tr class='vfirst'><td rowspan='2' class='gfirst vfirst'>Ф.И.</td><td rowspan='2' colspan='2' class='gnolast vfirst'>Устроен</td><td class='vfirst glast' colspan='$day_number'>Дата</td></tr>";
	$html.="<tr class='vfirst'>";
	for($dayFOR=1;$dayFOR<=$day_number;$dayFOR++){
		//IF
		$dayFOR==$day_number ? $tdclass='glast' : $tdclass='gnolast';
		
		//Определяем переменную
		$html.="<td class='$tdclass'>$dayFOR</td>";
	}
	$html.="</tr>";
	
	/*НАЧАЛО: Создаем массив ускоряющий работу (чтобы не делать запрос sql на каждое число*/
	//Запрос к базе
	$daysRES=db_query("SELECT * FROM `phpbb_timetable` WHERE `year`=$Year AND `month`=$Month");
	
	//Определяем переменную
	$timetable=array();
	
	//WHILE
	while($day=db_fetch($daysRES)){
		$timetable[$day['user_id']][$day['day']]['status']=$day['status'];
		$timetable[$day['user_id']][$day['day']]['hours']=$day['hours'];
	}
	/*КОНЕЦ: Создаем массив ускоряющий работу (чтобы не делать запрос sql на каждое число*/
	
	/*НАЧАЛО: Строим тело таблицы*/
	//Определяем переменную
	$line=1;
	$engineer=0;
	$spec_prod_staff=0;
	
	//Определяем, является ли редактор шефом инженеров
	if($Redactor!=-1 && $Redactor!=0){
		$redactor_engineers_chief=db_short_easy("SELECT `engineer_chief` FROM `phpbb_users` WHERE `user_id`=".$Redactor);
	}else{
		$redactor_engineers_chief=0;
	}

	//IF
	if(db_count($usersRES)>0){
		//WHILE
		while($userWHILE=db_fetch($usersRES)){
			/*Пропускаем тех, у кого notimetable=1*/
			if($userWHILE['notimetable']==1) continue;

			//Получаем "шефа" правильным образом
			if($userWHILE['mychief_id']!=0){
				$chiefQUERY=db_query("SELECT * FROM `phpbb_users` WHERE `user_id`=".$userWHILE['mychief_id']);
				if(db_count($chiefQUERY)>0){
					$chief=db_fetch($chiefQUERY);
				}else{
					$chief=false;
				}
			}else{
				$chief=false;
			}
			
			//Флаг инженера
			$engineer=$userWHILE['engineer'];
			
			//Флаг специального сотрудника производства
			$spec_prod_staff=$userWHILE['spec_prod_staff'];
			
			//Проверяем, имеет ли сотрудник при
		
			//IF
			if($redactor_engineers_chief!=1){
				$line==db_count($usersRES) ? $trclass='vlast' : $trclass='vnolast';
			}else{
				$trclass='vnolast';
			}
			
			//Определяем переменную
			$line++;
			
			//Определяем переменную
			$html.="<tr class='$trclass'>";
			
			//Определяем переменную
			$html.="<td class='gfirst'><a href='/manager.php?action=show_contact&contact={$userWHILE['user_id']}'>{$userWHILE['username']}</a></td>";
			$userWHILE['hire']!="0000-00-00" ? $hire_monthWHILE=$MonthsShort[(int)date("m", strtotime($userWHILE['hire']))] : $hire_monthWHILE="";
			$userWHILE['hire']!="0000-00-00" ? $hire_yearWHILE=date("Y", strtotime($userWHILE['hire'])) : $hire_yearWHILE="";
			$html.="<td class='gnolast'>".$hire_monthWHILE."</td>";
			$html.="<td class='gnolast'>".$hire_yearWHILE."</td>";
			
			//FOR
			for($dayFOR=1;$dayFOR<=$day_number;$dayFOR++){
				//IF
				$dayFOR==$day_number ? $tdclass='glast' : $tdclass='gnolast';
				
				//IF
				isset($timetable[$userWHILE['user_id']][$dayFOR]['status']) ? $status=$timetable[$userWHILE['user_id']][$dayFOR]['status'] : $status=0;
				
				//IF
				if($timetable[$userWHILE['user_id']][$dayFOR]['hours']>=1 && $timetable[$userWHILE['user_id']][$dayFOR]['hours']<=7){
					$addtext='('.$timetable[$userWHILE['user_id']][$dayFOR]['hours'].')';
				}else{
					$addtext='';
				}
				
				//SWITCH
				switch ($status){
					case 0:
						$status_html='';
						$color='#fff';
						break;
					case 1:
						$status_html='';
						$color='#fff';
						break;
					case 2:
						$status_html='от'.$addtext;
						$color='#ffe599';
						break;
					case 3:
						$status_html='Б'.$addtext;
						$color='#b6d7a8';
						break;
					case 4:
						$status_html='до'.$addtext;
						$color='#E2B1E2';
						break;
					case 5:
						$status_html='к'.$addtext;
						$color='#9fc5e8';
						break;
					case 6:
						$status_html="";
						$color="#FFF;";
						break;
					case 51:
						$status_html='/-'.$addtext;
						$color='#fff';
						break;
					case 52:
						$status_html='-/'.$addtext;
						$color='#fff';
						break;
					case 53:
						$status_html='-'.$addtext;
						$color='#fff';
						break;						
					case 54:
						$status_html='1'.$addtext;
						$color='#fff';
						break;						
					case 55:
						$status_html='0.5'.$addtext;
						$color='#fff';
						break;						
					case 56:
						$status_html="<span style='font-size:7pt;'>0.5+<span style='color:red;'>0.5</span></span>".$addtext;
						$color='#fff';
						break;
					case 57:
						$status_html="<span style='color:red;'>1</span>".$addtext;
						break;
					case 58:
						$status_html="<span style='color:red;'>0.5</span>".$addtext;
						break;
					case 9:
						$status_html='зф'.$addtext;
						$color='#CF596E';
						break;
				}
				
				$day_of_week=date("N", strtotime("$Year-$Month-$dayFOR"));
				
				if((($day_of_week==6 || $day_of_week==7) && $status==0) || $status==6){
					if($status_html==''){
						$backgroundimage="url(/images/krestik.png)";
					}else{
						$backgroundimage="";
					}
				}else{
					$backgroundimage="";
				}
				
				$onDoubleClick="popup_menu(this.id);";
				
				//Определяем переменную
				$html.="<td id='{$userWHILE['user_id']}-$Year-$Month-$dayFOR-$engineer-$spec_prod_staff' class='$tdclass' onDblClick=\"$onDoubleClick\"  unselectable='on' onselectstart='return false;' style='background:$color;background-repeat:no-repeat;background-image:$backgroundimage;'>$status_html</td>";
			}
			
			//Определяем переменную
			$html.="</tr>";
		}
		
		//Добавляем комментарии к столбцам для инженеров
		if($redactor_engineers_chief==1 || is_engineer_chief_employee()){
			$comments_number=1;
			for($comment_number=1;$comment_number<=$comments_number;$comment_number++){
				//$comment_number==$comments_number ? $tr_class='vlast' : $tr_class='vnolast';
				$html.="<tr>";
				$html.="<td></td><td></td><td class='comment2'></td>";
				for($dayFOR=1;$dayFOR<=$day_number;$dayFOR++){
					$commentFOR=db_easy("SELECT * FROM `phpbb_timetable_comments` WHERE `year`=$Year AND `month`=$Month AND `day`=$dayFOR");
					if($commentFOR['comment1']!='' || $commentFOR['comment2']!='' || $commentFOR['comment3']!=''){
						$styleFOR="background:url('/images/cross.png') center center no-repeat";
					}else{
						$styleFOR="";
					}
					$dayFOR==$day_number ? $td_g_class='glast' : $td_g_class='gnolast';
					$html.="<td id='comment-$comment_number-$Year-$Month-$dayFOR' class='comment1' style=\"$styleFOR\" onDblClick=\"if(popup_id_check!=this.id) {popup_comment(this.id);}\" align='center'></td>";
				}
				$html.="</tr>";
			}
		}
	}
	/*КОНЕЦ: Строим тело таблицы*/
	
	//Возвращаем значение функции
	return $html;
}

/*Формируем отчет*/
function create_report(){
	//Получаем глобальные переменные
	global $Month;
	global $Year;
	global $Redactor;
	global $user; /*Переменная phpbb*/
	
	//Определяем переменные
	$html="";
	
	//Определяем переменную
	$users=array();

	/*Получаем список пользователей, графиком работы которых мы можем управлять*/
	//IF
	if(check_rights('timetable_show_all')){
		//IF
		if($Redactor==0){
			$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `username`!='root' AND `my_timetable_editor_id`=0 ORDER BY `username` ASC");	
		//ELSEIF
		}elseif($Redactor==-1){
			$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `username`!='root' ORDER BY `username` ASC");	
		//ELSE
		}else{
			$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `username`!='root' AND `my_timetable_editor_id`=$Redactor ORDER BY `username` ASC");	
		}
	//ELSE
	}else{
		if(db_short_easy("SELECT `timetable_editor` FROM `phpbb_users` WHERE `user_id`={$user->data['user_id']}")==1){
			$same_editor_id=$user->data['user_id'];
		}else{
			$same_editorSQL="SELECT `editor_id` FROM `phpbb_timetable_editors_rights` WHERE `user_id`={$user->data['user_id']}";
			if(db_easy_count($same_editorSQL)>0){
				$same_editor_id=db_short_easy($same_editorSQL);
			}
		}
		
		//Исключение в сортировке для пользователя Нечаев Андрей по его просьбе
		if($user->data['user_id']==46){
			$special_order='timetable_order';
		}else{
			$special_order='username';
		}
		
		//Запрос к базе
		$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN (0,3,9) AND `my_timetable_editor_id`=$same_editor_id ORDER BY `$special_order` ASC");
	}
	
	/*Строим шапку таблицы*/
	$html.="<tr class='vfirst'><td class='gfirst vfirst'>Ф.И.</td>
				<td class='gnolast vfirst' style='width:100px;background:#ffe599;'>Отпуска</td>
				<td class='gnolast vfirst' style='width:100px;background:#b6d7a8;'>Больничные</td>
				<td class='gnolast vfirst' style='width:100px;background:#E2B1E2;'>За свой счет</td>
				<td class='glast vfirst' style='width:100px;background:#9fc5e8;'>Командировки</td>
			</tr>";
	
	/*НАЧАЛО: Создаем массив ускоряющий работу (чтобы не делать запрос sql на каждое число*/
	if(@$_GET['report']=='year'){
		//Запрос к базе
		$daysRES=db_query("SELECT * FROM `phpbb_timetable` WHERE `year`=$Year");
		
		//Определяем переменную
		$timetable=array();
		
		//WHILE
		while($day=db_fetch($daysRES)){
			$timetable[$day['user_id']][$day['month']][$day['day']]['status']=$day['status'];
			$timetable[$day['user_id']][$day['month']][$day['day']]['hours']=$day['hours'];
		}
	}else{
		//Запрос к базе
		$daysRES=db_query("SELECT * FROM `phpbb_timetable` WHERE `year`=$Year AND `month`=$Month");
		
		//Определяем переменную
		$timetable=array();
		
		//WHILE
		while($day=db_fetch($daysRES)){
			$timetable[$day['user_id']][$day['day']]['status']=$day['status'];
			$timetable[$day['user_id']][$day['day']]['hours']=$day['hours'];
		}
	}
	/*КОНЕЦ: Создаем массив ускоряющий работу (чтобы не делать запрос sql на каждое число*/
	
	/*НАЧАЛО: Строим тело таблицы*/
	
	//IF
	if(db_count($usersRES)>0){
		//Определяем переменные
		$total=array();
		
		//Определяем переменную
		$line=1;		
		
		//WHILE
		while($userWHILE=db_fetch($usersRES)){
			/*Пропускаем тех, у кого notimetable=1*/
			if($userWHILE['notimetable']==1) continue;
			
			//Определяем переменные
			$total=array();
			$total_str=array();

			//Определяем переменные
			for($status=2;$status<=5;$status++){
				$total[$status]=0;
			}

			//IF
			if(@$_GET['report']=='year'){
				for($monthFOR=1;$monthFOR<=12;$monthFOR++){
					$day_numberFOR=cal_days_in_month(CAL_GREGORIAN, $monthFOR, $Year);
					for($dayFOR=1;$dayFOR<=$day_numberFOR;$dayFOR++){
						//IF
						if(isset($timetable[$userWHILE['user_id']][$monthFOR][$dayFOR]['status'])){
							//Определяем переменную
							$status=$timetable[$userWHILE['user_id']][$monthFOR][$dayFOR]['status'];
							
							//Определяем переменную
							$total[$status]+=$timetable[$userWHILE['user_id']][$monthFOR][$dayFOR]['hours'];			
						}
					}
				}
			//ELSE
			}else{
				/*Вычисляем количество дней в месяце*/
				$day_number=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
				
				//FOR
				for($dayFOR=1;$dayFOR<=$day_number;$dayFOR++){
					//IF
					if(isset($timetable[$userWHILE['user_id']][$dayFOR]['status'])){
						//Определяем переменную
						$status=$timetable[$userWHILE['user_id']][$dayFOR]['status'];
						
						//Определяем переменную
						$total[$status]+=$timetable[$userWHILE['user_id']][$dayFOR]['hours'];					
					}
				}
			}
			
			
			
			
			
			//Определяем переменную
			for($status=2;$status<=5;$status++){
				if($total[$status]>0){
					$total_str[$status]=get_time_str($total[$status]);
				}else{
					$total_str[$status]="";
				}
			}
			
			//IF
			$line==db_count($usersRES) ? $trclass='vlast' : $trclass='vnolast';
			
			//Определяем переменную
			$line++;
			
			//Определяем переменную
			$html.="<tr class='$trclass'>";
			
			//Определяем переменную
			$html.="<td class='gfirst'><a href='/manager.php?action=show_contact&contact={$userWHILE['user_id']}'>{$userWHILE['username']}</a></td>";
			
			//Определяем переменную
			$html.="<td class='gnolast'>{$total_str[2]}</td><td class='gnolast'>{$total_str[3]}</td><td class='gnolast'>{$total_str[4]}</td><td class='glast'>{$total_str[5]}</td>";
			
			//Определяем переменную
			$html.="</tr>";			
			
		}	
	}else{
	}
	/*КОНЕЦ: Строим тело таблицы*/
	
	//Возвращаем значение функции
	return $html;
}
?>