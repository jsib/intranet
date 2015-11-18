<?php
function save_comment(){
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
	
	/*Получаем данные от пользователя*/
	if(isset($_POST['comment1'])){
		$comment1=$_POST['comment1'];
	}else{
		return "Не определены входные данные (comment1)";
	}
	if(isset($_POST['comment2'])){
		$comment2=$_POST['comment2'];
	}else{
		return "Не определены входные данные (comment2)";
	}
	if(isset($_POST['comment3'])){
		$comment3=$_POST['comment3'];
	}else{
		return "Не определены входные данные (comment3)";
	}
	
	/*Обрабатываем полученные данные*/
	$temp=explode('-', $id);
	$year=(int)$temp[2];
	$month=(int)$temp[3];
	$day=(int)$temp[4];
	
	//Делаем комментарии безопасными для БД
	$comment1_esc=db_escape($comment1);
	$comment2_esc=db_escape($comment2);
	$comment3_esc=db_escape($comment3);

	//Запрос к базе
	if(db_easy_count("SELECT * FROM `phpbb_timetable_comments` WHERE `year`=$year AND `month`=$month AND `day`=$day")==0){
		return db_result(db_query("INSERT INTO `phpbb_timetable_comments` SET `year`=$year, `month`=$month, `day`=$day, `comment1`='$comment1_esc', `comment2`='$comment2_esc', `comment3`='$comment3_esc'"));
	}else{
		/*Если точно такая же запись уже существует*/
		if(db_easy_count("SELECT * FROM `phpbb_timetable_comments` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `comment1`='$comment1_esc' AND `comment2`='$comment2_esc' AND `comment3`='$comment3_esc'")==1){
			return 1;
		/*иначе идет обновление записи в БД*/
		}else{
			return db_easy_result("UPDATE `phpbb_timetable_comments` SET `comment1`='$comment1_esc', `comment2`='$comment2_esc', `comment3`='$comment3_esc' WHERE `year`=$year AND `month`=$month AND `day`=$day");
		}
	}
}
?>