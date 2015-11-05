<?php
function delete_hr_manager_right(){
	/*Проверка прав на выполнение действия*/
	if(!check_rights('delete_hr_manager_right')){
		return "У вас нет соответствующих прав";
	}
	
	/*Проверка входных данных*/
	if(!isset($_GET['user'])){
		return "Ошибка входных данных (1)";
	}
	
	/*Проверка входных данных*/
	if(!preg_match("/^[0-9]{1,8}$/", $_GET['user'])){
		return "Ошибка в формате входных данных (2)";
	}
		
	//Определяем переменную
	$user_id=(int)$_GET['user'];
		
	/*Проверка входных данных*/
	if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id AND `hrmanager`=1")==0){
		return "Ошибка в формате входных данных (3)";
	}
		
	//Запрос к базе
	db_query("UPDATE `phpbb_users` SET `hrmanager`=0 WHERE `user_id`=$user_id");
	
	/*Проверка правильности выполнения запроса к БД*/
	if(!db_result()){
		return "Ошибка при выполнении (4)";
	}
	
	//Выполняем HTTP запрос
	header("location: /manager.php?action=show_rights");
}
?>