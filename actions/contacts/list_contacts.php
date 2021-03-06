<?php
function list_contacts(){
	//НАЧАЛО: Получаем направление сортировки
	if(isset($_GET['sortdirection'])){
		$sortdirection=$_GET['sortdirection'];
	}else{
		$sortdirection="asc";
	}
	if($sortdirection=="asc") $link_sortdirection="desc";
	if($sortdirection=="desc") $link_sortdirection="asc";
	//КОНЕЦ: Получаем направление сортировки
	
	//НАЧАЛО: Получаем столбец для сортировки
	if(isset($_GET['sort'])){
		$sort=$_GET['sort'];
	}else{
		$sort="username";
	}
	//КОНЕЦ: Получаем столбец для сортировки
	
	//НАЧАЛО: Получаем заголовки колонок для сортировки
	$headers=array('username'=>array('rus'=>"ФИО", 'sortcolumn'=>"username"), 'branch'=>array('rus'=>'Подразделение', 'sortcolumn'=>"`phpbb_points`.`name`"));
	foreach($headers as $name=>$value){
		if($sort==$name){
			$headers[$name]['html']="<a href='".uri_make(array('sortdirection'=>$link_sortdirection, 'sort'=>$name))."' class='header'>".$headers[$name]['rus']."<img src='/images/$sortdirection.png' class='header'></a>";
		}else{
			$headers[$name]['html']="<a href='".uri_make(array('sortdirection'=>'asc', 'sort'=>$name))."' class='header'>".$headers[$name]['rus']."</a>";
		}
	}
	//КОНЕЦ: Получаем заголовки колонок для сортировки
	
	//НАЧАЛО: Строим фильтр городов
	$branchesRES = db_query("SELECT * FROM `phpbb_branches` ORDER BY `name`");

	if(isset($_GET['branch']) && @$_GET['branch']!=1){
		$branch_id=$_GET['branch'];
		$branch_where1=" , `phpbb_branches`, `phpbb_points` ";
		$branch_where2=" AND `phpbb_branches`.`id`=$branch_id ";
	}else{
		$branch_where1="";
		$branch_where2="";
	}
	
	$branches_html="";
	while($branch=db_fetch($branchesRES)){
		if($branch_id==$branch['id']){
			$selected="selected";
		}else{
			$selected="";
		}
		if($branch['id']!=1){
			$branches_html.="<option value='{$branch['id']}' $selected>{$branch['name']}</option>";
		}else{
			$branches_html.="<option value='1' $selected>Все города</option>";
		}
	}
	
	if(check_rights('show_hidden_contacts')){
		if(@$_GET['show_hidden_contacts']=='on'){
			$sql_hidden_contacts="OR `user_type`=9";
			$hidden_contact_checked='checked';
		}else{
			$sql_hidden_contacts="";
		    $hidden_contact_checked='';
		}
	}
	//КОНЕЦ: Строим фильтр городов
	
	isset($_GET['branch']) ? $input_hidden_branch="<input type='hidden' name='branch' value='{$_GET['branch']}' />" : $input_hidden_branch="";
	
	if(check_rights('show_hidden_contacts')){
		$filter_hidden_contacts=template_get("contacts/filter_hidden_contacts", array(
														'input_hidden_branch'=>$input_hidden_branch,
														'hidden_contact_checked'=>$hidden_contact_checked
												));
	}
	
	$usersRES = db_query("SELECT *, `phpbb_branches`.`name` as `branch_name`, `phpbb_branches`.`id` as `branch_id`, 
										`phpbb_points`.`phone` as `officephone`,
										`phpbb_points`.`name` as `point_name`, `phpbb_points`.`id` as `point_id`
									FROM `phpbb_users` , `phpbb_branches`, `phpbb_points` 
									WHERE (`user_type` IN (0,3) $sql_hidden_contacts) AND `username`!='root' 
											AND `phpbb_points`.`id`=`phpbb_users`.`point_id`
											AND `phpbb_points`.`branch_id`=`phpbb_branches`.`id` 
											$branch_where2
									ORDER BY {$headers[$sort]['sortcolumn']} $sortdirection
									
									");
	
	$num_users=db_count($usersRES);
	$num=0;
	$table_html="";
	if(check_rights('delete_contact')){
		$th_html="		
						<th class='right'></th>";
	}else{
		$th_html="";
	}
	while ($userWHILE = db_fetch($usersRES)){
		$num++;
		
		$branch_points_number=db_easy_count("SELECT * FROM `phpbb_points` WHERE `branch_id`={$userWHILE['branch_id']}");
		if($branch_points_number==1){
			$branch_point_link="/manager.php?action=show_point&point=".$userWHILE['point_id'];
		}else{
			$branch_point_link="/manager.php?action=show_branch&branch=".$userWHILE['branch_id'];
		}
		
		if($num==$num_users){
			$bottom_class="bottom";
		}else{
			$bottom_class="";
		}
		
		if(trim($userWHILE['officephone'])!=""){
			$officephone=$userWHILE['officephone'];
			if(trim($userWHILE['user_extphone'])!=""){
				$officephone.=", доб. ".$userWHILE['user_extphone'];
			}
		}else{
			$officephone="-";
		}
		
		if(trim($userWHILE['user_workmobilephone'])!=""){
			$mobilephone=$userWHILE['user_workmobilephone']." (рабочий)";
		}elseif(trim($userWHILE['user_privatemobilephone'])!=""){
			$mobilephone=$userWHILE['user_privatemobilephone']." (личный)";
		}else{
			$mobilephone="-";
		}
		
		if(trim($userWHILE['user_occ'])==""){
			$userWHILE['user_occ']="-";
		}
		
		if(trim($userWHILE['point_id'])==1){
			$userWHILE['point_name']="-";
			$userWHILE['branch_name']="-";
		}
		
		$userWHILE['user_type']==9 ? $style_hidden_contact='color:grey' : $style_hidden_contact='';
		
		if(check_rights('delete_contact')){$right_class='';}else{$right_class='right';}
		$table_html.="	<tr class='$bottom_class'>
							<td class='left'><a href='/manager.php?action=show_contact&contact=".$userWHILE['user_id']."' style='font-size:9pt;$style_hidden_contact'>".$userWHILE['username']."</a></td>
							<td><a href='$branch_point_link' style='font-size:9pt;'>".$userWHILE['branch_name']."</a></td>
							<td style='width:250px;'>".$userWHILE['user_occ']."</td>
							<td style='width:250px;'>".$mobilephone."</td>
							<td  class='$right_class'>".$officephone."</td>";
		if(check_rights('delete_contact')){
			$table_html.="	<td class='right'><a href='/manager.php?action=delete_contact&contact={$userWHILE['user_id']}' onclick=\"if(!confirm('Удалить?')) return false;\">Удалить</a><br/></td>
						</tr>";
		}
	}
	
	//Ссылка "Добавить контакт"
	if(check_rights('add_contact')){
		$add_contact_html="<a href='/manager.php?action=add_contact' class='listcontacts'>Добавить сотрудника</a><br/><br/>";
	}else{
		$add_contact_html="";
	}

	$html.=template_get("contacts/list_contacts", array(
															'add_contact'=>$add_contact_html,
															'numusers'=>$num_users,
															'table'=>$table_html,
															'add_user'=>$add_user_html,
															'branches'=>$branches_html,
															'header[username]'=>$headers['username']['html'],
															'header[branch]'=>$headers['branch']['html'],
															'th_html'=>$th_html,
															'right_class'=>$right_class,
															'filter_hidden_contacts'=>$filter_hidden_contacts
																));
	return $html;
}
?>