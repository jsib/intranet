<?php
function add_contact(){
	/*Проверка прав на выполнение действия*/
	if(!check_rights('add_contact')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	//IF
	if(!isset($_POST['name'])){
		//Возвращаем значение функции
		return show_form_add_contact();
	//ELSE
	}else{
		//Проверка 'name'
		if(preg_match(REGEXP_USERNAME, $_POST['name'])){
			if(db_easy_count("SELECT `user_id` FROM `phpbb_users` WHERE `username`='".$_POST['name']."'")>0){
				$errors[]=ERROR_USERNAME_EXISTS;
			}else{
				$name=$_POST['name'];
			}
		}else{
			$errors[]=ERROR_USERNAME_REQUIREMENT;
		}
		
		//IF
		if(count($errors)==0){
			//Определяем переменную
			/*$user_data=array(	'username'=>$name.,
								'group_id'=>'1774',
								'user_lang'=>'ru',
								'user_type'=>0,
								'user_regdate'=>time()
							);*/
							
			$user_data=array(	'username'=>$name,
								'user_password'=>phpbb_hash('вава'),
								'user_email'=>'',
								'group_id'=>'1774', 
								'user_lang'=>'ru',
								'user_type'=>0,
								'user_regdate'=>time(),
								'point_id'=>1
							);
			
			/*Добавляем пользователя, использую функцию PHPBB*/
			$user_id=user_add($user_data);
			
			//Отправляем HTTP запрос
			header("location: /manager.php?action=edit_contact&contact=$user_id");
		//ELSE
		}else{
			//Возвращаем значение функции
			return show_form_add_contact($_POST, $errors);
		}
	}
}

/*Возвращает HTML код формы*/
function show_form_add_contact($contact=array(), $messages=array()){
	//Определяем значение переменной
	$message_html=show_messages($messages);
	
	/*Подключаем шаблон*/
	return template_get("contacts/add_contact", array(	
															'name'=>$contact['name'],
															'message'=>$message_html
												));
	
}

/*Возвращает HTML с сообщениями*/
function show_messages($messages){
	//Определяем переменную
	$html="";

	/*Сообщение о результате действия*/
	if(count($messages)>0){
		
		//FOREACH
		foreach($messages as $index=>$message){
			//Определяем переменную
			$messages_html.=$message;
			
			//Сокращенный IF-ELSE
			$index<count($messages) ? $messages_html.="<br/>" : '';
		}
		
		/*Подключаем шаблон*/
		$html=template_get("errormessage", array('message'=>$messages_html));	
	}else{
		/*Подключаем шаблон*/
		$html=template_get("nomessage");
	}
	
	//Возвращаем значение функции
	return $html;
}
?>