<?php
function set_td_remote(){
	/*Получаем данные от пользователя*/
	if(isset($_GET['td'])){
		if(!preg_match("/^[0-9]{1,8}\-[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}\-[01]{1}-[01]{1}$/", $_GET['td'])){
			return "Ошибка в формате входных данных (td).";
		}else{
			$td=$_GET['td'];
		}
	}else{
		return "Не определены входные данные (td)";
	}
	
	/*Получаем данные от пользователя*/
	if(isset($_GET['status'])){
		if(!preg_match("/^[0-9]{1,3}$/", $_GET['status'])){
			return "Ошибка в формате входных данных (status).";
		}else{
			$status=(int)$_GET['status'];
		}
	}else{
		return "Не определены входные данные (status)";
	}
	
	/*Получаем данные от пользователя*/
	$hours=(int)$_GET['hours'];
	
	/*Обрабатываем полученные данные*/
	$temp=explode('-', $td);
	$user_id=(int)$temp[0];
	$year=(int)$temp[1];
	$month=(int)$temp[2];
	$day=(int)$temp[3];

	/*Проверяем входной user_id*/
	if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id")==0){
		return "Ошибка входных данных (user_id).";
	}

	/*Запрещаем редактировать предыдущие месяцы*/ 
	if($month!=date('n')){
		return "Ошибка! Редактирование предыдущих месяцев запрещено.";
	}
	
	
	/*Проверяем количество использованных дней отпуска в этом году для этого пользователя*/
	$otpusk_kolvo=db_easy_count("SELECT * FROM `phpbb_timetable` WHERE `year`=$year AND `user_id`=$user_id AND `status`=$status");
	if($otpusk_kolvo>=20){
		return "Ошибка! Максимальное количество дней отпуска (20) уже достигнуто.";
	}

	/*Проверяем количество использованных дней больничного в этом году для этого пользователя*/
	$bolnichniy_kolvo=db_easy_count("SELECT * FROM `phpbb_timetable` WHERE `year`=$year AND `user_id`=$user_id AND `status`=$status");
	if($bolnichniy_kolvo>=5){
		return "Ошибка! Максимальное количество дней больничного (5) уже достигнуто.";
	}
			
	//exit;
	//Запрос к базе
	if(db_easy_count("SELECT * FROM `phpbb_timetable` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id")==0){
		return db_result(db_query("INSERT INTO `phpbb_timetable` SET `year`=$year, `month`=$month, `day`=$day, `user_id`=$user_id, `status`=$status, `hours`=$hours"));
	}else{
		//IF
		/*status=1 то же самое, что запись об этой ячейке отсутствует в БД*/
		//if($status==1){
			//return db_easy_result("DELETE FROM `phpbb_timetable` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id");
		//ELSE	
		//}else{
			/*Если точно такая же запись уже существует*/
			if(db_easy_count("SELECT * FROM `phpbb_timetable` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id AND `status`=$status AND `hours`=$hours")==1){
				return 1;
			/*иначе идет обновление записи в БД*/
			}else{
				return db_easy_result("UPDATE `phpbb_timetable` SET `status`=$status, `hours`=$hours WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id");
			}
		//}
	}
}
?>