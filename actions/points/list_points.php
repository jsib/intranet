<?php
function list_points(){
	if(isset($_GET['message'])){
		$point_id=trim($_GET['point']);
		$point_name=trim($_GET['name']);
		switch(@$_GET['message']){
			case "pointadded":
				$message_html=template_get("message", array('message'=>"Добавлена точка \"{$point_name}\""));
			break;
			case "pointdeleted":
				$message_html=template_get("message", array('message'=>"Удалена точка \"{$point_name}\""));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
	}
	$result_points = db_query("SELECT * FROM `phpbb_points` WHERE `id`!=1 ORDER BY `name` ASC");
	$num_points=db_count($result_points);
	$num=0;
	$table_html="";
	if(check_rights('delete_point')){
		$th_html="	<th class='right'></th>";
	}else{
		$th_html="";
	}
	while ($point = db_fetch($result_points)){
		$num++;
		if($num==$num_points){
			$bottom_class="bottom";
		}else{
			$bottom_class="";
		}
		if(check_rights('delete_point')){$right_class='';}else{$right_class='right';}
		$table_html.="	<tr class='$bottom_class'>
							<td><a href='/manager.php?action=show_point&point={$point['id']}' style='font-size:9pt;'>".$point['name']."</a></td>
							<td>".$point['phone']."</td>
							<td class='$right_class'>".$point['address']."</td>";
		if(check_rights('delete_point')){
			$table_html.="	<td class='right'><a href='/manager.php?action=delete_point&point={$point['id']}' onclick=\"if(!confirm('Удалить?')) return false;\">Удалить</a><br/></td>
						</tr>";
		}
	}
	if(check_rights('add_point')){
		$add_point_link="<a href='/manager.php?action=add_point' class='listcontacts'>Добавить отдел</a><br/><br/>";
	}
	$html.=template_get("points/list_points", array(		'addpointlink'=>$add_point_link,
															'numpoints'=>$num_points,
															'table'=>$table_html,
															'message'=>$message_html,
															'th_html'=>$th_html,
															'right_class'=>$right_class
																));
	return $html;
}
?>