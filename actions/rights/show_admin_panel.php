<?php
function show_admin_panel(){
	//Определяем переменную
	$html="";

	/*Подключаем файл шаблона*/
	$html.=template_get("admin/show_admin_panel",
								array(
										
								));

	
	//Возвращаем значение функции
	return $html;
}
?>