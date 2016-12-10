<?php
function show_arenda(){
	//Bind global variables
	global $config_arenda;
	
	//Retrieve arenda id from browser
	$arenda_id=(int)$_GET['arenda'];
	
	//Get SQL for request
	$arenda_sql="SELECT *,`phpbb_arendas`.`name` as `name`, `phpbb_clusters`.`name` as `cluster_name`,
								   `phpbb_categories`.`name` as `category_name`, `phpbb_objects`.`name` as `object_name`
								   FROM `phpbb_arendas`
								   LEFT JOIN `phpbb_clusters` ON `phpbb_arendas`.`cluster_id`=`phpbb_clusters`.`id`
								   LEFT JOIN `phpbb_categories` ON `phpbb_arendas`.`category_id`=`phpbb_categories`.`id`
								   LEFT JOIN `phpbb_objects` ON `phpbb_arendas`.`object_id`=`phpbb_objects`.`id`
								   WHERE `phpbb_arendas`.`id`=".$arenda_id;							
	//show($arenda_sql);
	
	//Retrieve arenda entity from database
	$arenda_res=db_query($arenda_sql);	
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