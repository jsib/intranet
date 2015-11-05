<?php
function add_branch(){
	if(!check_rights('add_branch')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	if(!isset($_POST['name'])){
		switch(@$_GET['message']){
			case "emptybranchname":
				$message_html=template_get("errormessage", array('message'=>"Название подразделения не может быть пустым"));	
			break;
			case "samebranchexists":
				$message_html=template_get("errormessage", array('message'=>"Подразделение с таким именем уже имеется"));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
	
		$html.=template_get("branches/add_branch", array(	
																'action'=>"/manager.php?action=add_branch",
																'message'=>$message_html
													));
	}else{
		$do=true;
		//Проверка на пустое название города
		$branch['name']=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $branch['name'])){
			header("location: /manager.php?action=add_branch&message=emptybranchname");
			$do=false;
		}
		//Проверка на наличие города с таким же именем
		if(db_easy_count("SELECT * FROM `phpbb_branches` WHERE `name`='{$branch['name']}'")>0){
			header("location: /manager.php?action=add_branch&message=samebranchexists");
			$do=false;
		}
		if($do){
			db_query("INSERT INTO `phpbb_branches` SET `name`='{$branch['name']}'");
			$branch_id=db_insert_id();
			header("location: /manager.php?action=show_branch&branch=$branch_id&message=branchjustadded");
		}
	}
	return $html;
}
?>