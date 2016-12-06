<?php
function delete_cluster(){
	//Check rights to perform this action
	if(!check_rights('delete_cluster')){
		return "У вас нет соответствующих прав";
	}

	//Get data from browser
	$cluster_id=(int)$_GET['cluster'];
	
	//Perform query to database
	$cluster_res=db_query("SELECT * FROM `phpbb_clusters` WHERE `id`=".$cluster_id);
	
	//Check for cluster existence
	if(db_count($cluster_res)==0){
		system_error();
	}else{
		//Get cluster from database
		$cluster=db_fetch($cluster_res);
		
		//Delete cluster from database
		db_query("DELETE FROM `phpbb_clusters` WHERE `id`=".$cluster_id);
		
		//Refer to other page
		header("location: /manager.php?action=list_clusters&result=cluster_deleted&name=".urlencode($cluster['name']));
	}
}
?>