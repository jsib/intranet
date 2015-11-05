<?php
function edit_branch(){
	if(!check_rights('edit_branch')){
		//Возвращаем значение функции
		return "У вас нет соответствующих прав";
	}
	
	$branch_id=$_GET['branch'];
	if(!isset($_POST['name'])){
		switch(@$_GET['message']){
			case "branchsaved":
				$message_html=template_get("message", array('message'=>"Изменения сохранены"));
			break;
			case "emptybranchname":
				$message_html=template_get("errormessage", array('message'=>"Название подразделения не может быть пустым"));	
			break;
			case "samebranchexists":
				$message_html=template_get("errormessage", array('message'=>"Подразделение с таким именем уже имеется"));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
		
		$branch = db_easy("SELECT * FROM `phpbb_branches` WHERE `id`=$branch_id");
		$show_branch_html="<a href='/manager.php?action=show_branch&branch=$branch_id' style='font-size:8pt;'>Просмотреть</a>";
		$html.=template_get("branches/edit_branch", array(		'action'=>"/manager.php?action=edit_branch&branch=$branch_id",
																'name'=>$branch['name'],
																'showbranch'=>$show_branch_html,
																'message'=>$message_html
																));
	}else{
		$branch['name']=trim($_POST['name']);
		$do=true;
		//Проверка на пустое название города
		$branch['name']=trim($_POST['name']);
		if(!preg_match("/^.{1,70}$/", $branch['name'])){
			header("location: /manager.php?action=edit_branch&branch=$branch_id&message=emptybranchname");
			$do=false;
		}
		//Проверка на наличие города с таким же именем
		if(db_easy_count("SELECT * FROM `phpbb_branches` WHERE `name`='{$branch['name']}'")>0){
			header("location: /manager.php?action=edit_branch&branch=$branch_id&message=samebranchexists");
			$do=false;
		}
		
		if($do){
			db_query("UPDATE `phpbb_branches`
					SET `name`='{$branch['name']}'
					WHERE `id`=$branch_id");
			header("location: /manager.php?action=edit_branch&branch=$branch_id&message=branchsaved");
		}
	}
	return $html;
}
?>