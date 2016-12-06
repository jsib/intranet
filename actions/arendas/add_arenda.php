<?php
function add_arenda(){
	/*Проверка прав на выполнение действия*/
	if(!check_rights('add_arenda')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	//IF
	if(!isset($_POST['name'])){
		//Возвращаем значение функции
		return show_form_add_arenda();
	//ELSE
	}else{
		$entity_name=$_POST['name'];
		
		//Check entity name
		if(preg_match(REGEXP_USERNAME, $entity_name)){
			
			if(db_easy_count("SELECT `id` FROM `phpbb_arendas` WHERE `name`='".$entity_name."'")>0){
				$errors[]=ERROR_USERNAME_EXISTS;
			}
		}else{
			$errors[]=ERROR_USERNAME_REQUIREMENT;
		}
		
		//IF
		if(count($errors)==0){
			db_query("INSERT INTO `phpbb_arendas` SET `name`='".$entity_name."'");

			$arenda_id=db_insert_id();
			
			//Отправляем HTTP запрос
			header("location: /manager.php?action=edit_arenda&arenda=$arenda_id");
		//ELSE
		}else{
			//Возвращаем значение функции
			return show_form_add_arenda($_POST, $errors);
		}
	}
}

/*Возвращает HTML код формы*/
function show_form_add_arenda($arenda=array(), $messages=array()){
	//Определяем значение переменной
	$message_html=show_messages($messages);
	
	/*Подключаем шаблон*/
	return template_get("arendas/add_arenda", array(	
															'name'=>$arenda['name'],
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