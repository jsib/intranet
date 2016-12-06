<?php
function list_clusters(){
	if(isset($_GET['message'])){
		$cluster_id=trim($_GET['cluster']);
		$cluster_name=trim($_GET['name']);
		switch(@$_GET['message']){
			case "cluster_added_success":
				$message_html=template_get("message", array('message'=>"Добавлен кластер \"{$cluster_name}\""));
			break;
			case "cluster_deleted":
				$message_html=template_get("message", array('message'=>"Удален кластер \"{$cluster_name}\""));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
	}
	$result_clusters = db_query("SELECT * FROM `phpbb_clusters` WHERE `id`!=1 ORDER BY `name` ASC");
	$entities_number=db_count($result_clusters);
	$cluster_counter=0;
	$table_html="";
	if(check_rights('delete_cluster')){
		$th_html="<th class='right'></th>";
	}else{
		$th_html="";
	}
	
	//Look over clusters
	while ($cluster = db_fetch($result_clusters)){
		$cluster_counter++;
		if($cluster_counter==$entities_number){
			$bottom_class="bottom";
		}else{
			$bottom_class="";
		}
		
		//Check rights for deleting cluster
		if(check_rights('delete_cluster')){
			$right_class='';
		}else{
			$right_class='right';
		}
		
		
		$table_html.="	<tr class='$bottom_class'>
							<td><a href='/manager.php?action=show_cluster&cluster=".$cluster['id']."' style='font-size:9pt;'>".$cluster['name']."</a></td>";

		if(check_rights('delete_cluster')){
			$table_html.="	<td class='right'><a href='/manager.php?action=delete_cluster&cluster={$cluster['id']}' onclick=\"if(!confirm('Удалить ".$cluster['name']."?')) return false;\">Удалить</a><br/></td>
						</tr>";
		}
	}
	if(check_rights('add_cluster')){
		$add_entity_link="<a href='/manager.php?action=add_cluster' class='listcontacts'>Добавить кластер</a><br/><br/>";
	}
	
	//Return HTML flow
	return template_get("clusters/list_clusters", array(	'add_entity_link'=>$add_entity_link,
															'entities_number'=>$entities_number,
															'table'=>$table_html,
															'message'=>$message_html,
															'th_html'=>$th_html,
															'right_class'=>$right_class
																));
}
?>