<?php
//Функция
function list_stat(){
	if(check_rights('show_stat')){
		if(@$_GET['view']=='names'){
			return list_stat_by_names();
		}else{
			return list_stat_by_dates();
		}
	}else{
		//Возвращаем значение функции
		return "Нет соответствующих прав.<br/>";
	}
}

//Функция
function list_stat_by_names(){
	//Определяем переменные
	$userStats_html="";
	$users=array();
	$stats=array();
	$userStats=array();
	
	//Запрос к базе
	$statsRES=db_query("SELECT * FROM `phpbb_stat` WHERE `user_id`!=5871 ORDER BY `date` ASC");
	
	//Цикл
	while($statWHILE=db_fetch($statsRES)){
		$stats[$statWHILE['id']]=$statWHILE;
	}
	
	//Запрос к базе
	$usersRES=db_query("SELECT  *
								FROM `phpbb_users`
								WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root' AND `user_email`!='olex3352@gmail.com' AND `user_id`!=95
								ORDER BY `username` ASC");
	
	//Цикл
	while($userWHILE=db_fetch($usersRES)){
		$users[$userWHILE['user_id']]['name']=$userWHILE['username'];
	}
	
	//Цикл
	foreach($stats as $id=>$statFOR){
		//IF на случай если пользователя удалили
		if($users[$statFOR['user_id']]['name']!=""){
			$userStats[$users[$statFOR['user_id']]['name']]['number']++;
			$userStats[$users[$statFOR['user_id']]['name']]['id']=$statFOR['user_id'];
		}
	}

	//Сортировка массива
	arsort($userStats);
	
	//Цикл
	foreach($userStats as $username=>$userStat){
		$userStats_html.="<a href='/manager.php?action=show_stat&user=".$userStat['id']."'>".$username."</a>: ".$userStat['number']."<br/>";
	}
	
	//Возвращаем значение функции
	return	$html.=template_get("stat/list_stat", array(
													'userStats'=>$userStats_html
												));
}

//Функция
function list_stat_by_dates(){
	//Определяем переменные
	$stat_html="";
	$users=array();
	$stats=array();
	$userStats=array();
	
	//Запрос к базе
	$usersRES=db_query("SELECT  *
								FROM `phpbb_users`
								WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root' AND `user_email`!='olex3352@gmail.com' AND `user_id`!=95 AND `user_id`!=5871
								ORDER BY `username` ASC");
	
	//Цикл
	while($userWHILE=db_fetch($usersRES)){
		$users[$userWHILE['user_id']]['name']=$userWHILE['username'];
	}
	
	//Запрос к базе
	$statsRES=db_query("SELECT * FROM `phpbb_stat` ORDER BY `date` DESC");
	
	//Цикл
	while($statWHILE=db_fetch($statsRES)){
		if($statWHILE['user_id']!=5871){
			$date_stats[date("d/m/Y", strtotime($statWHILE['date']))][]=array('uri'=>$statWHILE['uri'], 'time'=>date("H:i", strtotime($statWHILE['date'])), 'user_id'=>$statWHILE['user_id']);
		}
	}
	
	//Цикл
	foreach($date_stats as $date=>$stat){
		$stat_html.="<a href='manager.php?action=show_stat&date=$date'>".$date."</a>: ".count($stat)."<br/>";
	}

	//Возвращаем значение функции
	return	$html.=template_get("stat/list_stat", array(
													'userStats'=>$stat_html
												));
}
?>