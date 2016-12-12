<?php
function add_entity(){
	//Bind global variables
	global $pattern_action;
	
	//Define help variables
	$object_name=$pattern_action['object_name'];
	$object_plural_name=$pattern_action['object_plural_name'];
	
	//Check rights to perform this action
	if(!check_rights('add_'.$object_name)){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}
	
	//Show empty HTML form
	if(!isset($_POST['name'])){
		
		//Build result messages
		if(isset($_GET['result'])){
			switch($_GET['result']){
				case "empty_entity_name":
					$message_html=template_get("errormessage", array('message'=>'Название '.$pattern_action['object_name_rus_rodit_padezh'].' не может быть пустым'));	
				break;
				case "same_entity_exists":
					$message_html=template_get("errormessage", array('message'=>mb_convert_case($pattern_action['object_name_rus'], MB_CASE_TITLE).' с таким именем уже существует'));	
				break;
				default:
					$message_html=template_get("nomessage");
			}
		}
		
		//Return HTML flow
		$html.=template_get("patterns/add_entity", array(	
																'action'=>'/manager.php?action=add_'.$object_name,
																'message'=>$message_html
													));
	//Process retrived data
	}else{
		//Define checks flag
		$do=true;
		
		//Check for empty entity name
		$entity_name=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $entity_name)){
			header('location: /manager.php?action=add_'.$object_name.'&result=empty_entity_name');
			$do=false;
		}
		//Check for same entity existance
		if(db_easy_count("SELECT * FROM `phpbb_entities` WHERE `name`='".$entity_name."'")>0){
			//Refer to other page
			header("location: /manager.php?action=add_entity&result=same_entity_exists");
			
			//Change value of checks flag
			$do=false;
		}
		
		//Perform query to database
		if($do){
			$add_entity_res=db_query("INSERT INTO `phpbb_".$object_plural_name."` SET `name`='".$entity_name."'");
			
			//Get id of inserted entity
			$entity_id=db_insert_id();
			
			//Refer to other page
			header("location: /manager.php?action=list_entities&result=entity_added_success&name=".$entity_name);
		}
	}
	return $html;
}
?>