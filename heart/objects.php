<?php
//Define system objects
$system_objects['directions']=$system_objects['direction']=array(
	'singular_name_eng'=>'direction',
	'plural_name_eng'=>'directions',
	'actions'=>array(
		'add'=>array(
			'results'=>array(
				'entity_added'=>array('result'=>"Дирекция '{name}' добавлена успешно."),
				'same_entity_exists'=>array('result'=>"Дирекция c именем '{name}' уже имеется"),
				'empty_entity_name'=>array('result'=>"Имя дирекции не может быть пустым"))),
		'delete'=>array(
			'results'=>array(
				'success'=>array('result'=>"Дирекция с именем '{name}' успешно удалена"))),
		'edit'=>array(
			'results'=>array(
				'success'=>array('result'=>"Изменения успешно сохранены"),
				'same_entity_exists'=>array('result'=>"Дирекция c именем '{name}' уже имеется"),
				'empty_entity_name'=>array('result'=>"Имя дирекции не может быть пустым")))),
	'phrases'=>array(
		'all_entities_text'=>"Все дирекции",
		'no_child_message'=>"В данной дирекции нет сотрудников.",
		'confirm_delete_message'=>"Удалить сотрудника {entity_name}?"
	));
	
$system_objects['users']=$system_objects['user']=array(
	'singular_name_eng'=>'user',
	'plural_name_eng'=>'users',
	'plural_name_rus'=>'сотрудники',
	'parent'=>'direction',
	'actions'=>array(
		'add'=>array(
			'full_name_rus'=>"Добавить сотрудника",
			'button_text_rus'=>"Добавить сотрудника и редактировать остальные поля",
			'results'=>array(
				'entity_name_error'=>array('result'=>"Некорректное имя пользователя. ".TXT_REQUIREMENT_USERNAME),
				'same_entity_exists'=>array('result'=>"Пользователь c именем '{name}' уже имеется"))),
		'edit'=>array(
			'full_name_rus'=>"Редактировать карточку сотрудника",
			'button_text_rus'=>"Сохранить",
			'results'=>array(
				'entity_name_error'=>array('result'=>"Некорректное имя пользователя. ".TXT_REQUIREMENT_USERNAME),
				'same_entity_exists'=>array('result'=>"Пользователь c именем '{name}' уже имеется")),
			'form'=>array(
				'text_fields'=>array(
					'position', 'email', 'phone_ext', 'phone_mobile'),
				'numeric_fields'=>array(
					'direction', 'boss')
			)
		),
		'show'=>array(
			'full_name_rus'=>"Карточка сотрудника",
			'button_text_rus'=>"",
			'results'=>array(
				'entity_name_error'=>array('result'=>"Некорректное имя пользователя. ".TXT_REQUIREMENT_USERNAME),
				'same_entity_exists'=>array('result'=>"Пользователь c именем '{name}' уже имеется"))
		),
		'list'=>array()
	),
	'phrases'=>array(
		'all_entities_text'=>"Все сотрудники",
		'no_child_message'=>"В данной дирекции нет сотрудников.",
		'confirm_delete_message'=>"Удалить сотрудника {entity_name}?"
	)
);			
?>