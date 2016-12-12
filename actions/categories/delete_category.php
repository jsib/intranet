<?php
function delete_category(){
	//Check rights to perform this action
	if(!check_rights('delete_category')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}

	//Get data from browser
	$category_id=(int)$_GET['category'];
	
	//Perform query to database
	$category_res=db_query("SELECT * FROM `phpbb_categories` WHERE `id`=".$category_id);
	
	//Check for category existence
	if(db_count($category_res)==0){
		system_error();
	}else{
		//Get category from database
		$category=db_fetch($category_res);
		
		//Delete category from database
		db_query("DELETE FROM `phpbb_categories` WHERE `id`=".$category_id);
		
		//Refer to other page
		header("location: /manager.php?action=list_categories&result=category_deleted&name=".urlencode($category['name']));
	}
}
?>