<?php
function edit_object(){
	//Check rights for this action
	if(!check_rights('edit_object')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}

	//Retrieve object id from browser
	$object_id=(int)$_GET['object'];
	
	if(!isset($_POST['name'])){
		//Build result messages
		if(isset($_GET['result'])){
			switch(@$_GET['result']){
				case "object_saved_success":
					$message_html=template_get("message", array('message'=>"Изменения успешно сохранены"));
				break;
				case "empty_object_name":
					$message_html=template_get("errormessage", array('message'=>"Название объекта не может быть пустым"));
				break;
				case "same_object_exists":
					$message_html=template_get("errormessage", array('message'=>"Объект с таким именем уже существует"));
				break;
				default:
					$message_html=template_get("nomessage");
			}
		}
		
		//Retrieve objects from database
		$object = db_easy("SELECT * FROM `phpbb_objects` WHERE `id`=$object_id");
		
		//Build list objects link
		$list_objects_link="<a href='/manager.php?action=list_objects' style='font-size:8pt;color:black;text-decoration:underline;'>Все объекты</a>";
		
		//Build form action link
		$action_link="/manager.php?action=edit_object&object=".$object_id;
		
		//Build edit object link
		$show_object_link="<a href='/manager.php?action=show_object&object=".$object_id."' style='font-size:8pt;'>Просмотреть</a>";

		//Return HTML flow
		$html.=template_get("objects/edit_object", array(	'list_objects_link'=>$list_objects_link,
																'action_link'=>$action_link,
																'show_object_link'=>$show_object_link,
																'name'=>$object['name'],
																'message'=>$message_html
																));
	}else{
		$object['name']=trim($_POST['name']);
		
		//Define checks flag
		$do=true;
		
		//Check for empty object name
		$object['name']=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $object['name'])){
			header("location: /manager.php?action=edit_object&object=".$object_id."&result=empty_object_name");
			$do=false;
		}
		
		//Check for object with same name
		$other_objects_res=db_query("SELECT * FROM `phpbb_objects` WHERE `name`='".$object['name']."'");
		$other_object=db_fetch($other_objects_res);
		if(db_count($other_objects_res)>0){
			if($other_object['id']!=$object_id){
				header("location: /manager.php?action=edit_object&object=".$object_id."&result=same_object_exists");
				$do=false;
			}
		}
		
		//Perform database request
		if($do){
			db_query("UPDATE `phpbb_objects`
					  SET `name`='".$object['name']."'
					  WHERE `id`=".$object_id);
					  
			//Refer to other page		  
			header("location: /manager.php?action=edit_object&object=".$object_id."&result=object_saved_success");
		}
	}
	
	//Return HTML flow
	return $html;
}
?>