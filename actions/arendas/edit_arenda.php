<?php
//Require help script
require_once($_SERVER['DOCUMENT_ROOT']."/actions/arendas/add_arenda.php");

//Provide edit functionality for arenda entities 
function edit_arenda(){
	//Bind global variables
	global $Dbh;
	global $config_arenda;

	//Check rights to perform this action
	if(!check_rights('edit_arenda')){
		system_error('No permissions for edit_arenda action', ERR_NO_PERMISSION);
	}
	
	//Get arenda id from browser
	$arenda_id=(int)$_GET['arenda'];
	
	//Retrieve arenda entity from database
	$arenda_res=db_query("SELECT * FROM `phpbb_arendas` WHERE `id`=".$arenda_id);
	if(db_count($arenda_res)>0){
		$arenda=db_fetch($arenda_res);
	}else{
		system_error("Arenda entity with id ".$arenda_id." doesn't exist in database");
	}
	
	//Build empty HTML form
	if(!isset($_POST['name'])){
		//Get entity from database
		$arenda=db_easy("SELECT * FROM `phpbb_arendas` WHERE `id`=".$arenda_id);
		
		//Return HTML code of form
		$html.=show_form_edit_arenda($arenda);
	//Process retrieved data
	}else{
		$arenda_name=$_POST['name'];
		
		//Check name of entity
		if(preg_match(REGEXP_USERNAME, $arenda_name)){
			if(db_easy_count("SELECT `id` FROM `phpbb_arendas` WHERE `name`='".$arenda_name."' AND `id`!=".$arenda_id)>0){
				$errors[]="Точка аренды с таким названием уже существует.";
			}
		}else{
			$errors[]="Название точки аренды ".TXT_REQUIREMENTS_NAME;
		}
		
		
		//Build SQL pieces for standart text data
		$text_sql="";
		foreach(array_merge($config_arenda['standart_text_data_database'], $config_arenda['standart_date_data_database']) as $nameFOR){
			$text_sql.="`".$nameFOR."`= :".$nameFOR.", ";
		}
		$text_sql=substr($text_sql, 0, -2);

		//Build SQL pieces for standart numeric data
		$numeric_sql="";
		foreach($config_arenda['standart_numeric_data_database'] as $nameFOR){
			$numeric_sql.="`".$nameFOR."_id`= :".$nameFOR.", ";
		}
		$numeric_sql=substr($numeric_sql, 0, -2);
		
		//Checkboxes
		$checkboxes_sql="";
		foreach(array() as $nameFOR){
			if($_POST[$nameFOR]=="on"){
				$checkboxes_sql.="`$nameFOR`=1, ";
			}else{
				$checkboxes_sql.="`$nameFOR`=0, ";
			}
		}
		$checkboxes_sql=substr($checkboxes_sql, 0, -2);

		//Check for errors in retrieved data
		if(count($errors)==0){
			//Build SQL request
			$sql="	UPDATE
						`phpbb_arendas` 
					SET 
						".$text_sql.", "
						.$numeric_sql
						.$checkboxes_sql." 
					WHERE
						`id`=".$arenda_id;
						
			//show($sql);
			//show($_POST);
			
			//Prepare expression
			$sth=$Dbh->prepare($sql);
			
			//Bind text parameters to PDO
			foreach($config_arenda['standart_text_data_database'] as $nameFOR){
				$sth->bindParam(":".$nameFOR, trim($_POST[$nameFOR]), PDO::PARAM_STR);
			}

			//Bind date parameters to PDO
			foreach($config_arenda['standart_date_data_database'] as $nameFOR){
				$sth->bindParam(":".$nameFOR, date("Y-m-d", strtotime(trim($_POST[$nameFOR]))), PDO::PARAM_STR);
			}
			
			//Bind numeric parameters to PDO
			foreach($config_arenda['standart_numeric_data_database'] as $nameFOR){
				$sth->bindParam(":".$nameFOR, trim($_POST[$nameFOR]), PDO::PARAM_INT);
			}
			
			//Write to database via PDO
			if(!$sth->execute()){
				system_error($sth->errorInfo());
			}

			//Get entity data from database which was just wrote
			$arenda=db_easy("SELECT * FROM `phpbb_arendas` WHERE `id`=".$arenda_id);
			
			$messages[]="Изменения успешно сохранены";
			
			//Return HTML of form
			return show_form_edit_arenda($arenda, $messages);
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
	global $config_arenda;
	
	//Retrieve message HTML
	$template_replacements['message']=show_messages($messages);

	//Define SELECTs array
	$selects=array('cluster'=>'clusters', 'category'=>'categories', 'object'=>'objects');
	
	//Build HTML of SELECTs
	foreach($selects as $object_for=>$object_plural_for){
		$template_replacements[$object_plural_for]=get_bind_entities_options($object_for, $object_plural_for, $arenda);
	}
		
	//Build action link
	$template_replacements['action_link']="/manager.php?action=edit_arenda&arenda=".$arenda['id'];
	
	//Build list arendas link
	$template_replacements['list_arendas_link']="<a href='/manager.php?action=list_arendas' style='font-size:8pt;color:black;text-decoration:underline;'>Все точки аренды</a>";
	
	//Build show arenda link
	$template_replacements['show_arenda_link']="<a href='/manager.php?action=show_arenda&arenda=".$arenda['id']."' style='font-size:8pt;'>Просмотреть</a>";


	//Form template replacements with text data
	foreach($config_arenda['standart_text_data_form'] as $empty=>$replacement){
		$template_replacements[$replacement]=htmlspecialchars($arenda[$replacement]);
	}
	
	//Form template replacements with date data
	foreach($config_arenda['standart_date_data_form'] as $empty=>$replacement){
		if(in_array($arenda[$replacement], $config_arenda['empty_dates'])){
			$template_replacements[$replacement]="";
		}else{
			$template_replacements[$replacement]=date("d.m.Y", strtotime($arenda[$replacement]));
		}
	}
	
	//Return  HTML flow
	return template_get("arendas/edit_arenda", $template_replacements);	
}

//Get list of entities HTML
function get_bind_entities_options($object, $object_plural, $entity){
	//Define HTML flow
	$html="";
	
	$html.="<option value=''>".TXT_OPTION_NOT_DEFINED."</option>"; 
	
	//Execute query to database
	$entities_res=db_query("SELECT * FROM `phpbb_".$object_plural."` ORDER BY `name` ASC");
	
	//Build HTML list
	if(db_count($entities_res)>0){
		while($entity_while=db_fetch($entities_res)){
			$entity[$object.'_id']==$entity_while['id'] ? $selected="selected" : $selected="";
		
			$html.="<option value='".$entity_while['id']."' ".$selected.">".$entity_while['name']."</option>";
		}
	}
	
	//Return HTML flow
	return $html;
}
?>