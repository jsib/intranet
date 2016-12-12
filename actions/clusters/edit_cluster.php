<?php
function edit_cluster(){
	//Check rights for this action
	if(!check_rights('edit_cluster')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}

	//Retrieve cluster id from browser
	$cluster_id=(int)$_GET['cluster'];
	
	if(!isset($_POST['name'])){
		//Build result messages
		if(isset($_GET['result'])){
			switch(@$_GET['result']){
				case "cluster_saved_success":
					$message_html=template_get("message", array('message'=>"Изменения успешно сохранены"));
				break;
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
		
		//Retrieve clusters from database
		$cluster = db_easy("SELECT * FROM `phpbb_clusters` WHERE `id`=$cluster_id");
		
		//Build list clusters link
		$list_clusters_link="<a href='/manager.php?action=list_clusters' style='font-size:8pt;color:black;text-decoration:underline;'>Все кластеры</a>";
		
		//Build form action link
		$action_link="/manager.php?action=edit_cluster&cluster=".$cluster_id;
		
		//Build edit cluster link
		$show_cluster_link="<a href='/manager.php?action=show_cluster&cluster=".$cluster_id."' style='font-size:8pt;'>Просмотреть</a>";

		//Return HTML flow
		$html.=template_get("clusters/edit_cluster", array(	'list_clusters_link'=>$list_clusters_link,
																'action_link'=>$action_link,
																'show_cluster_link'=>$show_cluster_link,
																'name'=>$cluster['name'],
																'message'=>$message_html
																));
	}else{
		$cluster['name']=trim($_POST['name']);
		
		//Define checks flag
		$do=true;
		
		//Check for empty cluster name
		$cluster['name']=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $cluster['name'])){
			header("location: /manager.php?action=edit_cluster&cluster=".$cluster_id."&result=empty_cluster_name");
			$do=false;
		}
		
		//Check for cluster with same name
		$other_clusters_res=db_query("SELECT * FROM `phpbb_clusters` WHERE `name`='".$cluster['name']."'");
		$other_cluster=db_fetch($other_clusters_res);
		if(db_count($other_clusters_res)>0){
			if($other_cluster['id']!=$cluster_id){
				header("location: /manager.php?action=edit_cluster&cluster=".$cluster_id."&result=same_cluster_exists");
				$do=false;
			}
		}
		
		//Perform database request
		if($do){
			db_query("UPDATE `phpbb_clusters`
					  SET `name`='".$cluster['name']."'
					  WHERE `id`=".$cluster_id);
					  
			//Refer to other page		  
			header("location: /manager.php?action=edit_cluster&cluster=".$cluster_id."&result=cluster_saved_success");
		}
	}
	
	//Return HTML flow
	return $html;
}
?>