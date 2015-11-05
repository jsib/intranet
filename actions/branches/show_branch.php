<?php
function show_branch(){
	switch(@$_GET['message']){
		case "branchjustadded":
			$message_html=template_get("message", array('message'=>"Город успешно добавлен"));	
		break;
		default:
		$message_html=template_get("nomessage");
	}
	$branch_id=$_GET['branch'];
	$branch=db_easy("SELECT * FROM `phpbb_branches` WHERE `id`=$branch_id");
	$pointsRES=db_query("SELECT * FROM `phpbb_points` WHERE `branch_id`=$branch_id");
	if(db_count($pointsRES)>0){
		while($point=db_fetch($pointsRES)){
			$points_html.="<div style='padding-bottom:5px;'><a href='/manager.php?action=show_point&point={$point['id']}'>{$point['name']}</a></div>";
		}
	}else{
		$points_html="-";
	}
	
	if(check_rights('add_branch')){
		$edit_branch_html="<a href='/manager.php?action=edit_branch&branch=$branch_id' style='font-size:8pt;'>Редактировать</a>";
	}
	$html.=template_get("branches/show_branch", array(
																'name'=>$branch['name'],
																'editbranch'=>$edit_branch_html,
																'message'=>$message_html,
																'points'=>$points_html
												));
	return $html;
}
?>