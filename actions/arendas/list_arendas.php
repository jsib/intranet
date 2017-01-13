<?php
function list_arendas(){
	//Bind global variables
	global $config_arenda;
	
	//Helper
	$headers=$config_arenda['headers'];
	
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
	
	//Define first where flag
	$sql_where_flag=false;
	
	//Define binded entities columns
	$binded_columns_database=$config_arenda['binded_columns_database'];

	//Set columns with text data
	$columns=$config_arenda['columns_without_sort'];
	
	//Get number of columns
	$columns_number=count($columns);

	//Build HTML for filtering bind entities
	//Build SQL-piece for filtering by bind entities
	foreach($binded_columns_database as $name_for=>$name_plural_for){
		$bind_entities_sql_where.=build_bind_entities_filter_sql($name_for, $name_plural_for);
		$template_replacements[$name_plural_for]=build_filter_of_bind_entities($name_for, $name_plural_for);
	}
	
	//Build SQL-piece for filtering by responsible users
	$responsible_users_sql_where
	
	//Build SQL-piece for filtering by dates
	$dates_sql_where.=build_dates_filter_sql('date');
	$template_replacements['date']=build_filter_of_dates('date');
	
	//Build SQL for database request
	$sql='SELECT `phpbb_arendas`.`id` as `id`';
	
	//Build SQL-piece for standart text columns of database table
	if(isset($config_arenda['standart_text_data_database']) && count($config_arenda['standart_text_data_database'])>0){
		foreach($config_arenda['standart_text_data_database'] as $name_for){
			$sql.=', `phpbb_arendas`.`'.$name_for.'` as `'.$name_for.'`'; 
		}
	}
	
	//Build SQL-piece for date columns of database table
	if(isset($config_arenda['standart_date_data_database']) && count($config_arenda['standart_date_data_database'])>0){
		foreach($config_arenda['standart_date_data_database'] as $name_for){
			$sql.=', `phpbb_arendas`.`'.$name_for.'` as `'.$name_for.'`'; 
		}
	}
	
	//Build SQL-pieces to retrieve information of binded entities
	foreach($binded_columns_database as $name_for=>$name_plural_for){
		$sql.=", `phpbb_".$name_plural_for."`.`name` as `".$name_for."_name`";
		$sql.=", `phpbb_".$name_plural_for."`.`id` as `".$name_for."_id`"; 
	}
	
	//Build FROM SQL
	$sql.=" FROM `phpbb_arendas` ";
			
	
	//Build 'LEFT JOIN' SQL for binded entities
	foreach($binded_columns_database as $name_for=>$name_plural_for){
		$sql.=" LEFT JOIN `phpbb_".$name_plural_for."` ON `phpbb_arendas`.`".$name_for."_id`=`phpbb_".$name_plural_for."`.`id` ";
	}

	//Build 'LEFT JOIN' SQL for responsible users
	$sql.=" LEFT JOIN `phpbb_users` ON `phpbb_arendas`.`user_id`=`phpbb_users`.`user_id` ";
	
	$sql.=$bind_entities_sql_where.$responsible_users_sql_where.$dates_sql_where;
	$sql.=" ORDER BY ".$headers[$sort]['sortcolumn']." ".$sort_direction;
	
	//Perform request to database
	$arendas_res = db_query($sql);
	
	//Get number of arendas
	$arendas_number=db_count($arendas_res);
	
	//Define some variables
	$arendas_counter=0;
	$table_html="";
	
	if(check_rights('delete_arenda')){
		$th_html="
						<th class='right'></th>";
	}else{
		$th_html="";
	}
	
	if(db_count($arendas_res)>0){
		//Open <tr> tag
		$table_html.="<table class='listcontacts' cellpadding=0 cellspacing=0 border=0><tr>";
		
		//Build first row
		foreach($headers as $name_for=>$header_for){
			//Set width of column
			if(isset($header_for['width'])){
				$width_for='width:'.$header_for['width'].'px;';
			}else{
				$width_for='';
			}
			
			if($header_for['sorted']==true){
				if($sort==$name_for){
					$html_for="<a href='".uri_make(array('sortdirection'=>$link_sort_direction, 'sort'=>$name_for))."' class='header'>".$headers[$name_for]['title']."<img src='/images/".$sort_direction.".png' class='header'></a>";
				}else{
					$html_for="<a href='".uri_make(array('sortdirection'=>'asc', 'sort'=>$name_for))."' class='header'>".$headers[$name_for]['title']."</a>";
				}
				
				$table_html.='<th style="'.$width_for.'">'.$html_for."</th>";
			}else{
				$table_html.='<th style="'.$width_for.'">'.$headers[$name_for]['title']."</th>";
			}
		}
		
		//Retrieve HTML piece
		$table_html.=$th_html."</tr>";
		
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
	
			//Get special css class for last column
			if(check_rights('delete_arenda')){
				$right_class='';
			}else{
				$right_class='right';
			}
			
			//Define columns counter
			$columns_counter=0;
			//Build other columns
			foreach($headers as $name_for=>$header_for){
				if(isset($header_for['binded_object'])){
					//Helper for binded object name
					$binded_object_name_for=$header_for['binded_object'];
					//Put a dash for empty entity name
					if(trim($arenda_while[$binded_object_name_for.'_name'])=="" || $arenda_while[$binded_object_name_for.'_id']==1){
						$arenda_while[$binded_object_name_for.'_name']="-";
					}				
					
					//Get binded entity link 
					$entity_binded_link="/manager.php?action=show_".$binded_object_name_for."&".$binded_object_name_for."=".$arenda_while[$binded_object_name_for.'_id'];
					
					//Retrieve HTML piece
					$table_html.="
									<td style=''>
										<a href='".$entity_binded_link."' style='font-size:9pt;'>".
											$arenda_while[$binded_object_name_for.'_name'].
										"</a>
									</td>";
				}else{
					//Get first column sign
					if(isset($header_for['first'])){
						$first_for=(bool)$header_for['first'];
					}else{
						$first_for=false;
					}
					
					if($first_for===true){
						//Build first column
						$table_html.="	<tr class='".$bottom_class."'>
											<td class='left'>
												<a href='/manager.php?action=show_arenda&arenda=".$arenda_while['id']."' style='font-size:9pt;'>".
													$arenda_while['name'].
												"</a>
											</td>";
					}else{
						if(isset($config_arenda['standart_text_data_database'][$name_for]) && $arenda_while[$name_for]==""){
							$arenda_while[$name_for]="-";
						}
						
						if(isset($config_arenda['standart_date_data_database'][$name_for])){
							if(in_array($arenda_while[$name_for], $config_arenda['empty_dates'])){
								$arenda_while[$name_for]="-";
							}else{
								$arenda_while[$name_for]=date("d.m.Y", strtotime($arenda_while[$name_for]));
							}
						}
						
						$columns_counter++;

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
				}
			}

			if(check_rights('delete_arenda')){
				$table_html.="
				<td class='right'>
					<a href='".$arenda_delete_link."' onclick=\"if(!confirm('Удалить аренду &laquo;".$arenda_while['name']."&raquo;?')) return false;\">Удалить</a>
					<br/>
				</td>"; 
			}
			
			$table_html.='</tr>';
		}
		
		
		$table_html.='</table>';
		
	}else{
		$table_html="Записей не найдено.";
	}
	
	//Filter for dates
	$dates_html="<option value>";
	
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
	
	//Put right class to template
	$template_replacements['right_class']=$right_class;
	
	//Get filter of responsible users
	foreach(array(26=>'responsible_adg', 27=>'responsible_cw') as $key=>$value){
		$template_replacements[$value.'s']=build_filter_of_responsible_users($value, $key);
	}

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
				$sql_where=' AND ';
			}else{
				$sql_where=' WHERE ';
				$sql_where_flag=true;
			}
			
			$sql_where.=' `phpbb_'.$object_plural.'`.`id`='.$entity_id.' ';
		}else{
			$sql_where='';
		}
	}else{
		$sql_where='';
	}
	
	//Return SQL piece
	return $sql_where;
}

//Build filter of responsible user and SQL for database requests
function build_filter_of_responsible_users($object, $point_id){
	//Define HTML flow
	$entities_html="";
	
	//Retrieve entity id from browser
	if(isset($_GET[$object])){
		$entity_id=(int)$_GET[$object];
	}else{
		$entity_id=0;
	}
	
	//Retrieve clusters from database
	$entities_res = db_query('SELECT * FROM `phpbb_users` WHERE `point_id`='.$point_id.' ORDER BY `username`');
	
	//Add all clusters option
	$entities_html.="<option value='0' $selected>".TXT_OPTION_ALL."</option>";

	//Look over clusters in database
	while($entity=db_fetch($entities_res)){
		if($entity_id==$entity['user_id']){
			$selected="selected";
		}else{
			$selected="";
		}
		if($entity['id']!=1){
			$entities_html.="<option value='".$entity['user_id']."' ".$selected.">".$entity['username']."</option>";
		}
	}
	
	//Return HTML flow and sql-s
	return $entities_html;
}

//Build SQL piece to filter by responsible user
function build_responsible_users_filter_sql($object, $object_plural){
	//Bind global variables
	global $sql_where_flag;
	
	//Build sql-piece
	if(isset($_GET[$object])){
		//Retrieve entity id from browser
		$entity_id=(int)$_GET[$object];

		if($entity_id!=0){
			if($sql_where_flag){
				$sql_where=' AND ';
			}else{
				$sql_where=' WHERE ';
				$sql_where_flag=true;
			}
			
			$sql_where.=' `phpbb_users`.`user_id`='.$entity_id.' ';
		}else{
			$sql_where='';
		}
	}else{
		$sql_where='';
	}
	
	//Return SQL piece
	return $sql_where;
}


//Build HTML for filter of date
function build_filter_of_dates(){
	if(isset($_GET['date'])){
		if(trim($_GET['date'])!=""){
			$date=date("d.m.Y", strtotime($_GET['date']));
		}else{
			$date="";
		}
	}else{
		$date="";
	}
	
	//Return HTML flow
	return $date;
}

//Build SQL-piece to filter by dates
function build_dates_filter_sql($date_field){
	//Bind global variables
	global $sql_where_flag;
	
	//Build sql-piece
	if(isset($_GET['date'])){
		if(trim($_GET['date'])!=""){
			//Get date from browser safe
			$date=date("Y-m-d", strtotime($_GET['date']));

			//Check if this is first position in WHERE clause
			if($sql_where_flag){
				$sql_where=' AND ';
			}else{
				$sql_where=' WHERE ';
				$sql_where_flag=true;
			}
			
			//Add main part of SQL piece
			$sql_where.=' `phpbb_arendas`.`'.$date_field.'`=\''.$date.'\'';
		}else{
			$sql_where='';
		}
	}else{
		$sql_where='';
	}	
	
	//Return SQL piece
	return $sql_where;	
}

?>