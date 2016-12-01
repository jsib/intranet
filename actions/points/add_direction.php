<?php
function add_direction(){
	if(!check_rights('add_direction')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}

	if(!isset($_POST['name'])){
		switch(@$_GET['message']){
			case "emptypointname":
				$message_html=template_get("errormessage", array('message'=>"Название не может быть пустым"));	
			break;
			case "samepointexists":
				$message_html=template_get("errormessage", array('message'=>"Офис/склад с таким именем уже имеется"));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
		
		$branches_html="";
		$branchesRES=db_query("SELECT * FROM `phpbb_branches` ORDER BY `name` ASC");
		while($branch=db_fetch($branchesRES)){
			$branches_html.="<option value='{$branch['id']}' $selected_html>{$branch['name']}</option>";
		}
	
		$html.=template_get("points/add_point", array(	
																'action'=>"/manager.php?action=add_point",
																'branches'=>$branches_html,
																'message'=>$message_html
													));
	}else{
		$do=true;
		//Проверка на пустое название города
		$point['name']=trim($_POST['name']);
		$point['address']=trim($_POST['address']);
		$point['phone']=trim($_POST['phone']);
		$point['branch_id']=trim($_POST['branch']);
		if(!preg_match("/^.{1,70}$/", $point['name'])){
			header("location: /manager.php?action=add_point&message=emptypointname");
			$do=false;
		}
		//Проверка на наличие города с таким же именем
		if(db_easy_count("SELECT * FROM `phpbb_points` WHERE `name`='{$point['name']}'")>0){
			header("location: /manager.php?action=add_point&message=samepointexists");
			$do=false;
		}
		if($do){
			db_query("INSERT INTO `phpbb_points` SET
										`name`='{$point['name']}',
										`address`='{$point['address']}',
										`phone`='{$point['phone']}',
										`branch_id`={$point['branch_id']}");
			$point_id=db_insert_id();
			header("location: /manager.php?action=list_points&message=entity_added&name={$point['name']}");
		}
	}
	return $html;
}
?>