<?php
function list_users(){
	//Retrieve information from this function name
	$function_name_pieces=explode("_", __FUNCTION__);

	//Refer to global variables
	global $table_prefix;
	global $system_objects;

	//Retrieve object properties
	$object_plural_eng=$function_name_pieces[1];
	$object_singular_eng=$system_objects[$object_plural_eng]['singular_name_eng'];
	$object_plural_rus=$system_objects[$object_singular_eng]['plural_name_rus'];
	$object_actions=$system_objects[$object_singular_eng]['actions'];

	//Retrieve action properties
	$action_eng=$function_name_pieces[0];
	$action_full_eng=__FUNCTION__;

	//Retrieve parent object properties
	$parent_object_singular_eng=$system_objects[$object_singular_eng]['parent'];
	$parent_object_plural_eng=$system_objects[$parent_object_singular_eng]['plural_name_eng'];
	
	//Get sort direction
	if(isset($_GET['sort_direction'])){
		$sort_direction=$_GET['sort_direction'];
	}else{
		$sort_direction="asc";
	}
	
	//Get links for sorting columns
	if($sort_direction=="asc") $link_sort_direction="desc";  
	if($sort_direction=="desc") $link_sort_direction="asc";
	
	//Get column for sorting
	if(isset($_GET['sort'])){
		$sort=$_GET['sort'];
	}else{
		$sort="entity_name";
	}
	
	//Setting table sorting
	$headers=array( 'entity_name'=>array('rus'=>"ФИО", 'sort_column'=>"username"),
					'parent_entity_name'=>array('rus'=>'Дирекция', 'sort_column'=>"`".$table_prefix.$parent_object_plural_eng."`.`name`"
				   ));
				   
	//Build sorting links
	foreach($headers as $name=>$value){
		if($sort==$name){
			$headers[$name]['html']="<a href='".uri_make(array('sort_direction'=>$link_sort_direction, 'sort'=>$name))."' class='header'>".
									$headers[$name]['rus']."<img src='/images/$sort_direction.png' class='header'></a>";
		}else{
			$headers[$name]['html']="<a href='".uri_make(array('sort_direction'=>'asc', 'sort'=>$name))."' class='header'>".$headers[$name]['rus']."</a>";
		}
	}
	
	//Build SQL expression for parent object filter
	$parent_entities_db = db_query("SELECT * FROM `".$table_prefix.$parent_object_plural_eng."` ORDER BY 'name'");

	if(isset($_GET['parent_entity_id']) && @$_GET['parent_entity_id']!=1){
		$parent_entity_id=$_GET['parent_entity_id'];
		$parent_object_where=" AND `".$table_prefix.$parent_object_plural_eng."`.`id`=$parent_entity_id ";
	}else{
		$parent_object_where="";
		$parent_entity_id=0;
	}
	
	//Define HTML flow for list of parent entities
	$parent_entities_html="";
	while($parent_entityWHILE=db_fetch($parent_entities_db)){
		if($parent_entity_id==$parent_entityWHILE['id']){
			$selected="selected";
		}else{
			$selected="";
		}
		
		if($parent_entityWHILE['id']!=1){
			$parent_entities_html.="<option value='".$parent_entityWHILE['id']."' $selected>".$parent_entityWHILE['name']."</option>";
		}else{
			$parent_entities_html.="<option value='1' $selected>".$system_objects[$parent_object_singular_eng]['phrases']['all_entities_text']."</option>";
		}
	}
	
	//Form 'checked' flag for hidden entities
	if(check_rights('show_hidden_entities')){
		if(@$_GET['show_hidden_entities']=='on'){
			$sql_hidden_entities="OR `user_type`=9";
			$hidden_entity_checked='checked';
		}else{
			$sql_hidden_entities="";
		    $hidden_entity_checked='';
		}
	}
	
	//Build hidden input for value of parent_entity_id. Need when you change select of parent_entities.
	if(isset($_GET['parent_entity_id'])){
		$parent_entity_id=$_GET['parent_entity_id'];
		$input_hidden_entity="<input type='hidden' name='parent_entity_id' value='".$parent_entity_id."' />";
	}else{
		$input_hidden_entity="";
	}
	
	//Build HTML for hidden entities checkbox
	if(check_rights('show_hidden_'.$object_plural_eng)){
		$checkbox_hidden_entities=template_get($object_plural_eng."/checkbox_hidden_entities",
												array('input_hidden_entity'=>$input_hidden_entity,
													  'hidden_entity_checked'=>$hidden_entity_checked,
													  'action_full_eng'=>$action_full_eng
												));
	}
	
	$entities_sql="SELECT *, `".$table_prefix.$parent_object_plural_eng."`.`name` as `parent_entity_name`,
										  `".$table_prefix.$parent_object_plural_eng."`.`id` as `parent_entity_id`
									FROM  `".$table_prefix.$object_plural_eng."`, `".$table_prefix.$parent_object_plural_eng."`
									WHERE (`user_type` IN (0,3) $sql_hidden_entities) AND `username`!='root'
											AND `".$table_prefix.$parent_object_plural_eng."`.`id`=`".$table_prefix.$object_plural_eng."`.".$parent_object_singular_eng."_id
											$parent_object_where
									ORDER BY {$headers[$sort]['sort_column']} ".$sort_direction;
	$entities_db = db_query($entities_sql);
	
	//echo $entities_sql;
	
	//Get number of entities retrieved from database
	$entities_number=db_count($entities_db);
	
	//Define entities counter
	$entities_counter=0;

	//Add addition column for first row of table
	if(check_rights('delete_'.$object_singular_eng)){
		$th_html="		
						<th class='right'></th>";
	}else{
		$th_html="";
	}
	
	//Get class for column before 'delete action' column
	if(check_rights('delete_'.$object_singular_eng)){
		$right_class='';
	}else{
		$right_class='right';
	}
	
	//Define HTML flow for table
	$table_html="";
		
	//Form table HTML
	if($entities_number>0){
		//First row
		$table_html.="	<tr>
							<th class='left'>".$headers['entity_name']['html']."</th>
							<th>".$headers['parent_entity_name']['html']."</th>
							<th>Должность</th>
							<th>Мобильный</th>
							<th class='".$right_class."'>Добавочный</th>".$th_html."</tr>";			

		//Rows after first
		while ($entity_while = db_fetch($entities_db)){
			//Increase entity counter
			$entities_counter++;
					
			if($entities_counter==$entities_number){
				$bottom_class="bottom";
			}else{
				$bottom_class="";
			}
			
			if(trim($entity_while['phone_ext'])!=""){
				$phone_ext=$entity_while['phone_ext'];
				if(trim($entity_while['phone_ext'])!=""){
					$phone_ext.=", доб. ".$entity_while['phone_ext'];
				}
			}else{
				$phone_ext="-";
			}
			
			if(trim($entity_while['phone_mobile'])!=""){
				$phone_mobile=$entity_while['phone_mobile']." (рабочий)";
			}elseif(trim($entity_while['phone_mobile'])!=""){
				$phone_mobile=$entity_while['phone_mobile']." (личный)";
			}else{
				$phone_mobile="-";
			}
			
			if(trim($entity_while['user_occ'])==""){
				$entity_while['user_occ']="-";
			}
			
			$entity_while['user_type']==9 ? $style_hidden_entity='color:grey' : $style_hidden_entity='';
			
			$parent_entity_link=uri_make(array('action'=>'show_'.$parent_object_singular_eng, 'entity_id'=>$entity_while[$parent_object_singular_eng."_id"]));
			
			$table_html.="	<tr class='$bottom_class'>
								<td class='left'>".
								"<a href='/manager.php?action=show_".$object_singular_eng."&entity_id=".$entity_while['user_id']."' style='font-size:9pt;$style_hidden_entity'>".
								$entity_while['username']."</a></td>
								<td><a href='".$parent_entity_link."' style='font-size:9pt;'>".$entity_while['parent_entity_name']."</a></td>
								<td style='width:250px;'>".$entity_while['user_occ']."</td>
								<td style='width:250px;'>".$phone_mobile."</td>
								<td  class='$right_class'>".$phone_ext."</td>";
			
			//'Delete' column
			if(check_rights('delete_'.$object_singular_eng)){
				$table_html.=
					"	<td class='right'>".
					"<a href='/manager.php?action=delete_".$object_singular_eng."&entity_id={$entity_while['user_id']}' onclick=\"if(!confirm('".
					html_replace($system_objects[$parent_object_singular_eng]['phrases']['confirm_delete_message'], array('entity_name'=>$entity_while['username']))
					."')) return false;\">".
					"Удалить</a><br/></td></tr>";
			}
		}
	}else{
		$table_html.=$system_objects[$parent_object_singular_eng]['phrases']['no_child_message'];
	}
	
	//Form add entity link
	if(check_rights('add_'.$object_singular_eng)){
		$add_entity_link="<a href='/manager.php?action=add_".$object_singular_eng."' class='list_entities'>".$system_objects[$object_singular_eng]['actions']['add']['full_name_rus']."</a><br/><br/>";
	}else{
		$add_entity_link="";
	}

	return template_get($object_plural_eng."/list_".$object_plural_eng, array(
															'add_entity_link'=>$add_entity_link,
															'entities_number'=>$entities_number,
															'table'=>$table_html,
															'parent_entities'=>$parent_entities_html,
															'checkbox_hidden_entities'=>$checkbox_hidden_entities,
															'list_entities_link'=>'list_'.$object_plural_eng,
															'page_header'=>mb_convert_case($object_plural_rus, MB_CASE_TITLE)
														));
}
?>