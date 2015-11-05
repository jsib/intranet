<?php
function add_hr_manager_right(){
	//Определяем переменные
	$html="";
	$users_html="";
	
	/*Проверка прав на выполнение действия*/
	if(!check_rights('add_hr_manager_right')){
		return "У вас нет соответствующих прав";
	}
	
	//IF
	if(!isset($_POST['user'])){
		//Запрос к базе
		$usersRES=db_query("SELECT * FROM `phpbb_users` WHERE (`user_type`=0 OR `user_type`=3) AND `hrmanager`!=1 AND `username`!='root' ORDER BY `username` ASC");
		
		//WHILE
		while($userWHILE=db_fetch($usersRES)){
			$users_html.="<option value='{$userWHILE['user_id']}'>{$userWHILE['username']}</option>";
		}
		
		/*Подключаем шаблон*/
		$html.=template_get("rights/add_hr_manager_right", array(	
																'users'=>$users_html
													));
	//ELSE
	}else{
		/*Проверка входных данных*/
		if(!preg_match("/^[0-9]{1,8}$/", $_POST['user'])){
			return "Ошибка в формате входных данных (1)";
		}
		
		//Определяем переменную
		$user_id=(int)$_POST['user'];
		
		/*Проверка входных данных*/
		if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id")==0){
			return "Ошибка в формате входных данных (2)";
		}
		
		//Запрос к базе
		db_query("UPDATE `phpbb_users` SET `hrmanager`=1 WHERE `user_id`=$user_id");
		
		/*Проверка правильности выполнения запроса к БД*/
		if(!db_result()){
			return "Ошибка при выполнении (3)";
		}
		
		//Выполняем HTTP запрос
		header("location: /manager.php?action=show_rights");
	}
	
	//Возвращаем значение функции
	return $html;
}
?>