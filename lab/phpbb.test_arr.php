<?php
get('_acl_options');

function get($var_name)
{
	if ($var_name{0} == '_')
	{
		echo('yes');
	}
	else
	{
		echo('no');
	}
}
?>