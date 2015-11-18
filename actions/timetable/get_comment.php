<?php
function get_comment(){
	/*Получаем данные от пользователя*/
	if(isset($_POST['id'])){
		if(!preg_match("/^comment\-[0-9]{1,2}\-[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $_POST['id'])){
			return "Ошибка в формате входных данных (td).";
		}else{
			$id=$_POST['id'];
		}
	}else{
		return "Не определены входные данные (id)";
	}
	
	/*Обрабатываем полученные данные*/
	$temp=explode('-', $id);
	$year=(int)$temp[2];
	$month=(int)$temp[3];
	$day=(int)$temp[4];
	
	//Запрос к базе
	$q=db_query("SELECT * FROM `phpbb_timetable_comments` WHERE `year`=$year AND `month`=$month AND `day`=$day");
	if(db_count($q)==0){
		return 1;
	}else{
		$result=db_fetch($q);
		return $result['comment1']."\n".$result['comment2']."\n".$result['comment3'];
	}
}
?>