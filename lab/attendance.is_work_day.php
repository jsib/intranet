<?php
require_once($_SERVER['DOCUMENT_ROOT']."/config.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/Attendance.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/service.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/errors.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/db.php");

db_connect();

$attendancy = new Attendance();

$user_id = 5878;
$date = '14.02.2017';
$status = 6;

echo( 'Проверка, задан ли указанный статус для данного дня.<br/>' );
echo( 'Id пользователя: ' . $user_id . '<br/>' );
echo( 'Дата: ' . $date . '<br/>' );
echo( 'Статус: ' . $status . '<br/>' );
echo( 'Ответ: ' );
show( $attendancy->check_status($user_id, $date, $status) );

echo('<br/><br/>');

echo( 'Проверка, является ли день рабочим.<br/>' );
echo( 'Id пользователя: ' . $user_id . '<br/>' );
echo( 'Дата: ' . $date . '<br/>' );
echo( 'Ответ: ' );
show( $attendancy->is_work_day($user_id, $date) );
?>