<?php
//Define classic(standart) text field for edit and show forms, checks for data retrieved from browser, columns which we write to database.
//Don't put column(data field) here which behaviour is different, then standart.
//Also this fields are editable by edit form.
$config_arenda['standart_text_data_database']=array('name', 'priority', 'status', 'comment', 'next_step', 'contacts', 'responsible_adg', 'responsible_cw');
$config_arenda['standart_date_data_database']=array('date', 'contact_date');
$config_arenda['standart_text_data_form']=array('name', 'cluster_name', 'category_name', 'object_name', 'priority', 'status', 'comment', 'next_step', 'contacts', 'responsible_adg', 'responsible_cw');
$config_arenda['standart_date_data_form']=array('contact_date', 'date');
$config_arenda['standart_numeric_data_database']=array('cluster', 'category', 'object');

//Empty datas array
$config_arenda['empty_dates']=array("0000-00-00", "1970-01-01", "0001-11-30");

?>