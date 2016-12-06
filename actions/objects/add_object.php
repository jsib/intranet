<?php
function add_object(){
	if(!check_rights('add_object')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	//Show empty HTML form
	if(!isset($_POST['name'])){
		
		//Build result messages
		if(isset($_GET['result'])){
			switch($_GET['result']){
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
		
		//Return HTML flow
		$html.=template_get("objects/add_object", array(	
																'action'=>"/manager.php?action=add_object",
																'branches'=>$branches_html,
																'message'=>$message_html
													));
	//Process retrived data
	}else{
		//Define checks flag
		$do=true;
		
		//Check for empty entity name
		$object_name=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $object_name)){
			header("location: /manager.php?action=add_object&result=empty_object_name");
			$do=false;
		}
		//Check for same entity existance
		if(db_easy_count("SELECT * FROM `phpbb_objects` WHERE `name`='".$object_name."'")>0){
			//Refer to other page
			header("location: /manager.php?action=add_object&result=same_object_exists");
			
			//Change value of checks flag
			$do=false;
		}
		
		//Perform query to database
		if($do){
			db_query("INSERT INTO `phpbb_objects` SET
										`name`='{$object_name}'
										");
			//Get id of inserted entity
			$object_id=db_insert_id();
			
			//Refer to other page
			header("location: /manager.php?action=list_objects&result=object_added_success&name=".$object_name);
		}
	}
	return $html;
}
?>