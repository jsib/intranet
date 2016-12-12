<?php
function list_rights(){
	//Define variables
	$rights_html="";
	
	//Retrieve right group id from browser
	if(isset($_GET['group'])){
		$right_group_id=(int)$_GET['group'];
	}else{
		system_error('Right group id doesn\'t defined');
	}
	
	//Retrieve header of right group from database
	if($right_group_id>0){
		$right_group_name_rus=db_short_easy('SELECT `name` FROM `phpbb_rightgroups` WHERE `id`='.$right_group_id);
	}elseif($right_group_id==0){
		$right_group_name_rus="Без группы";
	}
	
	//Perform request to database
	$rights_res=db_query('SELECT * FROM `phpbb_rights` WHERE `group_id`='.$right_group_id.' ORDER BY `name` ASC');  
	
	//Show rights list
	if(db_count($rights_res)>0){
		$i=0;
		//WHILE
		while($right=db_fetch($rights_res)){
			if(trim($right['description'])!=""){
				$right_description_html='<div class="comment">('.$right['description'].')</div>';
			}else{
				$right_description_html='<br/><br/>';
			}
				
			$rights_html.='<h4>'.$right['name'].'</h4><a href="/manager.php?action=add_right&right='.$right['id'].'"><img src="/images/add.png" /></a>'.$right_description_html;
							
			$rights_html.=build_right_html($right['id'], $right['name']);
			$rights_html.="<br/><br/>";
			if(db_count($rights_res)!=$i+1) $rights_html.="<hr/><br/>";
			$i++;
		}
	//No rights presented
	}else{
		$rights_html.="<br/>Список прав пуст.";
	}
	
	//Build list entities link
	$list_groups_link='<a href="/manager.php?action=list_rightgroups" class="list_entities">Список групп</a>';
	
	//Return HTML flow
	return template_get("rights/list_rights", array(
													'list_groups_link'=>$list_groups_link,
													'rights_html'=>$rights_html,
													'group_name_rus'=>$right_group_name_rus
												));
}

//Build HTML of certain right
function build_right_html($right_id, $right_name){
	//Определяем переменные
	$html="";
	
	//Запрос к базе
	$rights_res=db_query("SELECT `phpbb_users`.`username`, `phpbb_rights_users`.`user_id`, `phpbb_rights_users`.`right_id`
							FROM `phpbb_rights_users`, `phpbb_users`
								WHERE `phpbb_rights_users`.`right_id`=$right_id
									AND `phpbb_rights_users`.`user_id`=`phpbb_users`.`user_id`
										ORDER BY `phpbb_users`.`username` ASC");
										
	//IF
	if(db_count($rights_res)>0){
		//WHILE
		while($user=db_fetch($rights_res)){
			$html.=$user['username'].
					"<a href='/manager.php?action=delete_right&user={$user['user_id']}&right={$user['right_id']}'><img src='/images/delete.png' style='padding-left:20px;' /></a><br/>";
		}
	//ELSE	
	}else{
		$html.="Ни один пользователь не имеет права $right_name";
	}
	
	//Возвращаем значение функции
	return $html;
}
?>