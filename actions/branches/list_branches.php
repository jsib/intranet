<?php
function list_branches(){
	$result_branches = db_query("SELECT * FROM `phpbb_branches` WHERE `id`!=1 ORDER BY `name`");
	$num_branches=db_count($result_branches);
	$num=0;
	$table_html="";
	while ($branch = db_fetch($result_branches)){
		$num++;
		if($num==$num_branches){
			$bottom_class="bottom";
		}else{
			$bottom_class="";
		}
		$table_html.="	<tr class='$bottom_class'>
							<td><a href='/manager.php?action=show_branch&branch=".$branch['id']."' style='font-size:9pt;'>".$branch['name']."</a></td>
							<td><a href='/manager.php?action=edit_branch&branch={$branch['id']}'>Редактировать</a></td>
							<td class='right'><a href='/manager.php?action=delete_branch&branch={$branch['id']}' onclick=\"if(!confirm('Удалить?')) return false;\">Удалить</a><br/></td>
						</tr>";
	}
	$add_branch_link="manager.php?action=add_branch";
	$html.=template_get("branches/list_branches", array(
															'addbranchlink'=>$add_branch_link,
															'numbranches'=>$num_branches,
															'table'=>$table_html,
																));
	return $html;
}
?>