<?php
function delete_point(){
	if(!check_rights('delete_point')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}

	/*Получаем данные от пользователя*/
	$point_id=$_GET['point'];
	
	//Запрос к базе
	$point=db_easy("SELECT * FROM `phpbb_points` WHERE `id`=$point_id");
	
	//Запрос к базе
	db_query("DELETE FROM `phpbb_points` WHERE `id`=$point_id");
	
	//Отправляем HTTP заголовок
	header("location: /manager.php?action=list_points&message=pointdeleted&name={$point['name']}");
	
	//Возвращаем значение функции
	return $html;
}
?>