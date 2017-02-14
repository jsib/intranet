<?php
class Attendance{
	function __construct(){
		
	}
	
	public function check_status($user_id, $date, $status){
		//Подключаем объект для работы с базой данных
		global $mysqli;
		
		//Разделяем дату на куски
		$day = date( 'j', strtotime($date) );
		$month = date( 'n', strtotime($date) );
		$year = date( 'Y', strtotime($date) );
		
		//Подготавливаем результат, чтобы проверить задан ли явно указанный день, как выходной
		$result = $mysqli->query( 'SELECT `hours` FROM `phpbb_timetable`
		                                    WHERE `user_id`=' . $user_id . '
											  AND `day`=' . $day . '
											  AND `month`=' . $month . '
											  AND `year`=' . $year . '
											  AND `status`=' . $status );
		
		//Проверяем, обозначен ли заданный статус для указанного дня
		if( $result->num_rows > 0 ){
			$row = $result->fetch_array();
			return $row['hours'];
		}else{
			return false;
		}
	}

	public function is_work_day($user_id, $date){
		//Получаем номер дня недели от 1 до 7
		$week_day_number = date( 'N', strtotime($date) );
		
		/* !!! Обязательно соблюдаем последовательность проверок !!! */
		
		//Этот день является явно заданным рабочим днем
		if( $this->check_status($user_id, $date, 1) ){
			return true;
		}

		//Этот день является явно заданным выходным
		if( $this->check_status($user_id, $date, 6) ){
			return false;
		}
		
		//Этот день является рабочим по умолчанию
		if( $week_day_number >=  1 && $week_day_number <=  5 ){
			return true;
		}
		
		//Этот день является выходным по умолчанию
		if( $week_day_number ==  6 || $week_day_number ==  7 ){
			return false;
		}
	}
	
	public function get_status($user_id, $date){
		//"Подключаем" глобальный объект для работы с базой данных
		global $mysqli;
		
		//Разделяем дату на куски
		$day = date( 'j', strtotime($date) );
		$month = date( 'n', strtotime($date) );
		$year = date( 'Y', strtotime($date) );
		$week_day = date( 'N', strtotime($date) );

		//Определяем статус по заданным $user_id и $date
		$result = $mysqli->query( 'SELECT `status` FROM `phpbb_timetable`
		                                    WHERE `user_id`=' . $user_id . '
											  AND `day`=' . $day . '
											  AND `month`=' . $month . '
											  AND `year`=' . $year );
		
		//Проверяем, обозначен ли заданный статус для указанного дня
		if( $result->num_rows > 0 ){
			$row = $result->fetch_array();
			return $row['status'];
		}else{
			//Статус "Выходной" (#6), явно не определен
			if( $week_day==6 || $week_day==7 ){
				return 6;
			}
			
			//Статус "Рабочий день" (#1), явно не определен
			if( $week_day >=1 && $week_day <= 5){
				return 1;
			}
		}
	}
}
?>