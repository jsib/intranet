<?php
function delete_right(){
	/*Проверка прав на выполнение действия*/
	if(!check_rights('delete_right')){
		return "У вас нет соответствующих прав";
	}
	
	//Определяем переменную
	$user_id=(int)$_GET['user'];

	/*Получаем и проверяем данные от пользвователя*/
	$right_id=(int)$_GET['right'];
	
	/*Проверка входных данных*/
	if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id")==0){
		return "Ошибка входных данных (user)";
	}
	
	/*Проверка входных данных*/
	if(db_easy_count("SELECT * FROM `phpbb_rights` WHERE `id`='$right_id'")==0){
		return "Ошибка в формате входных данных (right)";
	}
	
	//Запрос к базе
	$delRES=db_query("DELETE FROM `phpbb_rights_users` WHERE `user_id`=$user_id AND `right_id`=$right_id");
	
	/*Проверка правильности выполнения запроса к БД*/
	if(!db_result($delRES)){
		return "Ошибка при выполнении (delete)";
	}
	
	//Выполняем HTTP запрос
	header("location: /manager.php?action=show_rights");
}
?>