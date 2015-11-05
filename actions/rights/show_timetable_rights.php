<?php
function show_timetable_rights(){
	//Определяем переменные
	$html="";
	$rights_html="";
	$hrmanagers_html="";
	
	/*Выводим список менеджеров HR*/
	$hrmanagers_html.=show_hr_managers();
	
	//Запрос к базе
	$editorsRES=db_query("SELECT * FROM `phpbb_users` WHERE `timetable_editor`=1 ORDER BY `username` ASC");
	
	//IF
	if(db_count($editorsRES)>0){
		//WHILE
		while($editor=db_fetch($editorsRES)){
			//НАЧАЛО: Строим список, у кого есть аналочичные права
			$same_rights_usersRES=db_query("SELECT * FROM `phpbb_timetable_editors_rights`, `phpbb_users`
												WHERE `phpbb_users`.`user_id`=`phpbb_timetable_editors_rights`.`user_id`
													AND `phpbb_timetable_editors_rights`.`editor_id`={$editor['user_id']}");
			if(db_count($same_rights_usersRES)>0){
				$same_rights_html="";
				$same_right_suffix="AND `user_id` NOT IN(";
				while($same_rights_user=db_fetch($same_rights_usersRES)){
				$same_rights_html.="{$same_rights_user['username']}
							<a href='/manager.php?action=del_same_editor_rights&editor={$editor['user_id']}&user={$same_rights_user['user_id']}'>
								<img src='/images/delete.png' />
									</a>&nbsp;&nbsp;";
					$same_right_suffix.="{$same_rights_user['user_id']},";
				}
				$same_right_suffix=substr($same_right_suffix, 0, strlen($same_right_suffix)-1).")";
			}else{
				$same_rights_html="отсутствуют";
				$same_right_suffix="";
			}
			//КОНЕЦ: Строим список, у кого есть аналочичные права

			//НАЧАЛО: Строим список пользователей для "аналогичных прав"
			$same_rights_select="<form id='add_same_editor_rights{$editor['user_id']}' action='/manager.php?action=add_timetable_editor_same_rights' method='get' class='same_right'>
									<input type='hidden' name='action' value='add_same_editor_rights' />
									<input type='hidden' name='editor' value='{$editor['user_id']}' />
									<select class='same_rights' name='user'>";
			$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `user_type` IN(0,3) AND `user_id`!={$editor['user_id']} $same_right_suffix AND `username`!='root' ORDER BY `username` ASC");
			
			$num_same_rights_users=0;
			
			if(db_count($usersRES)>0){
				while($userWHILE=db_fetch($usersRES)){
					if(db_easy_count("SELECT * FROM `phpbb_timetable_editors_rights` WHERE `user_id`={$userWHILE['user_id']}")==0){
						$num_same_rights_users++;
						$same_rights_select.="<option value='{$userWHILE['user_id']}'>{$userWHILE['username']}</option>";
					}
				}
			}
			
			if($num_same_rights_users==0) $same_rights_select.="<option value=''>нет доступных пользователей</option>";
			
			$same_rights_select.="</select></form>";
			//КОНЕЦ: Строим список пользователей для "аналогичных прав"

			//Определяем переменную
			$rights_html.="<b class='smallheader'>Редактор: ".$editor['username']."</b>
								<div class='undersmallheader'>Аналогичные права: $same_rights_html</div>
								<div class='undersmallheader'>Добавить для: $same_rights_select <a href='#' onClick=\"document.getElementById('add_same_editor_rights{$editor['user_id']}').submit();\"><img src='/images/add.png' /></a></div>
								<ul class='show_timetable_rights'>";
			
			//Запрос к базе
			$edited_usersRES=db_query("SELECT * FROM `phpbb_users` WHERE `my_timetable_editor_id`={$editor['user_id']} ORDER BY `username` ASC");
			
			//IF
			if(db_count($edited_usersRES)>0){
				//WHILE
				while($edited_user=db_fetch($edited_usersRES)){
					$rights_html.="<li>".$edited_user['username']."</li>";
				}
			}else{
				$rights_html.="<li>Не указаны пользователи для управления</li>";
			}
			
			$rights_html.="</ul>";
		}
	}else{
		$rights_html="Ни один пользователь не имеет прав на управление графиками работ.";
	}

	/*Подключаем файл шаблона*/
	$html.=template_get("rights/show_timetable_rights",
								array(	'hrmanagers'=>$hrmanagers_html,
										'rights'=>$rights_html
								));

	
	//Возвращаем значение функции
	return $html;
}

/*Выводим список менеджеров HR*/
function show_hr_managers(){
	//Определяем переменные
	$html="";
	
	//Запрос к базе
	$hrmanagersRES=db_query("SELECT * FROM `phpbb_users` WHERE `hrmanager`=1 ORDER BY `username` ASC");
	
	//IF
	if(db_count($hrmanagersRES)>0){
		//WHILE
		while($hrmanager=db_fetch($hrmanagersRES)){
			$html.=$hrmanager['username'].
					"<a href='/manager.php?action=delete_hr_manager_right&user={$hrmanager['user_id']}'><img src='/images/delete.png' style='padding-left:20px;' /></a><br/>";
		}
	//ELSE	
	}else{
		$html.="Ни один пользователь не имеет прав HR-менеджера";
	}
	
	//Возвращаем значение функции
	return $html;
}
?>