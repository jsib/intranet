<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
  
/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

display_forums('', $config['load_moderators']);

// Set some stats, get posts count from forums data if we... hum... retrieve all forums data
$total_posts	= $config['num_posts'];
$total_topics	= $config['num_topics'];
$total_users	= $config['num_users'];

$l_total_user_s = ($total_users == 0) ? 'TOTAL_USERS_ZERO' : 'TOTAL_USERS_OTHER';
$l_total_post_s = ($total_posts == 0) ? 'TOTAL_POSTS_ZERO' : 'TOTAL_POSTS_OTHER';
$l_total_topic_s = ($total_topics == 0) ? 'TOTAL_TOPICS_ZERO' : 'TOTAL_TOPICS_OTHER';

// Grab group details for legend display
if ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
{
	$sql = 'SELECT group_id, group_name, group_colour, group_type
		FROM ' . GROUPS_TABLE . '
		WHERE group_legend = 1
		ORDER BY group_name ASC';
}

//Проверка прав доступа
if($user->data['user_type']!=3 && $user->data['user_type']!=0)
{
	header("location: /");
	exit;
}

$result = $db->sql_query($sql);

$legend = array();
while ($row = $db->sql_fetchrow($result))
{
	$colour_text = ($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . '"' : '';
	$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];

	if ($row['group_name'] == 'BOTS' || ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile')))
	{
		$legend[] = '<span' . $colour_text . '>' . $group_name . '</span>';
	}
	else
	{
		$legend[] = '<a' . $colour_text . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
	}
}
$db->sql_freeresult($result);

$legend = implode(', ', $legend);

// Generate birthday list if required ...
$birthday_list = '';
if ($config['load_birthdays'] && $config['allow_birthdays'])
{
	$now = getdate(time() + $user->timezone + $user->dst - date('Z'));
	$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday
		FROM ' . USERS_TABLE . ' u
		LEFT JOIN ' . BANLIST_TABLE . " b ON (u.user_id = b.ban_userid)
		WHERE (b.ban_id IS NULL
			OR b.ban_exclude = 1)
			AND u.user_birthday LIKE '" . $db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%'
			AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$birthday_list .= (($birthday_list != '') ? ', ' : '') . get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

		if ($age = (int) substr($row['user_birthday'], -4))
		{
			$birthday_list .= ' (' . ($now['year'] - $age) . ')';
		}
	}
	$db->sql_freeresult($result);
}
/*НАЧАЛО: Manager. Автор: Домышев Илья, Акустик Групп*/
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/templates.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/files.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/service.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/uris.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/auth.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/dates.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/text.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/special_variables.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty/Smarty.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/blocks/blocks.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/AttendanceBenefits.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/AttendanceStatistics.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/manager/UserHire.class.php");

/*Запись в статистику*/
if(@$_GET['action']!='list_stat' && @$_GET['action']!='show_stat'){
	$db->sql_query("INSERT INTO `phpbb_stat` SET `user_id`={$user->data['user_id']}, `date`='".date("Y-m-d H:i:s")."', `uri`='{$_SERVER['REQUEST_URI']}'");
}

$html="";
$prepared_uri=array();
$action=$_GET['action'];
$dir=$_SERVER['DOCUMENT_ROOT']."/actions";
$dp = opendir($dir);
while($subdir = readdir($dp)){
	if($subdir != '.' && $subdir != '..' && is_dir($dir."/".$subdir)){
		$action_file=$dir."/".$subdir."/".$action.".php";
		if(file_exists($action_file)){
			//Include action file
			require_once($action_file);
			
			//Connect to database
			db_connect();
			
			//Get HTML flow from action's function
			$html.=$action();
		}
	}
}
/*КОНЕЦ: Manager. Автор: Домышев Илья, Акустик Групп*/

// Assign index specific vars
$template->assign_vars(array(
	'HTML'	=> template_get('main', array('html'=>"<div class='manager'>".$html."</div><br/>")),
	'TOTAL_POSTS'	=> sprintf($user->lang[$l_total_post_s], $total_posts),
	'TOTAL_TOPICS'	=> sprintf($user->lang[$l_total_topic_s], $total_topics),
	'TOTAL_USERS'	=> sprintf($user->lang[$l_total_user_s], $total_users),
	'NEWEST_USER'	=> sprintf($user->lang['NEWEST_USER'], get_username_string('full', $config['newest_user_id'], $config['newest_username'], $config['newest_user_colour'])),

	'LEGEND'		=> $legend,
	'BIRTHDAY_LIST'	=> $birthday_list,

	'FORUM_IMG'				=> $user->img('forum_read', 'NO_UNREAD_POSTS'),
	'FORUM_UNREAD_IMG'			=> $user->img('forum_unread', 'UNREAD_POSTS'),
	'FORUM_LOCKED_IMG'		=> $user->img('forum_read_locked', 'NO_UNREAD_POSTS_LOCKED'),
	'FORUM_UNREAD_LOCKED_IMG'	=> $user->img('forum_unread_locked', 'UNREAD_POSTS_LOCKED'),

	'S_LOGIN_ACTION'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login'),
	'S_DISPLAY_BIRTHDAY_LIST'	=> ($config['load_birthdays']) ? true : false,

	'U_MARK_FORUMS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}index.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;mark=forums') : '',
	'U_MCP'				=> ($auth->acl_get('m_') || $auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=front', true, $user->session_id) : '')
);

/*НАЧАЛО: Manager. Замена в шаблоне. Автор: Домышев Илья, Акустик Групп*/
main_menu();
/*КОНЕЦ: Manager. Замена в шаблоне. Автор: Домышев Илья, Акустик Групп*/

// Output page
page_header($user->lang['INDEX']);
$template->set_filenames(array(
	'body' => 'manager.html')
);

page_footer();

?>