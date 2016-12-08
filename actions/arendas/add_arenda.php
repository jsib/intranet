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
		$entity_name=trim($_POST['name']);
		
		//Check entity name
		if(preg_match(REGEXP_USERNAME, $entity_name)){
			
			if(db_easy_count("SELECT `id` FROM `phpbb_arendas` WHERE `name`='".$entity_name."'")>0){
				$errors[]="Точка аренды с таким названием уже существует";
			}
		}else{
			$errors[]="Название аренды ".TXT_REQUIREMENTS_NAME;
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
	//Return HTML
	$message_html=show_messages($messages);
	
	//Return HTML
	return template_get("arendas/add_arenda", array(	
															'name'=>$arenda['name'],
															'message'=>$message_html
												));
	
}

//Return HTML with message
function show_messages($messages){
	//Define HTML flow
	$html="";

	//Process retrieved messages
	if(count($messages)>0){
		
		//FOREACH
		foreach($messages as $message_key=>$message){
			//Определяем переменную
			$messages_html.=$message;
			
			//Сокращенный IF-ELSE
			$message_key<count($messages) ? $messages_html.="<br/>" : '';
		}
		
		//Build HTML
		$html=template_get("message", array('message'=>$messages_html));	
	//No messages presented
	}else{
		//Build HTML
		$html=template_get("nomessage");
	}
	
	//Возвращаем значение функции
	return $html;
}
?>