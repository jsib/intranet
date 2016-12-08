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
					'cluster_name'=>array(
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
	
	//Build SQL-piece for filtering clusters
	$clusters_sql_where=build_cluster_filter_sql();
	
	//Build HTML for filtering clusters
	$clusters_html=build_filter_of_clusters();
	
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
				 `phpbb_arendas`.`responsible_cw` as `responsible_cw`,
				 `phpbb_clusters`.`name` as `cluster_name`,
				 `phpbb_categories`.`name` as `category_name`,
				 `phpbb_clusters`.`id` as `cluster_id`,
				 `phpbb_categories`.`id` as `category_id`
			FROM `phpbb_arendas`
			LEFT JOIN `phpbb_clusters` ON `phpbb_arendas`.`cluster_id`=`phpbb_clusters`.`id`
			LEFT JOIN `phpbb_categories` ON `phpbb_arendas`.`category_id`=`phpbb_categories`.`id`
			LEFT JOIN `phpbb_objects` ON `phpbb_arendas`.`object_id`=`phpbb_objects`.`id`
			$clusters_sql_where
			ORDER BY ".$headers[$sort]['sortcolumn']." ".$sort_direction;
	
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
							<th>".$headers['object_name']['html']."</th>
							<th>Категория</th>
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
			
			//Get cluster link
			$cluster_link="/manager.php?action=show_cluster&cluster=".$arenda_while['cluster_id'];

			//Get category link
			$category_link="/manager.php?action=show_category&category=".$arenda_while['category_id'];

			//Get special css class for last row
			if($arendas_number==$arendas_counter){
				$bottom_class="bottom";
			}else{
				$bottom_class="";
			}
			
			//Put a dash for empty cluster name
			if(trim($arenda_while['cluster_name'])=="" || $arenda_while['cluster_id']==1){
				$arenda_while['cluster_name']="-";
			}
			
			//Put a dash for empty category name
			if(trim($arenda_while['category_name'])=="" || $arenda_while['category_id']==1){
				$arenda_while['category_name']="-";
			}

			//Put a dash for empty object name
			if(trim($arenda_while['object_name'])=="" || $arenda_while['object_id']==1){
				$arenda_while['object_name']="-";
			}
			
			//Put a dash for empty priority
			foreach($config_arenda['standart_text_data_database'] as $name_for){
				if(trim($arenda_while[$name_for])==""){
					$arenda_while[$name_for]="-";
				}
			}

			//Get special css class for last column
			if(check_rights('delete_arenda')){
				$right_class='';
			}else{
				$right_class='right';
			}
			
			
			$table_html.="	<tr class='".$bottom_class."'>
								<td class='left'>
									<a href='/manager.php?action=show_arenda&arenda=".$arenda_while['id']."' style='font-size:9pt;".$style_hidden_arenda."'>".
										$arenda_while['name'].
									"</a>
								</td>
								<td>
									<a href='".$cluster_link."' style='font-size:9pt;'>".
										$arenda_while['cluster_name'].
									"</a>
								</td>
								<td>
									<a href='".$category_link."' style='font-size:9pt;'>".
										$arenda_while['category_name'].
									"</a>
								</td>
								<td>
									<a href='".$object_link."' style='font-size:9pt;'>".
										$arenda_while['object_name'].
									"</a>
								</td>";
								
								/*$table_html.="<td>".
												  $arenda_while['priority'].
											 "</td>";*/

								
								$columns=array('priority', 'contact_date', 'status', 'comment', 'next_step', 'date', 'contacts', 'responsible_adg', 'responsible_cw');
								
								$columns_number=count($columns);
								$columns_counter=0;
								
								foreach($columns as $name_for){
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
		$arenda_add_link="<a href='/manager.php?action=add_arenda' class='listcontacts'>Добавить точку аренды</a><br/><br/>";
	}else{
		$arenda_add_link="";
	}

	$html.=template_get("arendas/list_arendas", array(
															'arenda_add_link'=>$arenda_add_link,
															'arendas_number'=>$arendas_number,
															'table'=>$table_html,
															'clusters'=>$clusters_html,
															'th_html'=>$th_html,
															'right_class'=>$right_class,
																));
	return $html;
}

//Build filter of clusters and SQL for database requests
function build_filter_of_clusters(){
	//Define HTML flow
	$clusters_html="";
	
	//Retrieve cluster id from browser
	if(isset($_GET['cluster'])){
		$cluster_id=(int)$_GET['cluster'];
	}else{
		$cluster_id=0;
	}
	
	//Retrieve clusters from database
	$clusters_res = db_query("SELECT * FROM `phpbb_clusters` ORDER BY `name`");
	
	//Add all clusters option
	$clusters_html.="<option value='0' $selected>".TXT_OPTION_ALL."</option>";

	//Look over clusters in database
	while($cluster=db_fetch($clusters_res)){
		if($cluster_id==$cluster['id']){
			$selected="selected";
		}else{
			$selected="";
		}
		if($cluster['id']!=1){
			$clusters_html.="<option value='".$cluster['id']."' ".$selected.">".$cluster['name']."</option>";
		}
	}
	
	//Return HTML flow and sql-s
	return $clusters_html;
}

//Build SQL piece to filter clusters
function build_cluster_filter_sql(){
	//Bind global variables
	global $sql_where_flag;
	
	//Build sql-piece
	if(isset($_GET['cluster'])){
		//Retrieve cluster id from browser
		$cluster_id=(int)$_GET['cluster'];

		if($cluster_id!=0){
			if($sql_where_flag){
				$sql_where=" AND ";
			}else{
				$sql_where=" WHERE ";
				$sql_where_flag=true;
			}
			
			$sql_where.=" `phpbb_clusters`.`id`=".$cluster_id." ";
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