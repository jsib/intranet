<?php
function add_user(){
	//Retrieve information from this function name
	$function_name_pieces=explode("_", __FUNCTION__);

	//Refer to global variables
	global $table_prefix;
	global $system_objects;

	//Retrieve object properties
	$object_singular_eng=$function_name_pieces[1];
	$object_plural_eng=$system_objects[$object_singular_eng]['plural_name_eng'];
	$object_actions=$system_objects[$object_plural_eng]['actions'];
	
	//Retrieve action properties
	$action_eng=$function_name_pieces[0];
	$action_full_eng=__FUNCTION__;
	
	if(!check_rights($action_full_eng)){
		//Return HTML flow
		system_error('permission_denied');
	}
	
	//No HTML form data
	if(!isset($_POST['name'])){
		//Возвращаем значение функции
		return show_form_add_entity($system_objects, $object_singular_eng, $object_plural_eng, $action_eng, $action_full_eng);
	//HTML form data alreary retrieved
	}else{
		//Retrive entity name from browser
		$entity_name_eng=trim($_POST['name']);
		
		//Check entity name
		if(preg_match(REGEXP_USERNAME, $entity_name_eng)){
			//Entity with same name already exists
			if(db_easy_count("SELECT `".$object_singular_eng."_id` FROM `".$table_prefix.$object_plural_eng."` WHERE `username`='".$entity_name_eng."'")>0){
				$errors[]=html_replace($object_actions[$action_eng]['results']['same_entity_exists']['result'], array('name'=>$entity_name_eng));
			}
		//Error in entity name
		}else{
			$errors[]=html_replace($object_actions[$action_eng]['results']['entity_name_error']['result'], array('name'=>$entity_name_eng));
		}
		
		//Save data to database
		if(count($errors)==0){
			$user_data=array(	'username'=>$entity_name_eng,
								'user_password'=>phpbb_hash('вава'),
								'user_email'=>'',
								'group_id'=>'1774', 
								'user_lang'=>'ru',
								'user_type'=>0,
								'user_regdate'=>time(),
								'direction_id'=>0
							);
			
			//Add user with PHPBB function
			$user_id=user_add($user_data);
			
			//Refer to other page
			header("location: /manager.php?action=edit_".$object_singular_eng."&entity_id=$user_id");
		//Show HTML form with error message
		}else{
			//Prepare entity for error page
			$entity['name']=$entity_name_eng;
			
			//Return HTML flow
			return show_form_add_entity($system_objects, $object_singular_eng, $object_plural_eng, $action_eng, $action_full_eng, $entity, $errors);
		}
	}
}



//Return HTML code of form
function show_form_add_entity($system_objects, $object_singular_eng, $object_plural_eng, $action_eng, $action_full_eng, $entity=array(), $messages=array()){
	//Build message HTML
	$message_html=show_messages($messages);
		
	//Return HTML flow
	return template_get($object_plural_eng."/".$action_full_eng, array(
															'page_header'=>$system_objects[$object_singular_eng]['actions'][$action_eng]['full_name_rus'],
															'action_link'=>"/manager.php?action=".$action_full_eng,
															'name'=>$entity['name'],
															'message'=>$message_html,
															'button_text_rus'=>$system_objects[$object_singular_eng]['actions'][$action_eng]['button_text_rus']
												));
}
?>