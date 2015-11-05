<?php
//Функция
function show_stat(){
	if(check_rights('show_stat')){
		if(isset($_GET['user']) && !isset($_GET['date'])){
			return show_stat_only_user();
		}

		if(isset($_GET['user']) && isset($_GET['date'])){
			return show_stat_user_and_date();
		}

		
		if(isset($_GET['date']) && !isset($_GET['user'])){
			return show_stat_only_date();
		}
	}else{
		//Возвращаем значение функции
		return "Нет соответствующих прав.<br/>";
	}
}

//Функция
function show_stat_user_and_date(){
	//Определяем переменные
	$stat_html="";
	$user_id=$_GET['user'];
	$date=$_GET['date'];
	
	//Запрос к базе
	$username=db_short_easy("SELECT `username` FROM `phpbb_users` WHERE `user_id`=$user_id LIMIT 1");
	
	//Определяем переменные
	$stat_html.="<span style='font-weight:bold;text-decoration:underline;'>Тип</span>: по сотруднику и дате<br/>";
	$stat_html.="<span style='font-weight:bold;text-decoration:underline;'>Имя сотрудника</span>: $username<br/>";
	$stat_html.="<span style='font-weight:bold;text-decoration:underline;'>Дата</span>: $date<br/><br/>";

	//Определяем переменные
	$date_stats=array();
	
	//Запрос к базе
	$statsRES=db_query("SELECT * FROM `phpbb_stat` WHERE `user_id`=$user_id ORDER BY `date` DESC");
	
	//Цикл
	while($statWHILE=db_fetch($statsRES)){
		$date_stats[date("d/m/Y", strtotime($statWHILE['date']))][]=array('uri'=>$statWHILE['uri'], 'time'=>date("H:i", strtotime($statWHILE['date'])));
	}
	
	//Цикл
	foreach($date_stats as $dateFOR=>$statFOR){
		if($dateFOR==$date){
			foreach($statFOR as $stat_day){
				$stat_html.=$stat_day['time'].": <a href='".$stat_day['uri']."'>".$stat_day['uri']."</a><br/>";
			}
		}
	}
	
	//Возвращаем значение функции
	return	$html.=template_get("stat/show_stat", array(
													'username'=>$username,
													'userStats'=>$stat_html
												));
}

//Функция
function show_stat_only_user(){
	//Определяем переменные
	$user_id=$_GET['user'];
	$stat_html="";
	
	//Запрос к базе
	$username=db_short_easy("SELECT `username` FROM `phpbb_users` WHERE `user_id`=$user_id LIMIT 1");
	
	//Определяем переменные
	$stat_html.="<span style='font-weight:bold;text-decoration:underline;'>Тип</span>: по сотруднику<br/>";
	$stat_html.="<span style='font-weight:bold;text-decoration:underline;'>Имя сотрудника</span>: $username<br/><br/>";

	//Определяем переменные
	$date_stats=array();
	
	//Запрос к базе
	$statsRES=db_query("SELECT * FROM `phpbb_stat` WHERE `user_id`=$user_id ORDER BY `date` DESC");
	
	//Определяем переменные
	$number=db_count($statsRES);

	//Цикл
	while($statWHILE=db_fetch($statsRES)){
		$date_stats[date("d/m/Y", strtotime($statWHILE['date']))][]=array('uri'=>$statWHILE['uri'], 'time'=>date("H:i", strtotime($statWHILE['date'])));
	}
	
	//Определяем переменные
	//$stat_html.="Итого: $number<br/><br/>";
	
	//Цикл
	foreach($date_stats as $date=>$statFOR){
		$stat_html.="<a href='/manager.php?action=show_stat&date=$date'>".$date."</a>: "."<a href='/manager.php?action=show_stat&user=$user_id&date=$date'>".count($statFOR)." страниц</a><br/>";
	}	

	//Возвращаем значение функции
	return	$html.=template_get("stat/show_stat", array(
													'username'=>$username,
													'userStats'=>$stat_html
												));
}

//Функция
function show_stat_only_date(){
	//Определяем переменные
	$date=$_GET['date'];
	$stat_html="";

	//Определяем переменные
	$stats=array();
	$date_stats=array();

	//Определяем переменные
	$stat_html.="<span style='font-weight:bold;text-decoration:underline;'>Тип</span>: по дате<br/>";
	$stat_html.="<span style='font-weight:bold;text-decoration:underline;'>Дата</span>: $date<br/><br/>";

	//Запрос к базе
	$usersRES=db_query("SELECT  *
								FROM `phpbb_users`
								WHERE (`user_type`=0 OR `user_type`=3) AND `username`!='root' AND `user_email`!='olex3352@gmail.com' AND `user_id`!=95 AND `user_id`!=5871
								ORDER BY `username` ASC");
	
	//Цикл
	while($userWHILE=db_fetch($usersRES)){
		$users[$userWHILE['user_id']]=$userWHILE['username'];
	}
	

	//Запрос к базе
	$statsRES=db_query("SELECT * FROM `phpbb_stat` WHERE  `user_id`!=5871 ORDER BY `date` DESC");
	
	//Определяем переменные
	$number=db_count($statsRES);

	//Цикл
	while($statWHILE=db_fetch($statsRES)){
		$stats[date("d/m/Y", strtotime($statWHILE['date']))][$statWHILE['user_id']]++;
	}
	
	//Определяем переменные
	$date_stats=$stats[$date];

	//show($date_stats);
	
	//Цикл
	foreach($date_stats as $user_idFOR=>$countFOR){
		$stat_html.="<a href='/manager.php?action=show_stat&user=$user_idFOR'>".$users[$user_idFOR]."</a>: <a href='/manager.php?action=show_stat&user=$user_idFOR&date=$date'>".$countFOR." страниц</a><br/>";
	}
	
	//Возвращаем значение функции
	return	$html.=template_get("stat/show_stat", array(
													'userStats'=>$stat_html
												));
}
?>