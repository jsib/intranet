<?php
function add_right(){
	//Определяем переменные
	$html="";
	$users_html="";
	
	/*Проверка прав на выполнение действия*/
	if(!check_rights('add_right')){
		return "У вас нет соответствующих прав";
	}
	
	//There is some input data from browser
	if(!isset($_POST['user'])){
		//Get right id from browser
		$right_id=(int)$_GET['right'];
		
		//Check data from browser
		$right_res=db_query('SELECT `name` FROM `phpbb_rights` WHERE `id`='.$right_id);
		if(db_count($right_res)==0){
			system_error('No right with id '.$right_id.' in database');
		//Get right name from database
		}else{
			$right_name=db_fetch($right_res)['name'];
		}
		
		//Request to database
		$users_res=db_query("SELECT * FROM `phpbb_users`
								WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root'
													ORDER BY `username` ASC");
		
		//WHILE
		while($user_while=db_fetch($users_res)){
			if(db_easy_count('SELECT * FROM `phpbb_rights_users`
								WHERE `user_id`='.$user_while['user_id'].'
									AND `right_id`='.$right_id
						)==0){
				$users_html.='<option value="'.$user_while['user_id'].'">'.$user_while['username'].'</option>';
			}
		}
		
		//Return HTML flow
		return template_get("rights/add_right", array(
																'users'=>$users_html,
																'right_id'=>$right_id,
																'right_name'=>$right_name
													));
	//No input data from browser
	}else{
		//Retrieve data from browser
		$user_id=(int)$_POST['user'];
		$right_id=(int)$_POST['right'];

		/*Проверка входных данных*/
		if(db_easy_count('SELECT * FROM `phpbb_users` WHERE `user_id`='.$user_id)==0){
			system_error('User with id '.$user_id.' doesn\'t exist');
		}
		
		//Check if right with this id exist in database
		$rights_res=db_query('SELECT * FROM `phpbb_rights` WHERE `id`='.$right_id);
		if(db_count($rights_res)>0){
			$right=db_fetch($rights_res);
		}else{
			system_error('Right with id '.$right_id.' doesn\'t exist');
		}
		
		//Check if this right already given to this user
		if(db_easy_count('SELECT * FROM `phpbb_rights_users` WHERE `user_id`='.$user_id.' AND `right_id`='.$right_id)!=0){
			system_error('User with id '.$user_id.' already has right with id '.$right_id);
		}else{
			//Request to database
			$insert_res=db_query('INSERT INTO `phpbb_rights_users` SET `user_id`='.$user_id.', `right_id`='.$right_id);
			
			//Refer to other page
			if(db_result($insert_res)){
				header('location: /manager.php?action=list_rights&group='.$right['group_id']);
			//Process error
			}else{
				system_error('Error when trying to give user with id '.$user_id.' right with id '.$right_id);	
			}
		}
	}
}
?>