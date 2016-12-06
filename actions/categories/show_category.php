<?php
function show_category(){
	//Get category id
	$category_id=(int)$_GET['category'];
	
	//Retrieve category from database
	$category=db_easy("SELECT * FROM `phpbb_categories` WHERE `id`=".$category_id);
	
	//Check rights to perform this actions
	if(check_rights('edit_category')){
		//Build edit category link
		$edit_category_link="<a href='/manager.php?action=edit_category&category=".$category_id."' style='font-size:8pt;'>Редактировать</a>";
	}
	
	//Build list categories link
	$list_categories_link="<a href='/manager.php?action=list_categories' style='font-size:8pt;color:black;text-decoration:underline;'>Все категории</a>";
	
	$html.=template_get("categories/show_category", array(
																'name'=>$category['name'],
																'list_categories_link'=>$list_categories_link,
																'edit_category_link'=>$edit_category_link,
																'message'=>$message_html,
												));
	return $html;
}
?>