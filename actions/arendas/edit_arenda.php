<?php
//Подключаем вспомогательный скрипт
require_once($_SERVER['DOCUMENT_ROOT']."/actions/arendas/add_arenda.php");

//Функция
function edit_arenda(){
	//Глобальная переменная
	global $Dbh;

	/*Проверка прав на выполнение действия*/
	if(!check_rights('edit_arenda')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	/*Получаем id, предварительно проверив*/
	$arenda_id=(int)$_GET['arenda'];
	$arendaRES=db_query("SELECT * FROM `phpbb_arendas` WHERE `id`=$arenda_id");
	if(db_count($arendaRES)>0){
		$arenda=db_fetch($arendaRES);
	}else{
		$errors[]="Критическая ошибка входных данных (arenda_id)";
	}
	
	//IF
	if(!isset($_POST['name'])){
		//Get entity from database
		$arenda=db_easy("SELECT * FROM `phpbb_arendas` WHERE `id`=".$arenda_id);
		
		//Return HTML code of form
		$html.=show_form_edit_arenda($arenda);
	}else{
		$entity_name=$_POST['name'];
		//Check name of entity
		if(preg_match(REGEXP_USERNAME, $entity_name)){
			if(db_easy_count("SELECT `id` FROM `phpbb_arendas` WHERE `name`='".$entity_name."' AND `id`!=$arenda_id")>0){
				$errors[]=ERROR_USERNAME_EXISTS;
			}else{
				$name=$entity_name;
			}
		}else{
			$errors[]=ERROR_USERNAME_REQUIREMENT;
		}
		
		
		//REGEXP_EASY_STRING
		$strings_sql="";
		$strings_params=array('status');
		foreach($strings_params as $nameFOR){
				$strings_sql.="`".$nameFOR."`= :".$nameFOR." , ";
		}	

		//Numeric fields
		$cluster_id=(int)$_POST['cluster'];
		$category_id=(int)$_POST['category'];
		
		//Checkboxes
		$checkboxes_sql="";
		foreach(array() as $nameFOR){
			if($_POST[$nameFOR]=="on"){
				$checkboxes_sql.="`$nameFOR`=1, ";
			}else{
				$checkboxes_sql.="`$nameFOR`=0, ";
			}
		}

		//Проверяем наличие ошибок во входных данных
		if(count($errors)==0){
			//Формируем SQL запрос
			$sql="	UPDATE
						`phpbb_arendas` 
					SET 
						".$strings_sql."
						".$checkboxes_sql."
						`cluster_id`=$cluster_id,
						`category_id`=$category_id
					WHERE
						`id`=".$arenda_id;
						
			//show($sql);
			
			//Prepare expression
			$sth=$Dbh->prepare($sql);
			
			//Bind parameters to database
			foreach($strings_params as $nameFOR){
				$sth->bindParam(":".$nameFOR, $_POST[$nameFOR], PDO::PARAM_STR);
			}
			
			//Write to database
			if(!$sth->execute()) show($sth->errorInfo());

			//Get entity data from database which was just wrote
			$arenda=db_easy("SELECT * FROM `phpbb_arendas` WHERE `id`=".$arenda_id);
			
			//Return HTML of form
			return show_form_edit_arenda($arenda, $errors);
		}else{
			//Return HTML of form
			return show_form_edit_arenda($arenda, $errors);
		}
	}
	
	//Возвращаем HTML-код
	return $html;
}

//Return HTML code of form
function show_form_edit_arenda($arenda=array(), $messages=array()){
	//Bind global variables
	global $MonthsShort;
	
	//Retrieve message HTML
	$message_html=show_messages($messages);

	//Build list entities link
	$show_entity_link="<a href='/manager.php?action=show_arenda&arenda=".$arenda['id']."' style='font-size:8pt;'>Просмотреть</a>";
	
	//Build show entity link
	$list_entities_link="<a href='/manager.php?action=list_arendas' style='font-size:8pt;color:black;text-decoration:underline;'>Все точки аренды</a>";

	//Get select HTML
	$clusters_html=get_clusters_options($arenda);

	//Get select HTML
	$categorys_html=get_categorys_options($arenda);

	//Get select HTML
	$objects_html=get_objects_options($arenda);

	//Return HTML flow
	return template_get("arendas/edit_arenda", array(		
												'action'=>"/manager.php?action=edit_arenda&arenda=".$arenda['id'],
												'list_entities_link'=>$list_entities_link,
												'show_entity_link'=>$show_entity_link,
												'name'=>$arenda['name'],
												'status'=>$arenda['status'],
												'message'=>$message_html,
												'clusters'=>$clusters_html,
												'categorys'=>$categorys_html,
												'objects'=>$objects_html
											));
}

//Get list of entities HTML
function get_clusters_options($arenda){
	//Define HTML flow
	$html="";
	
	//Execute query to database
	$entities_res=db_query("SELECT * FROM `phpbb_clusters` ORDER BY `name` ASC");
	
	//IF
	if(db_count($entities_res)>0){
		//WHILE
		while($entity_while=db_fetch($entities_res)){
			$arenda['cluster_id']==$entity_while['id'] ? $selected="selected" : $selected="";
		
			$html.="<option value='{$entity_while['id']}' $selected>{$entity_while['name']}</option>";
		}
	}
	
	//Return HTML flow
	return $html;
}

//Get list of entities HTML
function get_categorys_options($arenda){
	//Define HTML flow
	$html="";
	
	//Execute query to database
	$entities_res=db_query("SELECT * FROM `phpbb_categorys` ORDER BY `name` ASC");
	
	//IF
	if(db_count($entities_res)>0){
		//WHILE
		while($entity_while=db_fetch($entities_res)){
			$arenda['cluster_id']==$entity_while['id'] ? $selected="selected" : $selected="";
		
			$html.="<option value='{$entity_while['id']}' $selected>{$entity_while['name']}</option>";
		}
	}
	
	//Return HTML flow
	return $html;
}

//Get list of entities HTML
function get_objects_options($arenda){
	//Define HTML flow
	$html="";
	
	//Execute query to database
	$entities_res=db_query("SELECT * FROM `phpbb_objects` ORDER BY `name` ASC");
	
	//IF
	if(db_count($entities_res)>0){
		//WHILE
		while($entity_while=db_fetch($entities_res)){
			$arenda['object_id']==$entity_while['id'] ? $selected="selected" : $selected="";
		
			$html.="<option value='{$entity_while['id']}' $selected>{$entity_while['name']}</option>";
		}
	}
	
	//Return HTML flow
	return $html;
}
?>