<?php
//Define classic(standart) text field for edit and show forms, checks for data retrieved from browser, columns which we write to database.
//Don't put column(data field) here which behaviour is different, then standart.
//Also this fields are editable by edit form.
$config_arenda['standart_text_data_database']=array('date', 'contact_date', 'name', 'priority', 'status', 'comment', 'next_step', 'contacts', 'responsible_adg', 'responsible_cw');
$config_arenda['standart_text_data_form']=array('name', 'cluster_name', 'category_name', 'object_name', 'priority', 'status', 'comment', 'next_step', 'contacts', 'responsible_adg', 'responsible_cw');
$config_arenda['standart_numeric_data_database']=array('cluster', 'category', 'object');

?>