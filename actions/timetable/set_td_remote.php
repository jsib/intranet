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
	
	
	//*Проверяем количество использованных дней больничного и отпуска в текущем году для отдельного пользователя{
	$check_array = array(0=>array(
							'name_rus_rodit_padezh'=>'больничного',
							'hours_per_day'=>8,
							'max_days'=>5,
							'code'=>3),
						1=>array(
							'name_rus_rodit_padezh'=>'отпуска',
							'hours_per_day'=>8,
							'max_days'=>20,
							'code'=>2)						
						);
	foreach($check_array as $key=>$check){
		
		$status_rodit_pad=$check['name_rus_rodit_padezh'];
		$status_hours_per_day=$check['hours_per_day'];
		$status_max_days=$check['max_days'];
		$status_code=$check['code'];

		//Проверяем, заполнена ли уже данная ячейка и вычисляем количество часов в ней, если да{
		$status_this_res=db_query("SELECT `hours` FROM `phpbb_timetable` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id AND `status`={$status_code}");
		if(db_count($status_this_res)>0){
			$status_this_hours=db_fetch($status_this_res)['hours'];
		}else{ 
			$status_this_hours=0;
		}
		//}
		$status_res=db_query("SELECT * FROM `phpbb_timetable` WHERE `year`=$year AND `user_id`=$user_id AND `status`={$status_code}");
		$status_sum=0;
		while($statusWHILE=db_fetch($status_res)){
			$status_sum+=$statusWHILE['hours']; 
		}
		
		if(($status_sum+$hours-$status_this_hours)>$status_max_days*$status_hours_per_day && $status==$status_code){
			$status_rest_hours_total=$status_max_days*$status_hours_per_day-$status_sum;
			$status_rest_hours=$status_rest_hours_total%$status_hours_per_day;
			$status_rest_days=($status_rest_hours_total-$status_rest_hours)/$status_hours_per_day;
		return "Ошибка! У вас осталось {$status_rest_days}д {$status_rest_hours}ч $status_rodit_pad."; 
		}
	}
	//}

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