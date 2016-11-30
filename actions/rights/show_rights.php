<?php
function show_rights(){
	//Определяем переменные
	$html="";
	$rights_html="";
	
	/*Выводим список менеджеров HR*/
	$rightsRES=db_query("SELECT * FROM `phpbb_rights` ORDER BY `name` ASC");  
	
	//IF
	if(db_count($rightsRES)>0){
		$i=0;
		//WHILE
		while($right=db_fetch($rightsRES)){
			if(trim($right['description'])!=""){
				$right_description_html="<div class='comment'>({$right['description']})</div>";
			}else{
				$right_description_html="<br/><br/>";
			}
				
			$rights_html.="<h4>{$right['name']}</h4><a href='/manager.php?action=add_right&right={$right['id']}'><img src='/images/add.png' /></a>
								$right_description_html
									";
			$rights_html.=show_right_users($right['id'], $right['name']);
			$rights_html.="<br/><br/>";
			if(db_count($rightsRES)!=$i+1) $rights_html.="<hr/><br/>";
			$i++;
		}
	}else{
		$rights_html.="<br/>Нет прав. Сперва создайте права.";
	}
	
	/*Подключаем файл шаблона*/
	$html.=template_get("rights/show_rights",
								array(	'rights_html'=>$rights_html
								));

	
	//Возвращаем значение функции
	return $html;
}

/*Выводим список менеджеров HR*/
function show_right_users($right_id, $right_name){
	//Определяем переменные
	$html="";
	
	//Запрос к базе
	$rightsRES=db_query("SELECT `phpbb_users`.`username`, `phpbb_rights_users`.`user_id`, `phpbb_rights_users`.`right_id`
							FROM `phpbb_rights_users`, `phpbb_users`
								WHERE `phpbb_rights_users`.`right_id`=$right_id
									AND `phpbb_rights_users`.`user_id`=`phpbb_users`.`user_id`
										ORDER BY `phpbb_users`.`username` ASC");
										
	//IF
	if(db_count($rightsRES)>0){
		//WHILE
		while($user=db_fetch($rightsRES)){
			$html.=$user['username'].
					"<a href='/manager.php?action=delete_right&user={$user['user_id']}&right={$user['right_id']}'><img src='/images/delete.png' style='padding-left:20px;' /></a><br/>";
		}
	//ELSE	
	}else{
		$html.="Ни один пользователь не имеет права $right_name";
	}
	
	//Возвращаем значение функции
	return $html;
}
?>