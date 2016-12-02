<?php
function edit_point(){
	if(!check_rights('edit_point')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}

	$point_id=$_GET['point'];
	if(!isset($_POST['name'])){
		switch(@$_GET['message']){
			case "pointsaved":
				$message_html=template_get("message", array('message'=>"Изменения сохранены"));
			break;
			case "emptypointname":
				$message_html=template_get("errormessage", array('message'=>"Название не может быть пустым"));	
			break;
			case "samepointexists":
				$message_html=template_get("errormessage", array('message'=>"Офис/склад с таким именем уже имеется"));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
		
		$point = db_easy("SELECT * FROM `phpbb_points` WHERE `id`=$point_id");
		$show_point_html="<a href='/manager.php?action=show_point&point=$point_id&' style='font-size:8pt;'>Просмотреть</a>";
		/*Строим список SELECT*/
		$branches_html="";$selected_html="";
		$branchesRES=db_query("SELECT * FROM `phpbb_branches` ORDER BY `name` ASC");
		while($branch=db_fetch($branchesRES)){
			if($point['branch_id']==$branch['id']){
				$selected_html="selected";
			}else{
				$selected_html="";
			}
			$branches_html.="<option value='{$branch['id']}' $selected_html>{$branch['name']}</option>";
		}
		
		$html.=template_get("points/edit_point", array(		'action'=>"/manager.php?action=edit_point&point=$point_id",
																'name'=>$point['name'],
																'address'=>$point['address'],
																'phone'=>$point['phone'],
																'branches'=>$branches_html,
																'showpoint'=>$show_point_html,
																'message'=>$message_html
																));
	}else{
		$point['name']=trim($_POST['name']);
		$point['address']=trim($_POST['address']);
		$point['phone']=trim($_POST['phone']);
		$point['branch_id']=trim($_POST['branch']);
		$do=true;
		//Проверка на пустое название города
		$point['name']=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $point['name'])){
			header("location: /manager.php?action=edit_point&point=$point_id&message=emptypointname");
			$do=false;
		}
		//Проверка на наличие города с таким же именем
		$other_pointRES=db_query("SELECT * FROM `phpbb_points` WHERE `name`='{$point['name']}'");
		$other_point=db_fetch($other_pointRES);
		if(db_count($other_pointRES)>0){
			if($other_point['id']!=$point_id){
				header("location: /manager.php?action=edit_point&point=$point_id&message=samepointexists");
				$do=false;
			}
		}
		if($do){
			db_query("UPDATE `phpbb_points`
					SET `name`='{$point['name']}',
						`branch_id`={$point['branch_id']},
						`address`='{$point['address']}',
						`phone`='{$point['phone']}'
					WHERE `id`=$point_id");
			header("location: /manager.php?action=edit_point&point=$point_id&message=pointsaved");
		}
	}
	return $html;
}
?>