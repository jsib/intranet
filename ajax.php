<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

//Проверка прав доступа
if($user->data['user_type']!=3 && $user->data['user_type']!=0)
{
	echo "Вы не выполнили вход на форум.";
	exit;
}

require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/templates.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/files.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/service.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/uris.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/auth.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/dates.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/AttendanceBenefits.class.php");

$html="";
$prepared_uri=array();
$action=$_GET['action'];
$dir=$_SERVER['DOCUMENT_ROOT']."/actions";
$dp = opendir($dir);
while($subdir = readdir($dp)){
	if($subdir != '.' && $subdir != '..' && is_dir($dir."/".$subdir)){
		$action_file=$dir."/".$subdir."/".$action.".php";
		if(file_exists($action_file)){
			require_once($action_file);
			db_connect();
			$html.=$action();
		}
	}
}

echo $html;
?>