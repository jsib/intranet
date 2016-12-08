<?php
function delete_arenda(){
	//Check rights to delete arenda
	if(check_rights('delete_arenda')){
		//Retrieve arenda id from browser
		$arenda_id=(int)$_GET['arenda'];
		
		//Delete arenda entity from database
		if(db_easy_count("SELECT * FROM `phpbb_arendas` WHERE `id`=".$arenda_id)>0){
			db_query("DELETE FROM `phpbb_arendas` WHERE `id`=".$arenda_id);
			if(db_result()>0){
				header("location: /manager.php?action=list_arendas&result=arenda_deleted");
			}else{
				system_error("Error when attempt to delete arenda entity with id ".$arenda_id." from database");
			}
		//No arenda entity with this id
		}else{
			system_error("Arenda entity with id ".$arenda_id." doesn't exist in database");
		}
		
		
		return $html;
	}else{
		system_error("No rights to action delete_arenda", ERR_NO_PERMISSION);
	}
}
?>