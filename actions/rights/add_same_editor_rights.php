<?php
function add_same_editor_rights(){
	/*Проверка прав на выполнение действия*/
	if(!check_rights('add_same_editor_rights')){
		return "У вас нет соответствующих прав";
	}

	/*Получаем и проверяем данные от пользователя*/
	$editor_id=(int)$_GET['editor'];
	
	/*Получаем и проверяем данные от пользователя*/
	$user_id=(int)$_GET['user'];

	/*Проверка входных данных*/
	if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$editor_id AND `timetable_editor`=1")==0){
		return "Ошибка в формате входных данных (editor)";
	}
	
	/*Проверка входных данных*/
	if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id")==0){
		return "Ошибка в формате входных данных (user)";
	}

	//Запрос к базе
	if(db_easy_count("SELECT * FROM `phpbb_timetable_editors_rights` WHERE `user_id`=$user_id AND `editor_id`=$editor_id")==0){
		$insertRES=db_query("INSERT INTO `phpbb_timetable_editors_rights` SET `user_id`=$user_id, `editor_id`=$editor_id");
	}
	
	/*Проверка правильности выполнения запроса к БД*/
	if(!db_result($insertRES)){
		return "Ошибка при выполнении (insert)";
	}
	
	//Выполняем HTTP запрос
	header("location: /manager.php?action=show_timetable_rights");
}
?>