<?php
function list_rightgroups(){
	//Define variables
	$groups_html="";
	
	//Perform request to database
	$groups_res=db_query('SELECT * FROM `phpbb_rightgroups` ORDER BY `name` ASC');  
	
	//Build group list
	if(db_count($groups_res)>0){
		while($group=db_fetch($groups_res)){
			$rights_number_while=db_easy_count('SELECT `id` FROM `phpbb_rights` WHERE `group_id`='.$group['id']);
			$groups_html.='<a href="/manager.php?action=list_rights&group='.$group['id'].'">'.$group['name'].' ('.$rights_number_while.')</a><br/><br/>';
							
		}
	//No groups presented
	}else{
		$groups_html.="<br/>Список групп прав доступа пуст.";
	}
	
	//Return HTML flow
	return template_get("rights/list_rightgroups", array(
													'groups_html'=>$groups_html,
												));
}
?>