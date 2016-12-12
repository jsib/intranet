<?php
function add_category(){
	//Check rights to perform this action
	if(!check_rights('add_category')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}
	
	//Show empty HTML form
	if(!isset($_POST['name'])){
		
		//Build result messages
		if(isset($_GET['result'])){
			switch($_GET['result']){
				case "empty_category_name":
					$message_html=template_get("errormessage", array('message'=>"Название категории не может быть пустым"));	
				break;
				case "same_category_exists":
					$message_html=template_get("errormessage", array('message'=>"Категория с таким именем уже существует"));	
				break;
				default:
					$message_html=template_get("nomessage");
			}
		}
		
		//Return HTML flow
		$html.=template_get("categories/add_category", array(	
																'action'=>"/manager.php?action=add_category",
																'branches'=>$branches_html,
																'message'=>$message_html
													));
	//Process retrived data
	}else{
		//Define checks flag
		$do=true;
		
		//Check for empty entity name
		$category_name=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $category_name)){
			header("location: /manager.php?action=add_category&result=empty_category_name");
			$do=false;
		}
		//Check for same entity existance
		if(db_easy_count("SELECT * FROM `phpbb_categories` WHERE `name`='".$category_name."'")>0){
			//Refer to other page
			header("location: /manager.php?action=add_category&result=same_category_exists");
			
			//Change value of checks flag
			$do=false;
		}
		
		//Perform query to database
		if($do){
			db_query("INSERT INTO `phpbb_categories` SET
										`name`='{$category_name}'
										");
			//Get id of inserted entity
			$category_id=db_insert_id();
			
			//Refer to other page
			header("location: /manager.php?action=list_categories&result=category_added_success&name=".$category_name);
		}
	}
	return $html;
}
?>