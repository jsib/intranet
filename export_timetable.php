<?php
//Экспорт данных об использовании рабочего времени в csv файл
require_once("/home/intranet.acoustic-group.net/www/includes/manager/files.php");
require_once("/home/intranet.acoustic-group.net/www/includes/manager/db.php");
require_once("/home/intranet.acoustic-group.net/www/includes/manager/service.php");
require_once("/home/intranet.acoustic-group.net/www/config.php");

show("Экспорт данных об использовании рабочего времени");

//Подключаемся к БД
db_connect();

//Содержимое CSV файла
$data="";

//Счетчик строк
$counter=0;

//Готовим массив с именами пользователей
$usersRES=db_query("SELECT * FROM `phpbb_users`");

$users=array();

while($userWHILE=db_fetch($usersRES)){
	$user_id=$userWHILE['user_id'];
	$users[$user_id]=$userWHILE['username'];
}

//Записываем информацию из таблицы timetable в CSV файл
$strRES=db_query("SELECT * FROM `phpbb_timetable`");
//$previous_last_timetable_id=db_short_easy("SELECT `last_timetable_id` FROM `phpbb_export` WHERE `id`=1");
$last_timetable_id=0;
$previous_last_timetable_id=7890;
while($strWHILE=db_fetch($strRES)){
	//if($strWHILE['id']>$previous_last_timetable_id){
		$user_id=$strWHILE['user_id'];
		
		//Условие на случай, если пользователь уже был удален, а информация в timetable о нем сохранилась
		if(isset($users[$user_id])){
			$data.=$strWHILE['id'].";".$strWHILE['year'].";".$strWHILE['month'].";".$strWHILE['day'].";".$users[$user_id].";".$strWHILE['status'].";".$strWHILE['hours']."\n";
			$counter++;
			//$last_timetable_id=$strWHILE['id'];
		}
	//}
}

//Записываем информацию о выгрузке в БД
if($counter>0){
//	db_query("UPDATE `phpbb_export` SET `last_timetable_id`=$last_timetable_id, `time`='".date("Y-m-d H:i:s")."' WHERE `id`=1");
	$last_timetable_id_info="Последний id записи в этом файле:$last_timetable_id";
}else{
	$last_timetable_id_info="С момента последнего экспорта записи отсутствуют";
}

//Заголовок
$data="id записи;год;месяц;день;имя пользователя;статус;кол-во часов\n".$data;
$data="Дата/время:".date("Y-m-d H:i:s").";Последний id записи в предыдущем файле:$previous_last_timetable_id;$last_timetable_id_info\n".$data;

//Выводим сведения о проделанной работе
//if(file_easy_write("/mnt/SRV1C2_Export/FromIntranet/timetable.csv", $data)){
if(file_easy_write("/home/intranet.acoustic-group.net/www/timetable.csv", $data)){

        show("Файл успешно создан");
        show("Выгружено ".$counter." строк");
}else{
        show("Возникли ошибки при создании файла");
}

if(file_easy_write("/mnt/SRV1C2_Export/FromIntranet/timetable_new.csv", $data)){
	show("Файл успешно создан");
	show("Выгружено ".$counter." строк");
}else{
	show("Возникли ошибки при создании файла");
}


?>
