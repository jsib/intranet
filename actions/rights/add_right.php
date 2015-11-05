<?php
function add_right(){
	//Определяем переменные
	$html="";
	$users_html="";
	
	/*Проверка прав на выполнение действия*/
	if(!check_rights('add_right')){
		return "У вас нет соответствующих прав";
	}
	
	//IF
	if(!isset($_POST['user'])){
		/*Получаем и проверяем данные от пользвователя*/
		$right_id=(int)$_GET['right'];
		
		/*Проверка входных данных*/
		$rightRES=db_query("SELECT `name` FROM `phpbb_rights` WHERE `id`='$right_id'");
		if(db_count($rightRES)==0){
			return "Ошибка в формате входных данных (right)";
		}else{
			$right_name=db_fetch($rightRES)['name'];
		}
		
		//Запрос к базе
		$usersRES=db_query("SELECT * FROM `phpbb_users`
								WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root'
													ORDER BY `username` ASC");
		
		//WHILE
		while($userWHILE=db_fetch($usersRES)){
			if(db_easy_count("SELECT * FROM `phpbb_rights_users`
								WHERE `user_id`={$userWHILE['user_id']}
									AND `right_id`=$right_id
						")==0){
				$users_html.="<option value='{$userWHILE['user_id']}'>{$userWHILE['username']}</option>";
			}
		}
		
		/*Подключаем шаблон*/
		$html.=template_get("rights/add_right", array(	
																'users'=>$users_html,
																'right_id'=>$right_id,
																'right_name'=>$right_name
													));
	//ELSE
	}else{
		/*Получаем и проверяем данные от пользвователя*/
		$user_id=(int)$_POST['user'];

		/*Получаем и проверяем данные от пользвователя*/
		$right_id=(int)$_POST['right'];

		/*Проверка входных данных*/
		if(db_easy_count("SELECT * FROM `phpbb_users` WHERE `user_id`=$user_id")==0){
			return "Ошибка в формате входных данных (user)";
		}
		
		/*Проверка входных данных*/
		if(db_easy_count("SELECT * FROM `phpbb_rights` WHERE `id`='$right_id'")==0){
			return "Ошибка в формате входных данных (right)";
		}
		
		//Запрос к базе
		if(db_easy_count("SELECT * FROM `phpbb_rights_users` WHERE `user_id`=$user_id AND `right_id`=$right_id")==0){
			$insertRES=db_query("INSERT INTO `phpbb_rights_users` SET `user_id`=$user_id, `right_id`=$right_id");
		}
		
		/*Проверка правильности выполнения запроса к БД*/
		if(!db_result($insertRES)){
			return "Ошибка при выполнении (insert)";
		}
		
		//Выполняем HTTP запрос
		header("location: /manager.php?action=show_rights");
	}
	
	//Возвращаем значение функции
	return $html;
}
?>