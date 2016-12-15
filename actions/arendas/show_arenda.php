<?php
function show_arenda(){
	//Bind global variables
	global $config_arenda;
	
	//Retrieve arenda id from browser
	$arenda_id=(int)$_GET['arenda'];
	
	//Define binded entities columns
	$binded_columns_database=$config_arenda['binded_columns_database'];
	
	//Build SQL for database request
	$sql='SELECT `phpbb_arendas`.`id` as `id`';
	
	//Build SQL-piece for standart text columns of database table
	if(isset($config_arenda['standart_text_data_database']) && count($config_arenda['standart_text_data_database'])>0){
		foreach($config_arenda['standart_text_data_database'] as $name_for){
			$sql.=', `phpbb_arendas`.`'.$name_for.'` as `'.$name_for.'`'; 
		}
	}
	
	//Build SQL-piece for date columns of database table
	if(isset($config_arenda['standart_date_data_database']) && count($config_arenda['standart_date_data_database'])>0){
		foreach($config_arenda['standart_date_data_database'] as $name_for){
			$sql.=', `phpbb_arendas`.`'.$name_for.'` as `'.$name_for.'`'; 
		}
	}
	
	//Build SQL-pieces to retrieve information of binded entities
	foreach($binded_columns_database as $name_for=>$name_plural_for){
		$sql.=", `phpbb_".$name_plural_for."`.`name` as `".$name_for."_name`";
		$sql.=", `phpbb_".$name_plural_for."`.`id` as `".$name_for."_id`"; 
	}
	
	//Build FROM SQL
	$sql.=" FROM `phpbb_arendas` ";
	
	//Build 'LEFT JOIN' SQL for binded entities
	foreach($binded_columns_database as $name_for=>$name_plural_for){
		$sql.=" LEFT JOIN `phpbb_".$name_plural_for."` ON `phpbb_arendas`.`".$name_for."_id`=`phpbb_".$name_plural_for."`.`id` ";
	}
	
	//Build WHERE clause
	$sql.=' WHERE `phpbb_arendas`.`id`='.$arenda_id;
	
	//Retrieve arenda entity from database
	$arenda_res=db_query($sql);	
	
	if(db_count($arenda_res)>0){
	 	$arenda=db_fetch($arenda_res); 
		//show($arenda);
	}else{
		system_error("Arenda with id".$arenda_id." doesn't exist");
	}
	
	//Replace empty values with dash
	foreach($arenda as $key=>$value){
		if($value==""){
			$arenda[$key]="-";
		}
	}
	
	//Build edit arenda link
	if(check_rights('edit_arenda')){
		$template_replacements['edit_arenda_link']="<a href='/manager.php?action=edit_arenda&arenda=".$arenda_id."' style='font-size:8pt;'>Редактировать</a>";
	}else{
		$template_replacements['edit_arenda_link']='';
	}
	
	//Build list arendas link
	$template_replacements['list_arendas_link']="<a href='/manager.php?action=list_arendas' style='font-size:8pt;'>Все точки аренды</a>";

	//Form template replacements with text data
	$replacements=$config_arenda['standart_text_data_form'];
	foreach($replacements as $empty=>$replacement){
		$template_replacements[$replacement]=$arenda[$replacement];
	}
	
	//Form template replacements with date data
	foreach($config_arenda['standart_date_data_form'] as $empty=>$replacement){
		if(in_array($arenda[$replacement], $config_arenda['empty_dates'])){
			$template_replacements[$replacement]="-";
		}else{
			$template_replacements[$replacement]=date("d.m.Y", strtotime($arenda[$replacement]));
		}
	}
	
	//Return  HTML flow
	return template_get("arendas/show_arenda", $template_replacements);
}
?>