<?php
function list_arendas(){
	//Bind global variables
	global $config_arenda;
	
	//Get sort direction
	if(isset($_GET['sortdirection'])){
		$sort_direction=$_GET['sortdirection'];
	}else{
		$sort_direction="asc";
	}
	if($sort_direction=="asc") $link_sort_direction="desc";
	if($sort_direction=="desc") $link_sort_direction="asc";
	
	//Get column for sorting
	if(isset($_GET['sort'])){
		$sort=$_GET['sort'];
	}else{
		$sort="name";
	}
	
	//Get headers of columns for sorting
	$headers=array( 'name'=>array(
						'rus'=>"Название",
						'sortcolumn'=>"name"
						),
					'cluster_name'=>array(
						'rus'=>'Кластер',
						'sortcolumn'=>"`phpbb_clusters`.`name`"
						),
					'category_name'=>array(
						'rus'=>'Категория',
						'sortcolumn'=>"`phpbb_categories`.`name`"
						),
					'object_name'=>array(
						'rus'=>'Объект',
						'sortcolumn'=>"`phpbb_objects`.`name`"
						)
					);
	
	//Build headers for HTML table
	foreach($headers as $name=>$value){
		if($sort==$name){
			$headers[$name]['html']="<a href='".uri_make(array('sortdirection'=>$link_sort_direction, 'sort'=>$name))."' class='header'>".$headers[$name]['rus']."<img src='/images/".$sort_direction.".png' class='header'></a>";
		}else{
			$headers[$name]['html']="<a href='".uri_make(array('sortdirection'=>'asc', 'sort'=>$name))."' class='header'>".$headers[$name]['rus']."</a>";
		}
	}
	
	//Define first where flag
	$sql_where_flag=false;
	
	//Retrieve cluster id from browser
	if(isset($_GET['cluster'])){
		$cluster_id=$_GET['cluster'];
	}
	
	//Define binded entities columns
	$columns_binded=array('cluster'=>'clusters', 'category'=>'categories', 'object'=>'objects');			

	//Build HTML for filtering bind entities
	//Build SQL-piece for filtering bind entities
	foreach($columns_binded as $name_for=>$name_plural_for){
		$bind_entities_sql_where.=build_bind_entities_filter_sql($name_for, $name_plural_for);
		$template_replacements[$name_plural_for]=build_filter_of_bind_entities($name_for, $name_plural_for);
	}
	
	
	
	//Build SQL for database request
	$sql="SELECT `phpbb_arendas`.`name` as `name`,
				 `phpbb_arendas`.`id` as `id`,
				 `phpbb_arendas`.`priority` as `priority`,
				  `phpbb_arendas`.`contact_date` as `contact_date`,
				 `phpbb_arendas`.`status` as `status`,
				 `phpbb_arendas`.`comment` as `comment`,
				 `phpbb_arendas`.`next_step` as `next_step`,
				  `phpbb_arendas`.`date` as `date`,
				 `phpbb_arendas`.`contacts` as `contacts`,
				 `phpbb_arendas`.`responsible_adg` as `responsible_adg`,
				 `phpbb_arendas`.`responsible_cw` as `responsible_cw`";
	
	//Build SQL-pices to retrieve information of binded entities
	foreach($columns_binded as $name_for=>$name_plural_for){		
		$sql.=" , `phpbb_".$name_plural_for."`.`name` as `".$name_for."_name`";
		$sql.=" , `phpbb_".$name_plural_for."`.`id` as `".$name_for."_id`";
	}
	
	//Build FROM SQL
	$sql.=" FROM `phpbb_arendas` ";
			
	
	//Build 'LEFT JOIN' SQL for binded entities
	foreach($columns_binded as $name_for=>$name_plural_for){		
		$sql.=" LEFT JOIN `phpbb_".$name_plural_for."` ON `phpbb_arendas`.`".$name_for."_id`=`phpbb_".$name_plural_for."`.`id` ";
	}
	
	$sql.=$bind_entities_sql_where;
	$sql.=" ORDER BY ".$headers[$sort]['sortcolumn']." ".$sort_direction;
	
	//Perform request to database
	$arendas_res = db_query($sql);
	
	//Get number of arendas
	$arendas_number=db_count($arendas_res);
	
	$arendas_counter=0;
	$table_html="";
	if(check_rights('delete_arenda')){
		$th_html="		
						<th class='right'></th>";
	}else{
		$th_html="";
	}
	
	if(db_count($arendas_res)>0){
		//Build HTML table
		$table_html="<table class='listcontacts' cellpadding=0 cellspacing=0 border=0>
						<tr>
							<th class='left'>".$headers['name']['html']."</th>
							<th>".$headers['cluster_name']['html']."</th>
							<th>".$headers['category_name']['html']."</th>
							<th>".$headers['object_name']['html']."</th>
							<th>Приоритет</th>
							<th>Дата контакта</th>
							<th>Статус</th>
							<th>Комментарий</th>
							<th>Next step</th>
							<th>Дата</th>
							<th>Контакты</th>
							<th>Ответственный ADG</th>
							<th class='{right_class}'>Ответственный C&W</th>
							{th_html}
						</tr>";
		
		//Iterate arendas from database
		while ($arenda_while = db_fetch($arendas_res)){
			//Increase counter
			$arendas_counter++;
			
			//Get delete arenda link
			$arenda_delete_link="/manager.php?action=delete_arenda&arenda=".$arenda_while['id'];

			//Get special css class for last row
			if($arendas_number==$arendas_counter){
				$bottom_class="bottom";
			}else{
				$bottom_class="";
			}
			
			//Put a dash for text data fields
			foreach($config_arenda['standart_text_data_database'] as $name_for){
				if($arenda_while[$name_for]==""){
					$arenda_while[$name_for]="-";
				}
			}

			//Put a dash for date data fields
			foreach($config_arenda['standart_date_data_database'] as $name_for){
				if(in_array($arenda_while[$name_for], $config_arenda['empty_dates'])){
					$arenda_while[$name_for]="-";
				}else{
					$arenda_while[$name_for]=date("d.m.Y", strtotime($arenda_while[$name_for]));
				}
			}
			
			//Get special css class for last column
			if(check_rights('delete_arenda')){
				$right_class='';
			}else{
				$right_class='right';
			}
			
			//Build first column
			$table_html.="	<tr class='".$bottom_class."'>
								<td class='left'>
									<a href='/manager.php?action=show_arenda&arenda=".$arenda_while['id']."' style='font-size:9pt;'>".
										$arenda_while['name'].
									"</a>
								</td>";
			

								
			//Build HTML columns for binded entities
			foreach($columns_binded as $name_for=>$name_plural_for){
				//Put a dash for empty entity name
				if(trim($arenda_while[$name_for.'_name'])=="" || $arenda_while[$name_for.'_id']==1){
					$arenda_while[$name_for.'_name']="-";
				}				
				
				//Get binded entity link
				$entity_binded_link="/manager.php?action=show_".$name_for."&".$name_for."=".$arenda_while[$name_for.'_id'];
				
				$table_html.="
								<td>
									<a href='".$entity_binded_link."' style='font-size:9pt;'>".
										$arenda_while[$name_for.'_name'].
									"</a>
								</td>";
			}
			
			//Set columns with text data
			$columns=array('priority', 'contact_date', 'status', 'comment', 'next_step', 'date'=>'date', 'contacts', 'responsible_adg', 'responsible_cw');
			
			//Get number of columns
			$columns_number=count($columns);
			
			//Define columns counter
			$columns_counter=0;
			
			//Build HTML columns for text entities
			foreach($columns as $type_for=>$name_for){
				$columns_counter++;
				if($type_for=='date'){
					//$arenda_while[$name_for]=date("d.m.Y", strtotime($arenda_while[$name_for])); 
				}
				if($columns_number==$columns_counter){
					$table_html.="<td class=".$right_class.">".
									$arenda_while[$name_for].
								"</td>";
				}else{
					$table_html.="<td>".
									$arenda_while[$name_for].
								"</td>";
					
				}
			}
			
			if(check_rights('delete_arenda')){
			$table_html.="
			<td class='right'>
				<a href='".$arenda_delete_link."' onclick=\"if(!confirm('Удалить точку аренды &laquo;".$arenda_while['name']."&raquo;?')) return false;\">Удалить</a>
				<br/>
			</td>";
			}
		"</tr>";
		}
		
		$table_html.="</table>";
	}else{
		$table_html="Записей не найдено.";
	}
	
	//Ссылка "Добавить контакт"
	if(check_rights('add_arenda')){
		$template_replacements['arenda_add_link']="<a href='/manager.php?action=add_arenda' class='listcontacts'>Добавить аренду</a><br/><br/>";
	}else{
		$template_replacements['arenda_add_link']="";
	}
	
	//Put number of arendas to template
	$template_replacements['arendas_number']=$arendas_number;
	
	//Put table html to template
	$template_replacements['table']=$table_html;
	
	//Put th html to template
	$template_replacements['th_html']=$th_html;
	
	//Put right class to template
	$template_replacements['right_class']=$right_class;

	$html.=template_get("arendas/list_arendas", $template_replacements);
	return $html;
}

//Build filter of bind entities and SQL for database requests
function build_filter_of_bind_entities($object, $object_plural){
	//Define HTML flow
	$entities_html="";
	
	//Retrieve entity id from browser
	if(isset($_GET[$object])){
		$entity_id=(int)$_GET[$object];
	}else{
		$entity_id=0;
	}
	
	//Retrieve clusters from database
	$entities_res = db_query("SELECT * FROM `phpbb_".$object_plural."` ORDER BY `name`");
	
	//Add all clusters option
	$entities_html.="<option value='0' $selected>".TXT_OPTION_ALL."</option>";

	//Look over clusters in database
	while($entity=db_fetch($entities_res)){
		if($entity_id==$entity['id']){
			$selected="selected";
		}else{
			$selected="";
		}
		if($entity['id']!=1){
			$entities_html.="<option value='".$entity['id']."' ".$selected.">".$entity['name']."</option>";
		}
	}
	
	//Return HTML flow and sql-s
	return $entities_html;
}

//Build SQL piece to filter bind entities
function build_bind_entities_filter_sql($object, $object_plural){
	//Bind global variables
	global $sql_where_flag;
	
	//Build sql-piece
	if(isset($_GET[$object])){
		//Retrieve entity id from browser
		$entity_id=(int)$_GET[$object];

		if($entity_id!=0){
			if($sql_where_flag){
				$sql_where=" AND ";
			}else{
				$sql_where=" WHERE ";
				$sql_where_flag=true;
			}
			
			$sql_where.=" `phpbb_".$object_plural."`.`id`=".$entity_id." ";
		}else{
			$sql_where="";
		}
	}else{
		$sql_where="";
	}
	
	//Return SQL piece
	return $sql_where;
}

?>