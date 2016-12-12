<?php
function edit_category(){
	//Check rights for this action
	if(!check_rights('edit_category')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}

	//Retrieve category id from browser
	$category_id=(int)$_GET['category'];
	
	if(!isset($_POST['name'])){
		//Build result messages
		if(isset($_GET['result'])){
			switch(@$_GET['result']){
				case "category_saved_success":
					$message_html=template_get("message", array('message'=>"Изменения успешно сохранены"));
				break;
				case "empty_category_name":
					$message_html=template_get("errormessage", array('message'=>"Название категории не может быть пустым"));
				break;
				case "same_category_exists":
					$message_html=template_get("errormessage", array('message'=>"Категория с таким именем уже имеется"));
				break;
				default:
					$message_html=template_get("nomessage");
			}
		}
		
		//Retrieve categories from database
		$category = db_easy("SELECT * FROM `phpbb_categories` WHERE `id`=$category_id");
		
		//Build list categories link
		$list_categories_link="<a href='/manager.php?action=list_categories' style='font-size:8pt;color:black;text-decoration:underline;'>Все категории</a>";
		
		//Build form action link
		$action_link="/manager.php?action=edit_category&category=".$category_id;
		
		//Build edit category link
		$show_category_link="<a href='/manager.php?action=show_category&category=".$category_id."' style='font-size:8pt;'>Просмотреть</a>";

		//Return HTML flow
		$html.=template_get("categories/edit_category", array(	'list_categories_link'=>$list_categories_link,
																'action_link'=>$action_link,
																'show_category_link'=>$show_category_link,
																'name'=>$category['name'],
																'message'=>$message_html
																));
	}else{
		$category['name']=trim($_POST['name']);
		
		//Define checks flag
		$do=true;
		
		//Check for empty category name
		$category['name']=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $category['name'])){
			header("location: /manager.php?action=edit_category&category=".$category_id."&result=empty_category_name");
			$do=false;
		}
		
		//Check for category with same name
		$other_categories_res=db_query("SELECT * FROM `phpbb_categories` WHERE `name`='".$category['name']."'");
		$other_category=db_fetch($other_categories_res);
		if(db_count($other_categories_res)>0){
			if($other_category['id']!=$category_id){
				header("location: /manager.php?action=edit_category&category=".$category_id."&result=same_category_exists");
				$do=false;
			}
		}
		
		//Perform database request
		if($do){
			db_query("UPDATE `phpbb_categories`
					  SET `name`='".$category['name']."'
					  WHERE `id`=".$category_id);
					  
			//Refer to other page		  
			header("location: /manager.php?action=edit_category&category=".$category_id."&result=category_saved_success");
		}
	}
	
	//Return HTML flow
	return $html;
}
?>