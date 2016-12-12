<?php
function show_cluster(){
	//Check rights for this action
	if(!check_rights('show_cluster')){
		system_error('No permissions for '.__FUNCTION__.' action', ERR_NO_PERMISSION);
	}
	
	//Get cluster id
	$cluster_id=(int)$_GET['cluster'];
	
	//Retrieve cluster from database
	$cluster=db_easy("SELECT * FROM `phpbb_clusters` WHERE `id`=".$cluster_id);
	
	//Check rights to perform this actions
	if(check_rights('edit_cluster')){
		//Build edit cluster link
		$edit_cluster_link="<a href='/manager.php?action=edit_cluster&cluster=".$cluster_id."' style='font-size:8pt;'>Редактировать</a>";
	}
	
	//Build list clusters link
	$list_clusters_link="<a href='/manager.php?action=list_clusters' style='font-size:8pt;color:black;text-decoration:underline;'>Все кластеры</a>";
	
	$html.=template_get("clusters/show_cluster", array(
																'name'=>$cluster['name'],
																'list_clusters_link'=>$list_clusters_link,
																'edit_cluster_link'=>$edit_cluster_link,
																'message'=>$message_html,
												));
	return $html;
}
?>