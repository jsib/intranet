<?php
function show_point(){
	switch(@$_GET['message']){
		case "pointjustadded":
			$message_html=template_get("message", array('message'=>"Офис/склад успешно добавлен"));	
		break;
		default:
		$message_html=template_get("nomessage");
	}
	$point_id=$_GET['point'];
	$point=db_easy("SELECT * FROM `phpbb_points` WHERE `id`=$point_id");	
	$branch=db_easy("SELECT * FROM `phpbb_branches` WHERE `id`={$point['branch_id']}");
	if(check_rights('edit_point')){
		$edit_point_html="<a href='/manager.php?action=edit_point&point=$point_id' style='font-size:8pt;'>Редактировать</a>";
	}
	$contactsRES = db_query("SELECT * FROM `phpbb_users`
									WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root' AND `user_email`!='olex3352@gmail.com'
											AND `point_id`=$point_id
									ORDER BY `username` ASC
									");
	$contacts_html="";
	while($contact=db_fetch($contactsRES)){
		$contacts_html.="<a href='/manager.php?action=show_contact&contact={$contact['user_id']}'>".$contact['username']."</a><br/>";
	}		
	$html.=template_get("points/show_point", array(
																'name'=>$point['name'],
																'address'=>$point['address'],
																'phone'=>$point['phone'],
																'editpoint'=>$edit_point_html,
																'message'=>$message_html,
																'branch'=>$branch['name'],
																'contacts'=>$contacts_html
												));
	return $html;
}
?>