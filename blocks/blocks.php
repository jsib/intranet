<?php
//"Вставка" в главное меню
function main_menu(){
	//Получаем глобальные переменные
	global $template;
	global $user;
	
	//IF
	if(check_rights('show_admin_panel')){
		$template->assign_var('LINKADMINSECTION', "<li class='div'>|</li><li><a href='/manager.php?action=show_admin_panel'>Панель администратора</a></li>");
	}
	
	/*//Проверка, является ли вошедший пользователь редактором графиков работ
	db_short_easy("SELECT `timetable_editor` FROM `phpbb_users` WHERE `user_id`={$user->data['user_id']}")==1 ? $timetable_editor=1 : $timetable_editor=0;
	
	//НАЧАЛО: Проверка, даны ли вошедшему пользователю права на управления графиками кого-либо из редакторов
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
	
	if(is_engineer_chief_employee()){
		$timetable_editor_same_rights=1;
	}
	
	//КОНЕЦ: Проверка, даны ли вошедшему пользователю права на управления графиками кого-либо из редакторов
	
	//IF
	if(check_rights('show_timetable') || $timetable_editor || $timetable_editor_same_rights){
		if(check_rights('timetable_menu_tablica') || $timetable_editor || $timetable_editor_same_rights){
			$url_postfix='action=show_timetable';
		}else{
			$url_postfix='action=show_timetable&regime=report';
		}
		$template->assign_var('LINKTIMETABLESECTION', "<li class='div'>|</li><li><a href='/manager.php?$url_postfix'>Графики работы</a>");
	}
	*/
	//Назначаем замены для шаблона
	$template->assign_var('MYPFOFILE', "/manager.php?action=show_contact&contact={$user->data['user_id']}");		
}
?>