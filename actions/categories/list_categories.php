<?php
function list_categories(){
	if(isset($_GET['message'])){
		$category_id=trim($_GET['category']);
		$category_name=trim($_GET['name']);
		switch(@$_GET['message']){
			case "category_added_success":
				$message_html=template_get("message", array('message'=>"Добавлена категория \"{$category_name}\""));
			break;
			case "category_deleted":
				$message_html=template_get("message", array('message'=>"Удалена категория \"{$category_name}\""));	
			break;
			default:
			$message_html=template_get("nomessage");
		}
	}
	$result_categories = db_query("SELECT * FROM `phpbb_categories` WHERE `id`!=1 ORDER BY `name` ASC");
	$entities_number=db_count($result_categories);
	$category_counter=0;
	$table_html="";
	if(check_rights('delete_category')){
		$th_html="<th class='right'></th>";
	}else{
		$th_html="";
	}
	
	//Look over categories
	while ($category = db_fetch($result_categories)){
		$category_counter++;
		if($category_counter==$entities_number){
			$bottom_class="bottom";
		}else{
			$bottom_class="";
		}
		
		//Check rights for deleting category
		if(check_rights('delete_category')){
			$right_class='';
		}else{
			$right_class='right';
		}
		
		
		$table_html.="	<tr class='$bottom_class'>
							<td><a href='/manager.php?action=show_category&category=".$category['id']."' style='font-size:9pt;'>".$category['name']."</a></td>";

		if(check_rights('delete_category')){
			$table_html.="	<td class='right'><a href='/manager.php?action=delete_category&category={$category['id']}' onclick=\"if(!confirm('Удалить ".$category['name']."?')) return false;\">Удалить</a><br/></td>
						</tr>";
		}
	}
	if(check_rights('add_category')){
		$add_entity_link="<a href='/manager.php?action=add_category' class='listcontacts'>Добавить категорию</a><br/><br/>";
	}
	
	//Return HTML flow
	return template_get("categories/list_categories", array(		'add_entity_link'=>$add_entity_link,
															'entities_number'=>$entities_number,
															'table'=>$table_html,
															'message'=>$message_html,
															'th_html'=>$th_html,
															'right_class'=>$right_class
																));
}
?>