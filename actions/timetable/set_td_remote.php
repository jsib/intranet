<?php
function set_td_remote(){
	/*�������� ������ �� ������������*/
	if(isset($_GET['td'])){
		if(!preg_match("/^[0-9]{1,8}\-[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}\-[01]{1}-[01]{1}$/", $_GET['td'])){
			return "������ � ������� ������� ������ (td).";
		}else{
			$td=$_GET['td'];
		}
	}else{
		return "�� ���������� ������� ������ (td)";
	}
	
	/*�������� ������ �� ������������*/
	if(isset($_GET['status'])){
		if(!preg_match("/^[0-9]{1,3}$/", $_GET['status'])){
			return "������ � ������� ������� ������ (status).";
		}else{
			$status=(int)$_GET['status'];
		}
	}else{
		return "�� ���������� ������� ������ (status)";
	}
	
	/*�������� ������ �� ������������*/
	$hours=(int)$_GET['hours'];
	
	/*������������ ���������� ������*/
	$temp=explode('-', $td);
	$user_id=(int)$temp[0];
	$year=(int)$temp[1];
	$month=(int)$temp[2];
	$day=(int)$temp[3];

	/*��������� ������� user_id*/
	if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id")==0){
		return "������ ������� ������ (user_id).";
	}
	
	//������ � ����
	if(db_easy_count("SELECT * FROM `phpbb_timetable` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id")==0){
		return db_result(db_query("INSERT INTO `phpbb_timetable` SET `year`=$year, `month`=$month, `day`=$day, `user_id`=$user_id, `status`=$status, `hours`=$hours"));
	}else{
		//IF
		/*status=1 �� �� �����, ��� ������ �� ���� ������ ����������� � ��*/
		//if($status==1){
			//return db_easy_result("DELETE FROM `phpbb_timetable` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id");
		//ELSE	
		//}else{
			/*���� ����� ����� �� ������ ��� ����������*/
			if(db_easy_count("SELECT * FROM `phpbb_timetable` WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id AND `status`=$status AND `hours`=$hours")==1){
				return 1;
			/*����� ���� ���������� ������ � ��*/
			}else{
				return db_easy_result("UPDATE `phpbb_timetable` SET `status`=$status, `hours`=$hours WHERE `year`=$year AND `month`=$month AND `day`=$day AND `user_id`=$user_id");
			}
		//}
	}
}
?>