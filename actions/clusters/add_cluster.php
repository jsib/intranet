<?php
function add_cluster(){
	if(!check_rights('add_cluster')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	//Show empty HTML form
	if(!isset($_POST['name'])){
		
		//Build result messages
		if(isset($_GET['result'])){
			switch($_GET['result']){
				case "empty_cluster_name":
					$message_html=template_get("errormessage", array('message'=>"Название кластера не может быть пустым"));	
				break;
				case "same_cluster_exists":
					$message_html=template_get("errormessage", array('message'=>"Кластер с таким именем уже существует"));	
				break;
				default:
					$message_html=template_get("nomessage");
			}
		}
		
		//Return HTML flow
		$html.=template_get("clusters/add_cluster", array(	
																'action'=>"/manager.php?action=add_cluster",
																'branches'=>$branches_html,
																'message'=>$message_html
													));
	//Process retrived data
	}else{
		//Define checks flag
		$do=true;
		
		//Check for empty entity name
		$cluster_name=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $cluster_name)){
			header("location: /manager.php?action=add_cluster&result=empty_cluster_name");
			$do=false;
		}
		//Check for same entity existance
		if(db_easy_count("SELECT * FROM `phpbb_clusters` WHERE `name`='".$cluster_name."'")>0){
			//Refer to other page
			header("location: /manager.php?action=add_cluster&result=same_cluster_exists");
			
			//Change value of checks flag
			$do=false;
		}
		
		//Perform query to database
		if($do){
			db_query("INSERT INTO `phpbb_clusters` SET
										`name`='{$cluster_name}'
										");
			//Get id of inserted entity
			$cluster_id=db_insert_id();
			
			//Refer to other page
			header("location: /manager.php?action=list_clusters&result=cluster_added_success&name=".$cluster_name);
		}
	}
	return $html;
}
?>