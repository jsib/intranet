<?php
//Define classic(standart) text field for edit and show forms, checks for data retrieved from browser, columns which we write to database.
//Don't put column(data field) here which behaviour is different, then standart.
//Also this fields are editable by edit form.
$config_arenda['standart_text_data_database']=array('name', 'description', 'comment', 'next_step_old', 'contacts', 'responsible_adg', 'responsible_cw');
$config_arenda['standart_date_data_database']=array('date', 'contact_date');
$config_arenda['standart_text_data_form']=array('name', 'cluster_name', 'category_name', 'object_name', 'status_name', 'next_step_name', 'priority_name', 'description', 'comment', 'next_step_old', 'contacts', 'responsible_adg', 'responsible_cw');
$config_arenda['binded_columns_database']=array('cluster'=>'clusters', 'category'=>'categories', 'object'=>'objects', 'status'=>'statuses', 'next_step'=>'next_steps', 'priority'=>'priorities');
$config_arenda['columns_without_sort']=array('description', 'contact_date', 'comment', 'date'=>'date', 'contacts', 'responsible_adg', 'responsible_cw');

$config_arenda['selects']=$config_arenda['binded_columns_database'];
$config_arenda['standart_numeric_data_database']=$config_arenda['binded_columns_database'];
$config_arenda['standart_date_data_form']=$config_arenda['standart_date_data_database'];

//Empty datas array
$config_arenda['empty_dates']=array("0000-00-00", "1970-01-01", "0001-11-30");

//Build headers for table
$config_arenda['headers']=array(
						'name'=>array(
							'title'=>"Название арендатора",
							'sortcolumn'=>"name",
							'sorted'=>true
							),
						'cluster_name'=>array(
							'title'=>'Кластер',
							'sortcolumn'=>"`phpbb_clusters`.`name`",
							'sorted'=>true
							),
						'object_name'=>array(
							'title'=>'Объект',
							'sortcolumn'=>"`phpbb_objects`.`name`",
							'sorted'=>true
							),
						'status_name'=>array(
							'title'=>'Статус',
							'sortcolumn'=>"`phpbb_statuses`.`name`",
							'sorted'=>true
							),
						'next_step_name'=>array(
							'title'=>'Next Step',
							'sortcolumn'=>"`phpbb_next_steps`.`name`",
							'sorted'=>true
							),
						'priority_name'=>array(
							'title'=>'Приоритет',
							'sortcolumn'=>"`phpbb_priorities`.`name`",
							'sorted'=>true
							),
						'description'=>array(
							'title'=>'Описание',
							'sorted'=>false
							),
						'contact_date'=>array(
							'title'=>'Дата контакта',
							'sorted'=>false
							),
						'comment'=>array(
							'title'=>'Комментарий',
							'sorted'=>false
						),
						'date'=>array(
							'title'=>'Дата',
							'sorted'=>false
						),
						'contacts'=>array(
							'title'=>'Контакты',
							'sorted'=>false
						),
						'responsible'=>array(
							'title'=>'Ответственный',
							'sorted'=>false
						),
						'responsible_cw'=>array(
							'title'=>'Ответственный C&W',
							'sorted'=>false
						)
					);		
?>