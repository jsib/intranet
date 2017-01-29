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
	
	//Get user information
	$user=db_easy('SELECT * FROM `phpbb_users` WHERE `user_id`='.$user_id);

	/*Проверяем входной user_id*/
	if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id")==0){
		return "Ошибка входных данных (user_id).";
	}

	/*Запрещаем редактировать предыдущие месяцы*/
	if(!check_rights('edit_previous_month_timetables')){
		if($month!=date('n')){
			return "Ошибка! Редактирование предыдущих и будущих месяцев запрещено.";
		}
	}
	//Проверяем количество использованных дней отпуска в текущем году для отдельного пользователя
	foreach(array(2=>'отпуска', 3=>'больничного') as $status_for=>$name_rp_for){
		if($status==$status_for){
			$vacation_rest=check_for_available_benefits($year, $month, $day, $status, $hours, $user);
			
			if($vacation_rest!==true){
				return 'Ошибка! Недостаточно дней/часов для '.$name_rp_for.', у вас осталось '.$vacation_rest;
			}
		}
	}
	
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

function check_for_available_benefits($year, $month, $day, $status, $this_cell_hours_new, $user){
	//Fetch from database current number of hours
	$this_cell_status_res=db_query('SELECT `hours`
	                                FROM `phpbb_timetable`
									WHERE `year`='.$year.'
									      AND `month`='.$month.'
										  AND `day`='.$day.'
										  AND `user_id`='.$user['user_id'].'
										  AND `status`='.$status
							      );
	
	//Put fetched value to variable	
	if(db_count($this_cell_status_res)>0){
		$this_cell_hours_current=db_fetch($this_cell_status_res)['hours'];
	}else{ 
		$this_cell_hours_current=0;
	}
	
	//Get benefits information
	$attendance_benefit = new AttendanceBenefits($user, $year, $status);
	$available_benefits=$attendance_benefit->get_available_benefits();
	
	//Return information about available benefits if not enough benefits
	if($available_benefits < $this_cell_hours_new - $this_cell_hours_current){
		return to_days_and_hours($available_benefits);
	//Return 'true' if we have enough
	}else{
		return true;
	}
}
?>