<?php
function show_object(){
	//Get object id
	$object_id=(int)$_GET['object'];
	
	//Retrieve object from database
	$object=db_easy("SELECT * FROM `phpbb_objects` WHERE `id`=".$object_id);
	
	//Check rights to perform this actions
	if(check_rights('edit_object')){
		//Build edit object link
		$edit_object_link="<a href='/manager.php?action=edit_object&object=".$object_id."' style='font-size:8pt;'>Редактировать</a>";
	}
	
	//Build list objects link
	$list_objects_link="<a href='/manager.php?action=list_objects' style='font-size:8pt;color:black;text-decoration:underline;'>Все объекты</a>";
	
	$html.=template_get("objects/show_object", array(
																'name'=>$object['name'],
																'list_objects_link'=>$list_objects_link,
																'edit_object_link'=>$edit_object_link,
																'message'=>$message_html,
												));
	return $html;
}
?>